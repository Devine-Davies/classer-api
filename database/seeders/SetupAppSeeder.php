<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Subscription;

class SetupAppSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Subscriptions
        $this->setupSubscription();
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
            if (!Subscription::where('code', $code['code'])->exists()) {
                Subscription::create([
                    'uid' => $code['uid'],
                    'code' => $code['code'],
                    'title' => $code['title'],
                    'quota' => $code['quota'],
                ]);
            }
        }
    }
}
