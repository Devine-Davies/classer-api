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
                'title' => 'Cloud Share',
                'quota' => 104857600,
            ],
            [
                'uid' => 'c5bf22dc-7abe-4999-a575',
                'code' => 'T01B3F5D',
                'title' => 'Cloud Share + Moments',
                'quota' => 209715200, // 200MB
            ],
            [
                'uid' => 'd2f3c5b8-9a1e-4c6b-8f3d',
                'code' => 'T01C4E6E',
                'title' => 'Cloud Backup',
                'quota' => 524288000, // 500MB
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
