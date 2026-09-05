<?php

namespace Tests\Unit\Models;

use App\Enums\AccountStatus;
use App\Enums\CloudStorageKind;
use App\Models\Order;
use App\Models\Plan;
use App\Models\User;
use App\Models\UserCloudUsage;
use App\Models\UserSubscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Tests\TestCase;

class UserStorageQuotaTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_upload_uses_kind_specific_usage_and_active_subscription_quota(): void
    {
        $user = User::factory()->create([
            'account_status' => AccountStatus::VERIFIED,
        ]);

        $plan = Plan::create([
            'title' => 'Storage Plan',
            'code' => 'STORAGE1',
            'quota' => 100,
            'duration' => 30,
        ]);
        foreach (CloudStorageKind::cases() as $kind) {
            $plan->entitlements()->create([
                'capability' => $kind->capability(),
                'quota' => 100,
            ]);
        }

        $order = Order::create([
            'quantity' => 1,
            'amount' => 0,
            'subtotal_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => 0,
            'currency' => 'gbp',
            'status' => 'paid',
        ]);

        UserSubscription::create([
            'uid' => (string) Str::uuid(),
            'user_id' => $user->uid,
            'plan_id' => $plan->uid,
            'order_id' => $order->uid,
            'status' => 'active',
            'expiration_date' => now()->addDay(),
        ]);

        UserCloudUsage::create([
            'uid' => (string) Str::uuid(),
            'user_id' => $user->uid,
            'share_usage' => 60,
            'backup_usage' => 90,
        ]);

        $this->assertTrue($user->canUpload(40, CloudStorageKind::SHARE));
        $this->assertFalse($user->canUpload(41, CloudStorageKind::SHARE));
        $this->assertSame(40, $user->remainingStorage(CloudStorageKind::SHARE));
        $this->assertSame(10, $user->remainingStorage(CloudStorageKind::BACKUP));
    }

    public function test_expired_active_subscription_is_not_treated_as_uploadable(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-26 12:00:00'));

        $user = User::factory()->create([
            'account_status' => AccountStatus::VERIFIED,
        ]);

        $plan = Plan::create([
            'title' => 'Expired Plan',
            'code' => 'EXPIRED1',
            'quota' => 100,
            'duration' => 30,
        ]);
        $plan->entitlements()->create([
            'capability' => CloudStorageKind::SHARE->capability(),
            'quota' => 100,
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

        UserSubscription::create([
            'uid' => (string) Str::uuid(),
            'user_id' => $user->uid,
            'plan_id' => $plan->uid,
            'order_id' => $order->uid,
            'status' => 'active',
            'expiration_date' => now()->subDay(),
        ]);

        UserCloudUsage::create([
            'uid' => (string) Str::uuid(),
            'user_id' => $user->uid,
            'share_usage' => 10,
            'backup_usage' => 0,
        ]);

        $this->assertNull($user->subscription);
        $this->assertFalse($user->canUpload(1));
        $this->assertSame(0, $user->remainingStorage());

        Carbon::setTestNow();
    }
}
