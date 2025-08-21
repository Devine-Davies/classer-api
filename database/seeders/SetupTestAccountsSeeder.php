<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class SetupTestAccountsSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $testUsers = [
            [
                // This user has been listed as APP_ADMIN_EMAILS in .env giving it admin privileges
                'uid' => "AAAA4b98-8654-4815-AAAA",
                'name' => 'Sky Walker',
                'email' => 'skywalker@classermedia.com',
            ],
            [
                'uid' => "BBBB2dc-7abe-4999-BBBB",
                'name' => 'Sky Walker 1',
                'email' => 'skywalker+1@classer.com',
            ],
            [
                'uid' => "CCCC3b8-9a1e-4c6b-CCCC",
                'name' => 'Sky Walker 2',
                'email' => 'skywalker+2@example.com',
            ]
        ];

        collect($testUsers)->each(function ($user) {
            User::create([
                'uid' => $user['uid'],
                'name' => $user['name'],
                'email' => $user['email'],
                'password' => Hash::make('password1'), // Set a common password for all test users
                'account_status' => 1, // Active account status
            ]);
        });
    }
}







        // User::factory(10)->create();

        // foreach (
        //     [[
        //         'uid' => Str::uuid(),
        //         // 'code' => 'T01' . $this->shortUuid(),
        //         'code' => 'T017A42C',
        //         'title' => 'Cloud Share',
        //         'quota' => 104857600, // 100MB
        //     ]] as $type
        // ) {
        //     Subscription::create($type);
        // }

        // User::create([
        //     'uid' => Str::uuid(),
        //     'name' => 'Rhys(RD) Devine-Davies',
        //     'email' => 'rd@example.com',
        //     // 'password' => bcrypt('password'),
        //     'password' => Hash::make('password'),
        //     'account_status' => 1,
        // ]);


        // User::create([
        //     'uid' => Str::uuid(),
        //     'name' => 'Rhys() Devine-Davies',
        //     'email' => 'rdd@example.com',
        //     // 'password' => bcrypt('password'),
        //     'password' => Hash::make('password'),
        //     'account_status' => 1,
        // ]);

        // $subscription = Subscription::all()->random()->first();
        // $mainUser = User::where('email', 'rdd@example.com')->first();

        // PaymentMethod::create([
        //     'uid' => Str::uuid(),
        //     'user_id' => $mainUser->uid,
        //     'provider' => 'stripe',
        //     'type' => 'service',
        //     'stripe_customer_id' => 'cus_' . Str::random(16),
        //     'stripe_payment_method_id' => 'pm_' . Str::random(16),
        //     'stripe_transaction_id' => 'tr_' . Str::random(16),
        //     'created_at' => now()->subDays(30),
        //     'updated_at' => now()->subDays(30),
        // ]);

        // $paymentMethod = PaymentMethod::where('user_id', $mainUser->uid)
        //     ->where('provider', 'stripe')
        //     ->first();

        // // Create 12 user subscriptions for the main user
        // // for the past 4 years, and renew them every 6 months
        // $now = Carbon::now();
        // // $years = 12 * 4; //fourYears
        // // $years = 24; // two years
        // $years = 12; // one year
        // for ($i = $years; $i >= 0; $i -= 6) {
        //     $startDate = $now->copy()->subMonths($i);
        //     $endDate = $startDate->copy()->addMonths(6);
        //     UserSubscription::create([
        //         'uid'                       => Str::uuid(),
        //         'user_id'                   => $mainUser->uid,
        //         'subscription_id'           => $subscription->uid,
        //         'payment_method_id'         => $paymentMethod->uid,
        //         'status'                    => $endDate->isPast() ? 'expired' : 'active',
        //         'expiration_date'           => $endDate,
        //         'auto_renew'                => true,
        //         'auto_renew_date'           => $endDate,
        //         'transaction_id'            => 'pi_' . Str::random(16),
        //         'updated_by'                => 'system',
        //         'notes'                     => 'Seeded subscription for testing',
        //         'created_at'                => $startDate,
        //         'updated_at'                => $startDate,
        //     ]);
        // }