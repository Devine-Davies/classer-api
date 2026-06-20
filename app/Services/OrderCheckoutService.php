<?php

namespace App\Services;

use App\Logging\AppLogger;
use App\Models\CatalogItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class OrderCheckoutService
{
    /**
     * Create order checkout service with logger context.
     *
     * @param  AppLogger  $logger  Application logger wrapper.
     */
    public function __construct(protected AppLogger $logger)
    {
        $this->logger->setContext('OrderCheckoutService');
    }

    /**
     * Create a pending order from selected catalog or product line items.
     *
     * @param  array<int, array{catalog_item_uid?:string,product_uid?:string,quantity?:int}>  $lineItems
     * @return Order Newly created pending order with items.
     *
     * @throws RuntimeException
     * @throws \Throwable
     */
    public function createPendingOrder(
        array $catalogItemUids,
        array $quantities,
    ): Order {
        $catalogItems = CatalogItem::query()
            ->where('is_published', true)
            ->where(function ($query) use ($catalogItemUids) {
                if (! empty($catalogItemUids)) {
                    $query->orWhereIn('uid', $catalogItemUids);
                }
            })
            ->get()
            ->keyBy('uid');

        $catalogItems->loadMissing('sellable');

        $resolvedItems = [];
        foreach ($catalogItems as $catalogItem) {
            $catalogItemUid = (string) ($catalogItem->uid ?? '');
            $productUid = (string) ($catalogItem->sellable_id ?? '');

            if (! $catalogItem) {
                $this->logger->warning('Skipping unresolved catalog item in checkout line items', [
                    'catalog_item_uid' => $catalogItemUid,
                    'product_uid' => $productUid,
                ]);

                continue;
            }

            /** @var Product|null $product */
            $quantity = max(1, (int) ($lineItem['quantity'] ?? 1));
            $originalUnitAmount = (int) $catalogItem->price_amount;
            $promotionPercentage = $catalogItem->promotion_eligible
                ? max(0, min(100, (int) $catalogItem->promotion_percentage))
                : 0;
            $discountedUnitAmount = $originalUnitAmount;

            if ($promotionPercentage > 0) {
                $discountedUnitAmount = (int) floor($originalUnitAmount * ((100 - $promotionPercentage) / 100));
            }

            $resolvedItems[] = [
                'catalog_item' => $catalogItem,
                'quantity' => $quantity,
                'unit_amount' => $discountedUnitAmount,
                'line_amount' => $discountedUnitAmount * $quantity,
                'original_unit_amount' => $originalUnitAmount,
                'original_line_amount' => $originalUnitAmount * $quantity,
                'promotion_percentage' => $promotionPercentage,
                'currency' => strtolower((string) $catalogItem->currency),
            ];
        }

        if (empty($resolvedItems)) {
            $this->logger->warning('Cannot create pending order: no valid catalog items resolved', [
                'requested_catalog_item_uids' => $catalogItemUids,
                'requested_product_uids' => $productUids,
            ]);
            throw new RuntimeException('No valid catalog items found for checkout.');
        }

        $orderQuantity = array_sum(array_map(static fn (array $item) => $item['quantity'], $resolvedItems));
        $orderAmount = array_sum(array_map(static fn (array $item) => $item['line_amount'], $resolvedItems));
        $orderCurrency = $resolvedItems[0]['currency'];

        return DB::transaction(function () use ($resolvedItems, $orderQuantity, $orderAmount, $orderCurrency) {
            $order = Order::create([
                'quantity' => $orderQuantity,
                'amount' => $orderAmount,
                'subtotal_amount' => $orderAmount,
                'discount_amount' => 0,
                'total_amount' => $orderAmount,
                'currency' => $orderCurrency,
                'status' => 'pending',
            ]);

            foreach ($resolvedItems as $item) {
                /** @var CatalogItem $catalogItem */
                $catalogItem = $item['catalog_item'];

                OrderItem::create([
                    'order_id' => $order->uid,
                    'catalog_item_id' => $catalogItem->uid,
                    'product_name' => $catalogItem->title,
                    'sku_snapshot' => $catalogItem->sku,
                    'name_snapshot' => $catalogItem->title,
                    'unit_amount' => $item['unit_amount'],
                    'quantity' => $item['quantity'],
                    'line_amount' => $item['line_amount'],
                    'currency' => $item['currency'],
                ]);
            }

            $this->logger->info('Created pending checkout order', [
                'order_uid' => $order->uid,
                'line_item_count' => count($resolvedItems),
                'quantity' => $orderQuantity,
                'amount' => $orderAmount,
                'currency' => $orderCurrency,
            ]);

            return $order->fresh()->load(['items.catalogItem', 'discountCode']);
        });
    }

    /**
     * Persist customer and shipping details onto an order.
     *
     * @param  Order  $order  Order to update.
     * @param  array<string, mixed>  $payload  Incoming customer/shipping payload.
     * @return Order Refreshed order.
     */
    public function hydrateCustomerDetails(Order $order, array $payload): Order
    {
        $order->fill([
            'customer_name' => $payload['customer_name'] ?? $order->customer_name,
            'customer_email' => $payload['customer_email'] ?? $order->customer_email,
            'shipping_line_1' => $payload['shipping_line_1'] ?? $order->shipping_line_1,
            'shipping_line_2' => $payload['shipping_line_2'] ?? $order->shipping_line_2,
            'shipping_city' => $payload['shipping_city'] ?? $order->shipping_city,
            'shipping_state' => $payload['shipping_state'] ?? $order->shipping_state,
            'shipping_postal_code' => $payload['shipping_postal_code'] ?? $order->shipping_postal_code,
            'shipping_country' => strtoupper($payload['shipping_country'] ?? $order->shipping_country),
        ]);

        $order->save();

        $this->logger->info('Hydrated checkout order customer details', [
            'order_uid' => $order->uid,
            'customer_email' => $order->customer_email,
            'shipping_country' => $order->shipping_country,
        ]);

        return $order->fresh();
    }
}
