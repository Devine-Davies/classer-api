<?php

namespace App\Services;

use App\Logging\AppLogger;
use App\Models\CatalogItem;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use LogicException;
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
     * Create a pending order from selected catalog items.
     *
     * @param  array<int, string>  $catalogItemUids  Catalog item UIDs in display order.
     * @param  array<string, int>  $quantities  Quantities keyed by catalog item UID.
     * @return Order Newly created pending order with items.
     *
     * @throws RuntimeException
     * @throws \Throwable
     */
    public function createPendingOrder(
        array $catalogItemUids,
        array $quantities,
        ?string $checkoutSessionHash = null,
    ): Order {
        if (empty($catalogItemUids)) {
            $this->logger->warning('Cannot create pending order: no catalog item UIDs provided');

            throw new RuntimeException('No valid catalog items found for checkout.');
        }

        $resolvedItems = $this->resolveCheckoutItems($catalogItemUids, $quantities);

        if (empty($resolvedItems)) {
            $this->logger->warning('Cannot create pending order: no valid catalog items resolved', [
                'requested_catalog_item_uids' => $catalogItemUids,
            ]);

            throw new RuntimeException('No valid catalog items found for checkout.');
        }

        $orderQuantity = array_sum(array_map(static fn (array $item) => $item['quantity'], $resolvedItems));
        $orderAmount = array_sum(array_map(static fn (array $item) => $item['line_amount'], $resolvedItems));
        $orderCurrency = $resolvedItems[0]['currency'];

        return DB::transaction(function () use ($resolvedItems, $orderQuantity, $orderAmount, $orderCurrency, $checkoutSessionHash) {
            $order = Order::create([
                'checkout_session_hash' => $checkoutSessionHash,
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
     * Create or reuse the pending order for one checkout attempt.
     *
     * @param  array<int, string>  $catalogItemUids
     * @param  array<string, int>  $quantities
     *
     * @throws \Throwable
     */
    public function createOrReusePendingOrder(
        array $catalogItemUids,
        array $quantities,
        string $checkoutSessionHash,
        ?string $editingOrderUid = null,
    ): Order {
        try {
            return DB::transaction(function () use ($catalogItemUids, $quantities, $checkoutSessionHash, $editingOrderUid): Order {
                $order = null;

                if ($editingOrderUid) {
                    $order = Order::query()
                        ->where('uid', $editingOrderUid)
                        ->lockForUpdate()
                        ->first();
                }

                if (! $order) {
                    $order = Order::query()
                        ->where('checkout_session_hash', $checkoutSessionHash)
                        ->lockForUpdate()
                        ->first();
                }

                if ($order) {
                    $this->assertOrderIsMutable($order);

                    return $this->synchronisePendingOrderItems(
                        order: $order,
                        catalogItemUids: $catalogItemUids,
                        quantities: $quantities,
                        checkoutSessionHash: $checkoutSessionHash,
                    );
                }

                return $this->createPendingOrder(
                    catalogItemUids: $catalogItemUids,
                    quantities: $quantities,
                    checkoutSessionHash: $checkoutSessionHash,
                );
            });
        } catch (QueryException $exception) {
            if (! $this->isCheckoutSessionHashUniqueViolation($exception)) {
                throw $exception;
            }

            $order = Order::query()
                ->where('checkout_session_hash', $checkoutSessionHash)
                ->firstOrFail();

            $this->assertOrderIsMutable($order);

            return $this->synchronisePendingOrderItems(
                order: $order,
                catalogItemUids: $catalogItemUids,
                quantities: $quantities,
                checkoutSessionHash: $checkoutSessionHash,
            );
        }
    }

    /**
     * @param  array<int, string>  $catalogItemUids
     * @param  array<string, int>  $quantities
     * @return array<int, array{catalog_item:CatalogItem,quantity:int,unit_amount:int,line_amount:int,currency:string}>
     */
    protected function resolveCheckoutItems(array $catalogItemUids, array $quantities): array
    {
        $catalogItems = CatalogItem::query()
            ->with('sellable')
            ->where('is_published', true)
            ->whereIn('uid', $catalogItemUids)
            ->get()
            ->keyBy('uid');

        return collect($catalogItemUids)
            ->map(static fn (string $uid): ?CatalogItem => $catalogItems->get($uid))
            ->filter()
            ->map(function (CatalogItem $catalogItem) use ($quantities): array {
                $quantity = max(1, (int) ($quantities[$catalogItem->uid] ?? 1));
                $pricing = $catalogItem->pricingBreakdown($quantity);

                return [
                    'catalog_item' => $catalogItem,
                    'quantity' => $quantity,
                    'unit_amount' => $pricing['unit_amount'],
                    'line_amount' => $pricing['line_amount'],
                    'currency' => strtolower((string) $catalogItem->currency),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * Update an existing pending order so it reflects the current checkout draft.
     *
     * @param  array<int, string>  $catalogItemUids
     * @param  array<string, int>  $quantities
     *
     * @throws RuntimeException
     */
    protected function synchronisePendingOrderItems(
        Order $order,
        array $catalogItemUids,
        array $quantities,
        string $checkoutSessionHash,
    ): Order {
        if (empty($catalogItemUids)) {
            throw new RuntimeException('No valid catalog items found for checkout.');
        }

        $resolvedItems = $this->resolveCheckoutItems($catalogItemUids, $quantities);

        if (empty($resolvedItems)) {
            $this->logger->warning('Cannot update pending order: no valid catalog items resolved', [
                'order_uid' => $order->uid,
                'requested_catalog_item_uids' => $catalogItemUids,
            ]);

            throw new RuntimeException('No valid catalog items found for checkout.');
        }

        return DB::transaction(function () use ($order, $resolvedItems, $checkoutSessionHash): Order {
            $catalogItemUids = collect($resolvedItems)
                ->map(static fn (array $item): string => $item['catalog_item']->uid)
                ->all();

            $order->items()
                ->whereNotIn('catalog_item_id', $catalogItemUids)
                ->delete();

            foreach ($resolvedItems as $item) {
                /** @var CatalogItem $catalogItem */
                $catalogItem = $item['catalog_item'];

                $orderItem = $order->items()
                    ->where('catalog_item_id', $catalogItem->uid)
                    ->first();

                if (! $orderItem) {
                    $orderItem = new OrderItem([
                        'order_id' => $order->uid,
                        'catalog_item_id' => $catalogItem->uid,
                    ]);
                }

                $orderItem->fill([
                    'product_name' => $catalogItem->title,
                    'sku_snapshot' => $catalogItem->sku,
                    'name_snapshot' => $catalogItem->title,
                    'unit_amount' => $item['unit_amount'],
                    'quantity' => $item['quantity'],
                    'line_amount' => $item['line_amount'],
                    'currency' => $item['currency'],
                ]);
                $orderItem->save();
            }

            $orderQuantity = array_sum(array_map(static fn (array $item) => $item['quantity'], $resolvedItems));
            $orderAmount = array_sum(array_map(static fn (array $item) => $item['line_amount'], $resolvedItems));
            $orderCurrency = $resolvedItems[0]['currency'];

            $order->fill([
                'checkout_session_hash' => $order->checkout_session_hash ?: $checkoutSessionHash,
                'quantity' => $orderQuantity,
                'amount' => $orderAmount,
                'subtotal_amount' => $orderAmount,
                'discount_amount' => 0,
                'total_amount' => $orderAmount,
                'currency' => $orderCurrency,
                'status' => 'pending',
                'discount_code_id' => null,
                'discount_snapshot' => null,
            ]);
            $order->save();

            $this->logger->info('Reused pending checkout order', [
                'order_uid' => $order->uid,
                'line_item_count' => count($resolvedItems),
                'quantity' => $orderQuantity,
                'amount' => $orderAmount,
                'currency' => $orderCurrency,
                'checkout_hash' => substr($checkoutSessionHash, 0, 12),
            ]);

            return $order->fresh(['items.catalogItem', 'discountCode']);
        });
    }

    protected function assertOrderIsMutable(Order $order): void
    {
        if ($order->status !== 'pending') {
            throw new LogicException('The order can no longer be updated.');
        }
    }

    protected function isCheckoutSessionHashUniqueViolation(QueryException $exception): bool
    {
        $sqlState = (string) ($exception->errorInfo[0] ?? '');
        $message = $exception->getMessage();

        return $sqlState === '23000'
            && str_contains($message, 'checkout_session_hash');
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

    /**
     * Recalculate pending order line amounts from current catalog item promotions.
     */
    public function refreshOrderLinePricingFromCatalog(Order $order): Order
    {
        $order->loadMissing('items.catalogItem');

        DB::transaction(function () use ($order): void {
            foreach ($order->items as $item) {
                $catalogItem = $item->catalogItem;

                if (! $catalogItem) {
                    continue;
                }

                $quantity = max(1, (int) $item->quantity);
                $pricing = $catalogItem->pricingBreakdown($quantity);
                $currency = strtolower((string) ($catalogItem->currency ?: $item->currency));

                $item->fill([
                    'unit_amount' => $pricing['unit_amount'],
                    'line_amount' => $pricing['line_amount'],
                    'currency' => $currency,
                ]);
                $item->save();
            }

            $order->load('items');
            $subtotal = (int) $order->items->sum('line_amount');

            $order->fill([
                'subtotal_amount' => $subtotal,
                'amount' => $subtotal,
                'total_amount' => $subtotal,
                'discount_amount' => 0,
                'discount_code_id' => null,
                'discount_snapshot' => null,
            ]);
            $order->save();
        });

        return $order->fresh(['items.catalogItem', 'discountCode']);
    }
}
