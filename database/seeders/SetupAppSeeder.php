<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Subscription;
use Illuminate\Database\Seeder;

class SetupAppSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Subscriptions
        $this->setupSubscription();

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
    public function setupSubscription(): void
    {
        $codes = [
            [
                'uid' => 'c1414b98-8654-4815-93a3',
                'code' => 'T017A42C',
                'title' => 'Classer Essentials',
                'quota' => 104857600,
            ],
        ];

        foreach ($codes as $code) {
            // Check if the subscription already exists
            if (! Subscription::where('code', $code['code'])->exists()) {
                Subscription::create([
                    'uid' => $code['uid'],
                    'code' => $code['code'],
                    'title' => $code['title'],
                    'quota' => $code['quota'],
                ]);
            }
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
                'slug' => 'classer-home',
                'name' => 'Classer Home',
                'short_description' => 'Black finish, 2GB RAM, 32GB storage.',
                'long_description' => 'Classer Home device with black finish, 2GB RAM, and 32GB internal storage for smooth everyday performance.',
                'description' => 'Color Black • 2GB RAM • 32GB Internal Storage',
                'purchase_type' => 'one_time',
                'price_amount' => 12900,
                'currency' => 'gbp',
                'is_active' => true,
            ],
            [
                'uid' => 'c6cbf523-30fd-4ab6-9eb4-8fc8d09d7a44',
                'sku' => 'CLS-CS-6M-001',
                'slug' => 'classer-cloud-share-six-mo',
                'name' => 'Classer Cloud Share Six Month',
                'short_description' => 'Cloud sharing access for six months.',
                'long_description' => 'Share your Classer content with anyone, anywhere for six months with simple private link access.',
                'description' => 'Share your Classer content with anyone, anywhere for 6 months.',
                'purchase_type' => 'monthly',
                'price_amount' => 990,
                'currency' => 'gbp',
                'is_active' => true,
            ],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(
                ['uid' => $product['uid']],
                $product
            );
        }
    }
}
