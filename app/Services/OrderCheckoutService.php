<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class OrderCheckoutService
{
    /**
     * @param array<int, array{product_uid:string, quantity?:int}> $lineItems
     */
    public function createPendingOrder(array $lineItems): Order
    {
        if (empty($lineItems)) {
            throw new RuntimeException('At least one order line item is required.');
        }

        $productUids = array_values(array_unique(array_map(
            static fn (array $item) => (string) ($item['product_uid'] ?? ''),
            $lineItems
        )));

        $products = Product::whereIn('uid', $productUids)
            ->where('is_active', true)
            ->get()
            ->keyBy('uid');

        $resolvedItems = [];
        foreach ($lineItems as $lineItem) {
            $productUid = (string) ($lineItem['product_uid'] ?? '');
            $product = $products->get($productUid);

            if (!$product) {
                continue;
            }

            $quantity = max(1, (int) ($lineItem['quantity'] ?? 1));
            $resolvedItems[] = [
                'product' => $product,
                'quantity' => $quantity,
                'unit_amount' => (int) $product->price_amount,
                'line_amount' => (int) $product->price_amount * $quantity,
                'currency' => strtolower((string) $product->currency),
            ];
        }

        if (empty($resolvedItems)) {
            throw new RuntimeException('No valid products found for checkout.');
        }

        $orderQuantity = array_sum(array_map(static fn (array $item) => $item['quantity'], $resolvedItems));
        $orderAmount = array_sum(array_map(static fn (array $item) => $item['line_amount'], $resolvedItems));
        $orderCurrency = $resolvedItems[0]['currency'];
        $primaryProduct = $resolvedItems[0]['product'];

        return DB::transaction(function () use ($resolvedItems, $orderQuantity, $orderAmount, $orderCurrency, $primaryProduct) {
            $order = Order::create([
                'product_id' => $primaryProduct->uid,
                'quantity' => $orderQuantity,
                'amount' => $orderAmount,
                'subtotal_amount' => $orderAmount,
                'discount_amount' => 0,
                'total_amount' => $orderAmount,
                'currency' => $orderCurrency,
                'status' => 'pending',
            ]);

            foreach ($resolvedItems as $item) {
                /** @var Product $product */
                $product = $item['product'];

                OrderItem::create([
                    'order_id' => $order->uid,
                    'product_id' => $product->uid,
                    'product_name' => $product->name,
                    'purchase_type' => $product->purchase_type,
                    'unit_amount' => $item['unit_amount'],
                    'quantity' => $item['quantity'],
                    'line_amount' => $item['line_amount'],
                    'currency' => $item['currency'],
                ]);
            }

            return $order->fresh()->load(['product', 'items.product', 'discountCode']);
        });
    }

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

        return $order->fresh();
    }
}
