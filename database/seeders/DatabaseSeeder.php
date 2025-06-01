<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Subscription;
use App\Models\UserSubscription;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        foreach (
            [[
                'uid' => Str::uuid(),
                'code' => 'T01' . $this->shortUuid(),
                'quota' => 104857600, // 100MB
            ]] as $type
        ) {
            Subscription::create($type);
        }

        User::create([
            'name' => 'Rhys Devine-Davies',
            'email' => 'rdd@example.com',
            // 'password' => bcrypt('password'),
            'password' => Hash::make('password'),
            'account_status' => 1,
        ]);

        $subscription = Subscription::all()->random()->first();
        $mainUser = User::find(1);

        // Create 12 user subscriptions for the main user
        // for the past 4 years, and renew them every 6 months
        $threeYears = 12 * 3; // 12 months * 3 years
        for ($i = $threeYears; $i >= 0; $i -= 6) {
            UserSubscription::create([
                'uid' => Str::uuid(),
                'user_id' => $mainUser->uid,
                'subscription_id' => $subscription->uid,
                'created_at' => now()->subMonths($i),
                'expiration_date' => now()->subMonths($i)->addDays(30), // 30 days from the created date     ]);
            ]);
        }

        $userSubscription = UserSubscription::where('user_id', $mainUser->uid)
            ->where('subscription_id', $subscription->uid)
            ->orderBy('created_at', 'desc')
            ->limit(1)
            ->first();

        $mainUser->subscription_id = $userSubscription->uid;
        $mainUser->save();
    }

    /**
     * Generate a short UUID
     */
    private function shortUuid(): string
    {
        return strtoupper(substr(Str::uuid(), 0, 5));
    }
}
