<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SetupAppSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Plans
        $this->setupPlans();

        // Create default checkout product
        $this->setupProducts();

        // Seed core test accounts and additional dummy users for order scenarios.
        $this->call(SetupTestAccountsSeeder::class);

        // Seed order lifecycle scenarios for checkout/admin testing.
        $this->call(SetupOrdersSeeder::class);
    }

    /**
     * Setup the subscription codes and quotas
     */
    public function setupPlans(): void
    {
        $codes = [
            [
                'title' => 'Cloud Share',
                'quota' => 104857600,
                'type' => 'cloud_share',
                'duration' => 'monthly',
                'catalog_item' => [
                    'sku' => 'PLAN-CS-MONTHLY',
                    'slug' => 'plan-cloud-share-monthly',
                    'price_amount' => 990,
                    'promotion_percentage' => 0,
                    'currency' => 'gbp',
                    'is_active' => true,
                    'image_url' => null,
                    'promotion_eligible' => true,
                    'discount_code_eligible' => true,
                    'shipping_required' => false,
                ],
            ],
            [
                'title' => 'Backup Storage',
                'quota' => 1073741824,
                'type' => 'backup_storage',
                'duration' => 'monthly',
                'catalog_item' => [
                    'sku' => 'PLAN-BACKUP-MONTHLY',
                    'slug' => 'plan-backup-storage-monthly',
                    'price_amount' => 490,
                    'promotion_percentage' => 0,
                    'currency' => 'gbp',
                    'is_active' => true,
                    'image_url' => null,
                    'promotion_eligible' => true,
                    'discount_code_eligible' => true,
                    'shipping_required' => false,
                ],
            ],
            [
                'title' => 'AI Enhanced Search',
                'quota' => 1000000,
                'type' => 'ai_search',
                'duration' => 'monthly',
                'catalog_item' => [
                    'sku' => 'PLAN-AI-SEARCH-MONTHLY',
                    'slug' => 'plan-ai-enhanced-search-monthly',
                    'price_amount' => 290,
                    'promotion_percentage' => 0,
                    'currency' => 'gbp',
                    'is_active' => true,
                    'image_url' => null,
                    'promotion_eligible' => true,
                    'discount_code_eligible' => true,
                    'shipping_required' => false,
                ],
            ],
        ];

        foreach ($codes as $code) {
            $plan = Plan::create([
                'uid' => (string) Str::uuid(),
                'code' => Str::upper(Str::random(8)),
                'title' => $code['title'],
                'quota' => $code['quota'],
                'type' => $code['type'],
                'duration' => $code['duration'],
            ]);

            $plan->syncCatalogItem($code['catalog_item'] ?? []);
        }
    }

    /**
     * Setup one-time checkout products.
     */
    public function setupProducts(): void
    {
        $products = [
            [
                'uid' => '2f9d55af-bfc5-4e67-9025-7f053f2a9ca1',
                'sku' => 'CLS-HOME-001',
                'slug' => 'classer-home-device',
                'name' => 'Classer Home',
                'short_description' => 'Black finish, 2GB RAM, 32GB storage.',
                'long_description' => 'Classer Home device with black finish, 2GB RAM, and 32GB internal storage for smooth everyday performance.',
                'description' => 'Color Black • 2GB RAM • 32GB Internal Storage',
                'is_active' => true,
                'catalog_item' => [
                    'price_amount' => 12900,
                    'promotion_percentage' => 0,
                    'currency' => 'gbp',
                    'is_active' => true,
                    'promotion_eligible' => true,
                    'discount_code_eligible' => true,
                    'shipping_required' => true,
                ],
            ],
            [
                'uid' => 'c6cbf523-30fd-4ab6-9eb4-8fc8d09d7a44',
                'sku' => 'CLS-CS-6M-001',
                'slug' => 'classer-cloud-share-free-6m',
                'name' => 'Cloud Share - 6 Months',
                'short_description' => 'Enjoy 6 months of Classer Cloud Share to easily share your content with anyone, anywhere.',
                'long_description' => 'Share your Classer content with anyone, anywhere for six months with simple private link access.',
                'description' => 'Share your Classer content with anyone, anywhere for 6 months.',
                'is_active' => true,
                'catalog_item' => [
                    'price_amount' => 990,
                    'promotion_percentage' => 0,
                    'currency' => 'gbp',
                    'is_active' => true,
                    'promotion_eligible' => true,
                    'discount_code_eligible' => true,
                    'shipping_required' => true,
                ],
            ],
        ];

        foreach ($products as $product) {
            $catalogItem = $product['catalog_item'] ?? [];
            unset($product['catalog_item']);

            Product::updateOrCreate(
                ['uid' => $product['uid']],
                $product
            )->syncCatalogItem($catalogItem);
        }
    }
}
