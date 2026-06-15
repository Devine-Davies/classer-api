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
        $plans = [
            [
                'title' => 'Cloud Share Taster',
                'quota' => 104857600,
                'type' => 'cloud_share',
                'duration' => 3 * 30 * 24 * 60 * 60, // 3 months in seconds
                'catalog_item' => [
                    'price_amount' => 990,
                    'is_published' => true,
                ],
            ],
            [
                'title' => 'Classer Share Pro',
                'quota' => 1073741824,
                'type' => 'cloud_share',
                'duration' => 6 * 30 * 24 * 60 * 60, // 6 months in seconds
                'catalog_item' => [
                    'price_amount' => 1990,
                    'is_published' => true,
                ],
            ],
            [
                'title' => 'AI Enhanced Search',
                'quota' => 1000000,
                'type' => 'ai_search',
                'duration' => 3 * 30 * 24 * 60 * 60, // 3 months in seconds
                'catalog_item' => [
                    'price_amount' => 2990,
                    'is_published' => true,
                ],
            ],
        ];

        foreach ($plans as $planData) {
            $plan = Plan::create([
                'code' => Str::upper(Str::random(8)),
                'title' => $planData['title'],
                'quota' => $planData['quota'],
                'type' => $planData['type'],
                'duration' => $planData['duration'],
            ]);

            $plan->syncCatalogItem($planData['catalog_item'] ?? []);
        }
    }

    /**
     * Setup one-time checkout products.
     */
    public function setupProducts(): void
    {
        $products = [
            [
                'title' => 'Classer Home Ultimate',
                'short_description' => 'Enjoy 6 months of Classer Backup Storage to securely store your content in the cloud.',
                'description' => 'Securely store your Classer content in the cloud for six months with easy access and peace of mind.',
                'catalog_item' => [
                    'price_amount' => 23990,
                    'is_published' => false,
                ],
            ],
            [
                'title' => 'Classer Home Pro',
                'short_description' => 'Enjoy 6 months of Classer Cloud Share to easily share your content with anyone, anywhere.',
                'description' => 'Share your Classer content with anyone, anywhere for six months with simple private link access.',
                'catalog_item' => [
                    'price_amount' => 17990,
                    'is_published' => true,
                ],
            ],
            [
                'title' => 'Classer Home',
                'short_description' => 'Black finish, 2GB RAM, 32GB storage.',
                'description' => 'Classer Home device with black finish, 2GB RAM, and 32GB internal storage for smooth everyday performance.',
                'catalog_item' => [
                    'price_amount' => 12990,
                    'is_published' => true,
                ],
            ],
        ];

        foreach ($products as $productData) {
            $product = Product::create([
                'title' => $productData['title'],
                'short_description' => $productData['short_description'],
                'description' => $productData['description'],
            ]);

            $product->syncCatalogItem($productData['catalog_item'] ?? []);
        }
    }
}
