<?php

namespace Database\Seeders;

use App\Models\DiscountCode;
use App\Models\Plan;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class SetupAppSeeder extends Seeder
{
    /**
     * Setup discount codes for testing.
     */
    public function run(): void
    {
        $firstProduct = Product::with('catalogItem')->first();
        $firstPlan = Plan::with('catalogItem')->first();
        $firstUser = User::first();

        $discountCodes = [
            [
                'code' => 'SUMMER10',
                'is_active' => true,
                'discount_percentage' => 10,
                'max_discount_percentage' => 50,
                'usage_limit' => null,
                'usage_count' => 0,
                'one_use_per_customer' => false,
                'internal_note' => 'General active discount code.',
            ],
            [
                'code' => 'WELCOME5',
                'is_active' => true,
                'discount_percentage' => 5,
                'max_discount_percentage' => 20,
                'usage_limit' => null,
                'usage_count' => 0,
                'one_use_per_customer' => true,
                'internal_note' => 'For first-time customers only.',
            ],
            [
                'code' => 'FIRSTORDER15',
                'is_active' => true,
                'discount_percentage' => 15,
                'max_discount_percentage' => 30,
                'min_order_amount' => 5000,
                'catalog_item_id' => $firstProduct?->catalogItem?->uid,
                'usage_limit' => null,
                'usage_count' => 0,
                'one_use_per_customer' => true,
                'internal_note' => 'Product-specific first-order code with minimum order amount.',
            ],
            [
                'code' => 'PLAN20',
                'is_active' => true,
                'discount_percentage' => 20,
                'max_discount_percentage' => 99,
                'catalog_item_id' => $firstPlan?->catalogItem?->uid,
                'usage_limit' => null,
                'usage_count' => 0,
                'one_use_per_customer' => false,
                'internal_note' => 'For subscription plans only.',
            ],
            [
                'code' => 'EXPIRED50',
                'is_active' => false,
                'discount_percentage' => 50,
                'max_discount_percentage' => 99,
                'usage_limit' => null,
                'usage_count' => 0,
                'one_use_per_customer' => false,
                'starts_at' => now()->subDays(30),
                'expires_at' => now()->subDay(),
                'disabled_at' => now()->subDay(),
                'disabled_by_user_id' => $firstUser?->uid,
                'internal_note' => 'Expired code. Should not be usable.',
            ],
            [
                'code' => 'USERONLY25',
                'is_active' => true,
                'discount_percentage' => 25,
                'max_discount_percentage' => 99,
                'assigned_user_id' => $firstUser?->uid,
                'assigned_email' => $firstUser?->email,
                'usage_limit' => null,
                'usage_count' => 0,
                'one_use_per_customer' => false,
                'internal_note' => 'Assigned to a specific user.',
            ],
            [
                'code' => 'EMAILONLY15',
                'is_active' => true,
                'discount_percentage' => 15,
                'max_discount_percentage' => 50,
                'assigned_email' => 'customer@example.com',
                'usage_limit' => null,
                'usage_count' => 0,
                'one_use_per_customer' => false,
                'internal_note' => 'Assigned to a specific customer email.',
            ],
            [
                'code' => 'FUTURE20',
                'is_active' => true,
                'discount_percentage' => 20,
                'max_discount_percentage' => 75,
                'starts_at' => now()->addDays(7),
                'expires_at' => now()->addDays(30),
                'usage_limit' => null,
                'usage_count' => 0,
                'one_use_per_customer' => false,
                'internal_note' => 'Scheduled future discount. Should not be usable yet.',
            ],
            [
                'code' => 'USEDUP10',
                'is_active' => true,
                'discount_percentage' => 10,
                'max_discount_percentage' => 40,
                'usage_limit' => 5,
                'usage_count' => 5,
                'one_use_per_customer' => false,
                'internal_note' => 'Usage limit reached. Should not be usable.',
            ],
            [
                'code' => 'MINORDER20',
                'is_active' => true,
                'discount_percentage' => 20,
                'max_discount_percentage' => 100,
                'min_order_amount' => 10000,
                'usage_limit' => null,
                'usage_count' => 0,
                'one_use_per_customer' => false,
                'internal_note' => 'Requires minimum order amount only.',
            ],
            [
                'code' => 'NOCAP5',
                'is_active' => true,
                'discount_percentage' => 5,
                'max_discount_percentage' => null,
                'usage_limit' => null,
                'usage_count' => 0,
                'one_use_per_customer' => false,
                'internal_note' => 'No maximum discount cap.',
            ],
            [
                'code' => 'DISABLED30',
                'is_active' => false,
                'discount_percentage' => 30,
                'max_discount_percentage' => 99,
                'usage_limit' => null,
                'usage_count' => 0,
                'one_use_per_customer' => false,
                'disabled_at' => now(),
                'disabled_by_user_id' => $firstUser?->uid,
                'internal_note' => 'Manually disabled code. Not expired, but inactive.',
            ],
        ];

        foreach ($discountCodes as $codeData) {
            DiscountCode::updateOrCreate(
                [
                    'code' => $codeData['code'],
                ],
                [
                    'is_active' => $codeData['is_active'],
                    'discount_percentage' => $codeData['discount_percentage'],
                    'max_discount_percentage' => $codeData['max_discount_percentage'],
                    'usage_limit' => $codeData['usage_limit'] ?? null,
                    'usage_count' => $codeData['usage_count'] ?? 0,
                    'one_use_per_customer' => $codeData['one_use_per_customer'] ?? false,
                    'internal_note' => $codeData['internal_note'] ?? null,
                    'catalog_item_id' => $codeData['catalog_item_id'] ?? null,
                    'min_order_amount' => $codeData['min_order_amount'] ?? null,
                    'starts_at' => $codeData['starts_at'] ?? null,
                    'expires_at' => $codeData['expires_at'] ?? null,
                    'disabled_at' => $codeData['disabled_at'] ?? null,
                    'assigned_user_id' => $codeData['assigned_user_id'] ?? null,
                    'assigned_email' => $codeData['assigned_email'] ?? null,
                    'created_by_user_id' => $firstUser?->uid,
                    'updated_by_user_id' => $firstUser?->uid,
                    'disabled_by_user_id' => $codeData['disabled_by_user_id'] ?? null,
                ]
            );
        }
    }
}
