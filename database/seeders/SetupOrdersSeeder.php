<?php

namespace Database\Seeders;

use App\Models\CatalogItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderPayment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class SetupOrdersSeeder extends Seeder
{
    /**
     * Seed orders and payments for scenario testing.
     */
    public function run(): void
    {
        $catalogItems = CatalogItem::query()
            ->with('sellable')
            ->whereNotNull('price_amount')
            ->get();

        if ($catalogItems->isEmpty()) {
            return;
        }

        $users = User::where('email', 'like', 'test.user.%@example.com')
            ->orderBy('email')
            ->get()
            ->values();

        if ($users->isEmpty()) {
            $users = User::query()->limit(1)->get()->values();
        }

        if ($users->isEmpty()) {
            return;
        }

        $scenarios = [
            [
                'order_uid' => '11111111-1111-4111-8111-111111111111',
                'status' => 'pending',
                'payment_status' => 'pending',
                'quantity' => 1,
                'order_paymenets' => [
                    [
                        'uid' => '31111111-1111-4111-8111-111111111111',
                        'stripe_payment_intent_id' => 'pi_test_111111111111',
                        'stripe_payment_method_id' => 'pm_test_000001',
                        'stripe_customer_id' => 'cus_test_000001',
                        'status' => 'pending',
                        'amount' => 990,
                        'currency' => 'GBP',
                    ],
                ],
            ],
            [
                'order_uid' => '11111111-1111-4111-8111-111111111112',
                'status' => 'processing',
                'payment_status' => 'processing',
                'items' => $this->shuffleAndTakeRandomCatalogItems($catalogItems, 2),
                'order_paymenets' => [
                    [
                        'uid' => '31111111-1111-4111-8111-111111111112',
                        'stripe_payment_intent_id' => 'pi_test_111111111112',
                        'stripe_payment_method_id' => 'pm_test_000002',
                        'stripe_customer_id' => 'cus_test_000002',
                        'status' => 'processing',
                        'amount' => 2980,
                        'currency' => 'GBP',
                    ],
                ],
            ],
            [
                'order_uid' => '11111111-1111-4111-8111-111111111113',
                'status' => 'pending',
                'payment_status' => 'requires_action',
                'quantity' => 1,
                'order_paymenets' => [
                    [
                        'uid' => '31111111-1111-4111-8111-111111111113',
                        'stripe_payment_intent_id' => 'pi_test_111111111113',
                        'stripe_payment_method_id' => 'pm_test_000003',
                        'stripe_customer_id' => 'cus_test_000003',
                        'status' => 'requires_action',
                        'amount' => 990,
                        'currency' => 'GBP',
                    ],
                ],
            ],
            [
                'order_uid' => '11111111-1111-4111-8111-111111111114',
                'status' => 'paid',
                'payment_status' => 'paid',
                'items' => $this->shuffleAndTakeRandomCatalogItems($catalogItems, 2),
                'paid_at' => now()->subDays(2),
                'order_paymenets' => [
                    [
                        'uid' => '31111111-1111-4111-8111-111111111114',
                        'stripe_payment_intent_id' => 'pi_test_111111111114',
                        'stripe_payment_method_id' => 'pm_test_000004',
                        'stripe_customer_id' => 'cus_test_000004',
                        'status' => 'paid',
                        'amount' => 2980,
                        'currency' => 'GBP',
                    ],
                ],
            ],
            [
                'order_uid' => '11111111-1111-4111-8111-111111111115',
                'status' => 'failed',
                'payment_status' => 'failed',
                'items' => $this->shuffleAndTakeRandomCatalogItems($catalogItems, 1),
                'failure_code' => 'card_declined',
                'failure_message' => 'The card was declined by issuer.',
                'created_at' => now()->subDays(3),
                'paid_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2),
                'order_paymenets' => [
                    [
                        'uid' => '31111111-1111-4111-8111-111111111115',
                        'stripe_payment_method_id' => 'pm_test_000005',
                        'stripe_customer_id' => 'cus_test_000005',
                        'status' => 'failed',
                        'amount' => 990,
                        'currency' => 'GBP',
                        'failure_code' => 'card_declined',
                        'failure_message' => 'The card was declined by issuer.',
                    ],
                ],
            ],
            [
                'order_uid' => '11111111-1111-4111-8111-111111111116',
                'status' => 'refunded',
                'payment_status' => 'refunded',
                'items' => $this->shuffleAndTakeRandomCatalogItems($catalogItems, 1),
                'paid_at' => now()->subDays(7),
                'refunded_at' => now()->subDays(1),
                'order_paymenets' => [
                    [
                        'uid' => '31111111-1111-4111-8111-111111111116',
                        'stripe_payment_intent_id' => 'pi_test_111111111116',
                        'stripe_payment_method_id' => 'pm_test_000006',
                        'stripe_customer_id' => 'cus_test_000006',
                        'status' => 'paid',
                        'amount' => 990,
                        'currency' => 'GBP',
                        'paid_at' => now()->subDays(7),
                    ],
                    [
                        'uid' => '31111111-1111-4111-8111-111111111117',
                        'stripe_payment_intent_id' => 'pi_test_111111111117',
                        'stripe_payment_method_id' => 'pm_test_000007',
                        'stripe_customer_id' => 'cus_test_000007',
                        'status' => 'refunded',
                        'amount' => 990,
                        'currency' => 'GBP',
                        'refunded_at' => now()->subDays(1),
                    ],
                ],
            ],
        ];

        foreach ($scenarios as $index => $scenario) {
            $user = $this->randomUser($users);

            $items = collect($scenario['items'] ?? []);

            if ($items->isEmpty()) {
                $items = $this->shuffleAndTakeRandomCatalogItems(
                    $catalogItems,
                    1,
                    (int) ($scenario['quantity'] ?? 1)
                );
            }

            $subtotalAmount = $items->sum(
                fn (array $item): int => (int) $item['line_amount']
            );

            $discountAmount = (int) ($scenario['discount_amount'] ?? 0);
            $totalAmount = max(0, $subtotalAmount - $discountAmount);
            $order = Order::updateOrCreate(
                [
                    'uid' => $scenario['order_uid'],
                ],
                [
                    'discount_code_id' => $scenario['discount_code_id'] ?? null,
                    'quantity' => $items->sum(
                        fn (array $item): int => (int) $item['quantity']
                    ),

                    'amount' => $totalAmount,
                    'subtotal_amount' => $subtotalAmount,
                    'discount_amount' => $discountAmount,
                    'total_amount' => $totalAmount,
                    'currency' => strtoupper((string) ($firstCatalogItem?->currency ?? 'GBP')),
                    'status' => $scenario['status'],

                    'customer_name' => $user->name ?? 'Test User',
                    'customer_email' => $user->email,

                    'shipping_line_1' => '123 Test Street',
                    'shipping_line_2' => null,
                    'shipping_city' => 'London',
                    'shipping_state' => null,
                    'shipping_postal_code' => 'SW1A 1AA',
                    'shipping_country' => 'GB',

                    'discount_snapshot' => $scenario['discount_snapshot'] ?? null,
                    'paid_at' => $scenario['paid_at'] ?? null,
                ]
            );

            $order->items()->delete();

            foreach ($items as $itemIndex => $item) {
                $catalogItem = $item['catalog_item'];
                $quantity = (int) ($item['quantity'] ?? 1);
                $unitAmount = (int) ($catalogItem->price_amount ?? 0);
                $lineAmount = $unitAmount * $quantity;

                OrderItem::create([
                    'uid' => sprintf(
                        '31111111-1111-4111-8111-%012d',
                        (($index + 1) * 100) + $itemIndex
                    ),

                    'order_id' => $order->uid,
                    'catalog_item_id' => $catalogItem->uid,

                    'sku_snapshot' => $catalogItem->sku ?? $catalogItem->code ?? $catalogItem->uid,
                    'name_snapshot' => $catalogItem->title ?? 'Catalog item',

                    'unit_amount' => $unitAmount,
                    'quantity' => $quantity,
                    'line_amount' => $lineAmount,
                ]);
            }

            foreach ($scenario['order_paymenets'] as $paymentIndex => $paymentData) {
                OrderPayment::updateOrCreate(
                    [
                        'uid' => $paymentData['uid'],
                    ],
                    [
                        'order_id' => $order->uid,

                        'stripe_payment_intent_id' => $paymentData['stripe_payment_intent_id'] ?? 'pi_test_'.str_replace('-', '', $scenario['order_uid']),
                        'stripe_payment_method_id' => $paymentData['stripe_payment_method_id'] ?? 'pm_test_'.str_pad((string) ($index + 1), 6, '0', STR_PAD_LEFT),
                        'stripe_customer_id' => $paymentData['stripe_customer_id'] ?? 'cus_test_'.str_pad((string) ($index + 1), 6, '0', STR_PAD_LEFT),

                        'status' => $paymentData['status'] ?? $scenario['payment_status'],
                        'amount' => $paymentData['amount'] ?? $totalAmount,
                        'currency' => strtoupper((string) ($firstCatalogItem?->currency ?? 'GBP')),

                        'failure_code' => $paymentData['failure_code'] ?? null,
                        'failure_message' => $paymentData['failure_message'] ?? null,

                        'created_at' => $paymentData['created_at'] ?? now(),
                        'paid_at' => $paymentData['paid_at'] ?? now()->addMinutes(5),
                        'updated_at' => $paymentData['updated_at'] ?? now()->addMinutes(5),
                        'refunded_at' => $paymentData['refunded_at'] ?? null,
                    ]
                );
            }
        }
    }

    /**
     * Select a random user from the available users collection.
     */
    protected function randomUser(Collection $users): User
    {
        return $users->random();
    }

    /**
     * Shuffle the catalog items and take a specified number of random items.
     */
    protected function shuffleAndTakeRandomCatalogItems($catalogItems, int $count, int $quantity = 1)
    {
        return $catalogItems
            ->shuffle()
            ->take($count)
            ->map(function (CatalogItem $item) use ($quantity): array {
                $unitAmount = (int) ($item->price_amount ?? 0);

                return [
                    'product' => $item->sellable,
                    'catalog_item' => $item,
                    'quantity' => $quantity,
                    'line_amount' => $unitAmount * $quantity,
                ];
            })
            ->values();
    }
}
