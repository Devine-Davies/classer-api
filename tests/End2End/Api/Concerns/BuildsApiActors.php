<?php

namespace Tests\End2End\Api\Concerns;

use App\Enums\AccountStatus;
use App\Models\Plan;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

trait BuildsApiActors
{
    protected function makeVerifiedUser(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'uid' => (string) Str::uuid(),
            'password' => 'password123',
            'account_status' => AccountStatus::VERIFIED,
        ], $overrides));
    }

    protected function actingAsUser(?User $user = null, array $abilities = ['user']): User
    {
        $user ??= $this->makeVerifiedUser();

        Sanctum::actingAs($user, $abilities);

        return $user;
    }

    protected function createActiveSubscriptionFor(User $user, int $quotaBytes = 52428800): UserSubscription
    {
        $plan = Plan::create([
            'uid' => (string) Str::uuid(),
            'title' => 'E2E Plan',
            'code' => 'E2E-'.strtoupper(Str::random(5)),
            'quota' => $quotaBytes,
            'type' => 'cloud_share',
            'duration' => '30 days',
        ]);

        return UserSubscription::create([
            'uid' => (string) Str::uuid(),
            'user_id' => $user->uid,
            'plan_id' => $plan->uid,
            'order_id' => (string) Str::uuid(),
            'status' => 'active',
            'expiration_date' => now()->addDays(30),
        ]);
    }
}
