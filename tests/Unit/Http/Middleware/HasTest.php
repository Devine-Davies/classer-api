<?php

namespace Tests\Unit\Http\Middleware;

use App\Enums\AccountStatus;
use App\Http\Middleware\Has;
use App\Models\Order;
use App\Models\Plan;
use App\Models\PlanEntitlement;
use App\Models\User;
use App\Models\UserCloudUsage;
use App\Models\UserSubscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Tests\TestCase;

class HasTest extends TestCase
{
    use RefreshDatabase;

    public function test_cloud_capabilities_use_entitlement_and_not_combined_usage(): void
    {
        $user = $this->subscribedUser(quota: 100, entitlements: [
            'cloud_share' => 100,
            'cloud_backup' => 100,
        ]);
        UserCloudUsage::create([
            'uid' => (string) Str::uuid(),
            'user_id' => $user->uid,
            'share_usage' => 100,
            'backup_usage' => 100,
        ]);

        $this->assertSame(204, $this->handle($user, 'cloudShare')->getStatusCode());
        $this->assertSame(204, $this->handle($user, 'cloudBackup')->getStatusCode());
    }

    public function test_cloud_capabilities_require_the_matching_entitlement(): void
    {
        $user = $this->subscribedUser(quota: 100, entitlements: ['cloud_share' => 100]);

        $response = $this->handle($user, 'cloudBackup');

        $this->assertSame(403, $response->getStatusCode());
        $this->assertSame([
            'status' => false,
            'message' => 'You do not have access to Cloud Backup.',
        ], $response->getData(true));
    }

    public function test_cloud_capabilities_allow_an_entitled_plan_with_zero_quota(): void
    {
        $user = $this->subscribedUser(quota: 0, entitlements: ['cloud_backup' => 0]);

        $this->assertSame(204, $this->handle($user, 'cloudBackup')->getStatusCode());
    }

    private function handle(User $user, string $capability)
    {
        $request = Request::create('/');
        $request->setUserResolver(fn (): User => $user);

        return (new Has)->handle($request, fn () => response()->noContent(), $capability);
    }

    private function subscribedUser(int $quota, array $entitlements = []): User
    {
        $user = User::factory()->create([
            'account_status' => AccountStatus::VERIFIED,
        ]);
        $plan = Plan::create([
            'title' => 'Cloud Plan',
            'code' => 'CLOUD-'.Str::random(8),
            'quota' => $quota,
            'duration' => 3600,
        ]);
        $order = Order::create([
            'quantity' => 1,
            'amount' => 0,
            'subtotal_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => 0,
            'currency' => 'gbp',
            'status' => 'paid',
        ]);

        foreach ($entitlements as $capability => $entitlementQuota) {
            PlanEntitlement::create([
                'plan_id' => $plan->uid,
                'capability' => $capability,
                'quota' => $entitlementQuota,
            ]);
        }

        UserSubscription::create([
            'uid' => (string) Str::uuid(),
            'user_id' => $user->uid,
            'plan_id' => $plan->uid,
            'order_id' => $order->uid,
            'status' => 'active',
            'expiration_date' => now()->addHour(),
        ]);

        return $user->fresh();
    }
}
