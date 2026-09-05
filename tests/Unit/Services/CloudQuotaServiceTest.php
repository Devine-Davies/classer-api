<?php

namespace Tests\Unit\Services;

use App\Enums\AccountStatus;
use App\Enums\CloudStorageKind;
use App\Exceptions\CloudStorageQuotaExceededException;
use App\Models\Order;
use App\Models\Plan;
use App\Models\PlanEntitlement;
use App\Models\User;
use App\Models\UserCloudUsage;
use App\Models\UserSubscription;
use App\Services\CloudQuotaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CloudQuotaServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_reserve_and_remaining_are_isolated_by_storage_kind(): void
    {
        [$user, $usage] = $this->subscribedUser(1_000, 100, 700);
        $service = new CloudQuotaService;

        $service->reserve($user, CloudStorageKind::SHARE, 300);

        $usage->refresh();
        $this->assertSame(400, $usage->share_usage);
        $this->assertSame(700, $usage->backup_usage);
        $this->assertSame(600, $service->remaining($user, CloudStorageKind::SHARE));
        $this->assertSame(300, $service->remaining($user, CloudStorageKind::BACKUP));
    }

    public function test_reserve_enforces_each_kind_quota_independently(): void
    {
        [$user, $usage] = $this->subscribedUser(1_000, 900, 100);
        $service = new CloudQuotaService;

        try {
            $service->reserve($user, CloudStorageKind::SHARE, 101);
            $this->fail('Expected quota exception was not thrown.');
        } catch (CloudStorageQuotaExceededException $exception) {
            $this->assertSame(101, $exception->attemptedBytes());
        }

        $service->reserve($user, CloudStorageKind::BACKUP, 101);

        $usage->refresh();
        $this->assertSame(900, $usage->share_usage);
        $this->assertSame(201, $usage->backup_usage);
    }

    public function test_release_floors_selected_usage_at_zero(): void
    {
        [$user, $usage] = $this->subscribedUser(1_000, 100, 500);

        (new CloudQuotaService)->release($user, CloudStorageKind::SHARE, 200);

        $usage->refresh();
        $this->assertSame(0, $usage->share_usage);
        $this->assertSame(500, $usage->backup_usage);
    }

    public function test_reserve_creates_a_missing_usage_row(): void
    {
        [$user] = $this->subscribedUser(1_000);

        (new CloudQuotaService)->reserve($user, CloudStorageKind::BACKUP, 250);

        $this->assertDatabaseHas('user_cloud_usages', [
            'user_id' => $user->uid,
            'share_usage' => 0,
            'backup_usage' => 250,
        ]);
    }

    public function test_reserve_uses_the_matching_entitlement_quota(): void
    {
        [$user] = $this->subscribedUser(100, backupQuota: 500);
        $service = new CloudQuotaService;

        $service->reserve($user, CloudStorageKind::BACKUP, 500);

        $this->assertSame(100, $service->quota($user, CloudStorageKind::SHARE));
        $this->assertSame(500, $service->quota($user, CloudStorageKind::BACKUP));
    }

    private function subscribedUser(
        int $quota,
        ?int $shareUsage = null,
        int $backupUsage = 0,
        ?int $backupQuota = null
    ): array {
        $user = User::factory()->create([
            'account_status' => AccountStatus::VERIFIED,
        ]);
        $plan = Plan::create([
            'title' => 'Storage Plan',
            'code' => 'STORAGE-'.Str::random(8),
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

        foreach (CloudStorageKind::cases() as $kind) {
            PlanEntitlement::create([
                'plan_id' => $plan->uid,
                'capability' => $kind->capability(),
                'quota' => $kind === CloudStorageKind::BACKUP
                    ? $backupQuota ?? $quota
                    : $quota,
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

        $usage = $shareUsage === null
            ? null
            : UserCloudUsage::create([
                'uid' => (string) Str::uuid(),
                'user_id' => $user->uid,
                'share_usage' => $shareUsage,
                'backup_usage' => $backupUsage,
            ]);

        return [$user, $usage];
    }
}
