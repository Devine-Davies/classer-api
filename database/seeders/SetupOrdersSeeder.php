<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderPayment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class SetupOrdersSeeder extends Seeder
{
    /**
     * Seed orders and payments for scenario testing.
     */
    public function run(): void
    {
        $homeProduct = Product::where('uid', '2f9d55af-bfc5-4e67-9025-7f053f2a9ca1')
            ->first();

        $cloudShare = Product::where('uid', 'c6cbf523-30fd-4ab6-9eb4-8fc8d09d7a44')
            ->first();

        $users = User::where('email', 'like', 'test.user.%@example.com')
            ->orderBy('email')
            ->get()
            ->values();

        if ($users->isEmpty()) {
            return;
        }

        $scenarios = [
            [
                'order_uid' => '10000000-0000-4000-8000-000000000001',
                'payment_uid' => '20000000-0000-4000-8000-000000000001',
                'status' => 'pending',
                'payment_status' => 'pending',
                'quantity' => 1,
            ],
            [
                'order_uid' => '10000000-0000-4000-8000-000000000002',
                'payment_uid' => '20000000-0000-4000-8000-000000000002',
                'status' => 'pending',
                'payment_status' => 'processing',
                'items' => [
                    ['product' => 'home', 'quantity' => 1],
                    ['product' => 'cloudShare', 'quantity' => 1],
                ],
            ],
            [
                'order_uid' => '10000000-0000-4000-8000-000000000003',
                'payment_uid' => '20000000-0000-4000-8000-000000000003',
                'status' => 'pending',
                'payment_status' => 'requires_action',
                'quantity' => 1,
            ],
            [
                'order_uid' => '10000000-0000-4000-8000-000000000004',
                'payment_uid' => '20000000-0000-4000-8000-000000000004',
                'status' => 'paid',
                'payment_status' => 'paid',
                'items' => [
                    ['product' => 'home', 'quantity' => 1],
                    ['product' => 'cloudShare', 'quantity' => 1],
                ],
                'paid_at' => now()->subDays(2),
            ],
            [
                'order_uid' => '10000000-0000-4000-8000-000000000005',
                'payment_uid' => '20000000-0000-4000-8000-000000000005',
                'status' => 'pending',
                'payment_status' => 'failed',
                'quantity' => 1,
                'failure_code' => 'card_declined',
                'failure_message' => 'The card was declined by issuer.',
            ],
            [
                'order_uid' => '10000000-0000-4000-8000-000000000006',
                'payment_uid' => '20000000-0000-4000-8000-000000000006',
                'status' => 'refunded',
                'payment_status' => 'refunded',
                'quantity' => 1,
                'paid_at' => now()->subDays(7),
                'refunded_at' => now()->subDays(1),
            ],
        ];

        $productMap = [
            'home' => $homeProduct,
            'cloudShare' => $cloudShare,
        ];

        foreach ($scenarios as $index => $scenario) {
            $user = $users[$index % $users->count()];
            $itemDefinitions = $scenario['items'] ?? [
                ['product' => 'home', 'quantity' => $scenario['quantity'] ?? 1],
            ];

            $orderItems = [];
            $orderAmount = 0;
            $orderQuantity = 0;

            foreach ($itemDefinitions as $itemDefinition) {
                $productKey = (string) ($itemDefinition['product'] ?? 'home');
                $itemProduct = $productMap[$productKey] ?? $homeProduct;
                $itemCatalogItem = $itemProduct?->catalogItem;

                if (! $itemProduct || ! $itemCatalogItem) {
                    continue;
                }

                $itemQuantity = max(1, (int) ($itemDefinition['quantity'] ?? 1));
                $lineAmount = (int) $itemCatalogItem->price_amount * $itemQuantity;

                $orderItems[] = [
                    'product' => $itemProduct,
                    'catalog_item' => $itemCatalogItem,
                    'quantity' => $itemQuantity,
                    'line_amount' => $lineAmount,
                ];

                $orderQuantity += $itemQuantity;
                $orderAmount += $lineAmount;
            }

            if (empty($orderItems)) {
                continue;
            }

            $primaryProduct = $orderItems[0]['product'];
            $primaryCatalogItem = $orderItems[0]['catalog_item'];

            $order = Order::updateOrCreate([
                'uid' => $scenario['order_uid'],
            ], [
                'product_id' => $primaryProduct->uid,
                'catalog_item_id' => $primaryCatalogItem?->uid,
                'quantity' => $orderQuantity,
                'amount' => $orderAmount,
                'subtotal_amount' => $orderAmount,
                'discount_amount' => 0,
                'total_amount' => $orderAmount,
                'currency' => strtolower((string) $primaryCatalogItem->currency),
                'status' => $scenario['status'],
                'customer_name' => $user->name,
                'customer_email' => $user->email,
                'shipping_line_1' => 'Seeded Street '.($index + 1),
                'shipping_line_2' => null,
                'shipping_city' => 'London',
                'shipping_state' => 'Greater London',
                'shipping_postal_code' => 'EC1A 1AA',
                'shipping_country' => 'GB',
                'discount_snapshot' => null,
                'paid_at' => $scenario['paid_at'] ?? null,
            ]);

            $order->items()->delete();

            foreach ($orderItems as $orderItem) {
                $itemProduct = $orderItem['product'];
                $itemCatalogItem = $orderItem['catalog_item'];

                OrderItem::create([
                    'order_id' => $order->uid,
                    'catalog_item_id' => $itemCatalogItem->uid,
                    'sku_snapshot' => $itemCatalogItem->sku,
                    'name_snapshot' => $itemProduct->title,
                    'unit_amount' => (int) $itemCatalogItem->price_amount,
                    'quantity' => $orderItem['quantity'],
                    'line_amount' => $orderItem['line_amount'],
                ]);
            }

            OrderPayment::updateOrCreate([
                'uid' => $scenario['payment_uid'],
            ], [
                'order_id' => $order->uid,
                'stripe_payment_intent_id' => 'pi_seeded_'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT),
                'stripe_payment_method_id' => $scenario['payment_status'] === 'pending' ? null : 'pm_seeded_'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT),
                'stripe_customer_id' => 'cus_seeded_'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT),
                'status' => $scenario['payment_status'],
                'amount' => $orderAmount,
                'currency' => strtolower((string) $primaryCatalogItem->currency),
                'failure_code' => $scenario['failure_code'] ?? null,
                'failure_message' => $scenario['failure_message'] ?? null,
                'paid_at' => $scenario['paid_at'] ?? null,
                'refunded_at' => $scenario['refunded_at'] ?? null,
            ]);
        }
    }
}
