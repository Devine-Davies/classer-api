<?php

namespace Tests\Feature;

use App\Enums\AccountStatus;
use App\Enums\CloudBackupStatus;
use App\Enums\CloudEntityStatus;
use App\Enums\CloudShareStatus;
use App\Enums\CloudStorageKind;
use App\Exceptions\CloudStorageQuotaExceededException;
use App\Exceptions\InvalidCloudShareStateException;
use App\Jobs\CloudShare\CloudShareExpireUpload;
use App\Jobs\CloudShare\CloudShareVerifyUpload;
use App\Logging\AppLogger;
use App\Models\CloudBackup;
use App\Models\CloudShare;
use App\Models\Order;
use App\Models\Plan;
use App\Models\User;
use App\Models\UserCloudUsage;
use App\Models\UserSubscription;
use App\Services\CloudQuotaService;
use App\Services\CloudShareCleanupService;
use App\Services\CloudShareManagementService;
use App\Services\CloudStorageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Mockery;
use Tests\TestCase;

class CloudShareLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_reserves_quota_and_only_schedules_abandoned_upload_cleanup(): void
    {
        Queue::fake();
        [$user, $usage] = $this->subscribedUser(quota: 1_000, used: 100);
        $service = $this->serviceWithPresignedPayload(size: 400);

        $share = $service->create($user, 'resource-1', $this->entities(size: 400));

        $this->assertSame(400, $share->expected_size);
        $this->assertSame(CloudShareStatus::UPLOADING, $share->status);
        $this->assertNotNull($share->upload_expires_at);
        $this->assertSame(500, $usage->fresh()->share_usage);
        Queue::assertPushed(CloudShareExpireUpload::class, 1);
        Queue::assertNotPushed(CloudShareVerifyUpload::class);
    }

    public function test_create_rejects_an_upload_that_exceeds_locked_usage(): void
    {
        Queue::fake();
        [$user, $usage] = $this->subscribedUser(quota: 1_000, used: 800);
        $service = $this->serviceWithPresignedPayload(size: 400, expectedCalls: 0);

        try {
            $service->create($user, 'resource-1', $this->entities(size: 400));
            $this->fail('Expected quota exception was not thrown.');
        } catch (CloudStorageQuotaExceededException $exception) {
            $this->assertSame(400, $exception->attemptedBytes());
        }

        $this->assertSame(800, $usage->fresh()->share_usage);
        $this->assertDatabaseCount('cloud_share', 0);
        Queue::assertNothingPushed();
    }

    public function test_create_initializes_a_missing_usage_row_before_reserving_quota(): void
    {
        Queue::fake();
        [$user] = $this->subscribedUser(quota: 1_000, used: null);
        $service = $this->serviceWithPresignedPayload(size: 400);

        $service->create($user, 'resource-1', $this->entities(size: 400));

        $this->assertDatabaseHas('user_cloud_usages', [
            'user_id' => $user->uid,
            'share_usage' => 400,
            'backup_usage' => 0,
        ]);
    }

    public function test_complete_marks_share_validating_and_dispatches_verification(): void
    {
        Queue::fake();
        [$user] = $this->subscribedUser(quota: 1_000, used: 0);
        $service = $this->serviceWithPresignedPayload(size: 400);
        $share = $service->create($user, 'resource-1', $this->entities(size: 400));

        $completed = $service->complete($user, $share);

        $this->assertSame(CloudShareStatus::VALIDATING, $completed->status);
        $this->assertNotNull($completed->completed_at);
        Queue::assertPushed(CloudShareVerifyUpload::class, 1);
    }

    public function test_complete_is_idempotent_after_a_lost_response(): void
    {
        Queue::fake();
        [$user] = $this->subscribedUser(quota: 1_000, used: 0);
        $service = $this->serviceWithPresignedPayload(size: 400);
        $share = $service->create($user, 'resource-1', $this->entities(size: 400));

        $firstResponse = $service->complete($user, $share);
        $secondResponse = $service->complete($user, $share);

        $this->assertSame(CloudShareStatus::VALIDATING, $firstResponse->status);
        $this->assertSame(CloudShareStatus::VALIDATING, $secondResponse->status);
        Queue::assertPushed(CloudShareVerifyUpload::class, 1);
    }

    public function test_verify_marks_share_active_and_records_actual_size(): void
    {
        [$user] = $this->subscribedUser(quota: 1_000, used: 400);
        $share = CloudShare::create([
            'uid' => (string) Str::uuid(),
            'user_id' => $user->uid,
            'resource_id' => 'resource-1',
            'expected_size' => 400,
            'status' => CloudShareStatus::VALIDATING,
            'expires_at' => now()->addHour(),
        ]);
        $share->cloudEntities()->create([
            'uid' => (string) Str::uuid(),
            'key' => 'cloud-share/share/video.mp4',
            'object_role' => 'video',
            'original_name' => 'video.mp4',
            'mime_type' => 'video/mp4',
            'expected_size' => 400,
        ]);

        $storage = Mockery::mock(CloudStorageService::class);
        $storage->shouldReceive('headObject')->once()->andReturn((object) [
            'size' => 400,
            'e_tag' => 'etag-1',
        ]);
        $service = new CloudShareManagementService(new AppLogger, $storage, new CloudQuotaService);

        $service->verify($share);

        $share->refresh();
        $this->assertSame(CloudShareStatus::ACTIVE, $share->status);
        $this->assertSame(400, $share->actual_size);
        $this->assertNotNull($share->validated_at);
        $entity = $share->cloudEntities()->first();
        $this->assertSame('etag-1', $entity->e_tag);
        $this->assertSame(400, $entity->actual_size);
        $this->assertSame(CloudEntityStatus::ACTIVE, $entity->status);
        $this->assertNotNull($entity->uploaded_at);
        $this->assertNotNull($entity->validated_at);
    }

    public function test_verify_is_idempotent_for_an_active_share(): void
    {
        [$user] = $this->subscribedUser(quota: 1_000, used: 400);
        $share = $this->shareForUser($user, CloudShareStatus::ACTIVE);
        $storage = Mockery::mock(CloudStorageService::class);
        $storage->shouldNotReceive('headObject');
        $service = new CloudShareManagementService(new AppLogger, $storage, new CloudQuotaService);

        $service->verify($share);

        $this->assertSame(CloudShareStatus::ACTIVE, $share->fresh()->status);
    }

    public function test_expired_upload_is_claimed_before_cleanup_and_can_no_longer_complete(): void
    {
        [$user] = $this->subscribedUser(quota: 1_000, used: 400);
        $share = $this->shareForUser($user, CloudShareStatus::UPLOADING, now()->subMinute());
        $storage = Mockery::mock(CloudStorageService::class);
        $cleanup = new CloudShareCleanupService(new AppLogger, $storage, new CloudQuotaService);
        $management = new CloudShareManagementService(
            new AppLogger,
            $storage,
            new CloudQuotaService
        );

        $claimed = $cleanup->claimExpiredUpload($share);

        $this->assertSame(CloudShareStatus::CLEANING, $claimed?->status);
        $this->expectException(InvalidCloudShareStateException::class);
        $management->complete($user, $share);
    }

    public function test_cleanup_does_not_claim_a_completed_upload(): void
    {
        [$user] = $this->subscribedUser(quota: 1_000, used: 400);
        $share = $this->shareForUser($user, CloudShareStatus::VALIDATING, now()->subMinute());
        $cleanup = new CloudShareCleanupService(
            new AppLogger,
            Mockery::mock(CloudStorageService::class),
            new CloudQuotaService
        );

        $this->assertNull($cleanup->claimExpiredUpload($share));
        $this->assertSame(CloudShareStatus::VALIDATING, $share->fresh()->status);
    }

    public function test_cleanup_finalization_releases_quota_only_once(): void
    {
        [$user, $usage] = $this->subscribedUser(quota: 1_000, used: 500);
        $share = $this->shareForUser($user, CloudShareStatus::CLEANING, now()->subMinute());
        $share->cloudEntities()->create([
            'uid' => (string) Str::uuid(),
            'key' => 'cloud-share/'.$share->uid.'/video.mp4',
            'object_role' => 'video',
            'expected_size' => 400,
        ]);
        $cleanup = new CloudShareCleanupService(
            new AppLogger,
            Mockery::mock(CloudStorageService::class),
            new CloudQuotaService
        );

        $cleanup->finalize($share);
        $cleanup->finalize($share);

        $this->assertSame(100, $usage->fresh()->share_usage);
        $this->assertSoftDeleted('cloud_share', ['id' => $share->id]);
    }

    public function test_expired_share_cleanup_does_not_touch_active_cloud_backup(): void
    {
        Storage::fake('user-storage');
        [$user, $usage] = $this->subscribedUser(quota: 2_000, used: 400, backupUsed: 400);
        $backup = CloudBackup::create([
            'uid' => (string) Str::uuid(),
            'user_id' => $user->uid,
            'resource_id' => 'backup-resource',
            'expected_size' => 400,
            'actual_size' => 400,
            'status' => CloudBackupStatus::ACTIVE,
        ]);
        $backupEntity = $backup->cloudEntities()->create([
            'uid' => (string) Str::uuid(),
            'key' => "classermedia.com/cloud-backups/{$backup->uid}/video.mp4",
            'object_role' => 'video',
            'expected_size' => 400,
            'actual_size' => 400,
            'status' => CloudEntityStatus::ACTIVE,
        ]);
        $share = $this->shareForUser(
            $user,
            CloudShareStatus::UPLOADING,
            now()->subMinute()
        );
        $shareEntity = $share->cloudEntities()->create([
            'uid' => (string) Str::uuid(),
            'key' => "classermedia.com/cloud-share/{$share->uid}/video.mp4",
            'object_role' => 'video',
            'expected_size' => 400,
        ]);
        Storage::disk('user-storage')->put($backupEntity->key, 'backup');
        Storage::disk('user-storage')->put($shareEntity->key, 'share');
        $storage = new CloudStorageService(new AppLogger);

        (new CloudShareExpireUpload($share))->handle(
            new CloudShareCleanupService(new AppLogger, $storage, new CloudQuotaService)
        );

        $this->assertSoftDeleted('cloud_share', ['id' => $share->id]);
        $this->assertSoftDeleted('cloud_entities', ['id' => $shareEntity->id]);
        $this->assertDatabaseHas('cloud_backups', [
            'id' => $backup->id,
            'status' => CloudBackupStatus::ACTIVE->value,
            'deleted_at' => null,
        ]);
        $this->assertDatabaseHas('cloud_entities', [
            'id' => $backupEntity->id,
            'deleted_at' => null,
        ]);
        Storage::disk('user-storage')->assertMissing($shareEntity->key);
        Storage::disk('user-storage')->assertExists($backupEntity->key);
        $this->assertSame(0, $usage->fresh()->share_usage);
        $this->assertSame(400, $usage->fresh()->backup_usage);
        $this->assertSame(400, $usage->fresh()->total_usage);
    }

    private function serviceWithPresignedPayload(int $size, int $expectedCalls = 1): CloudShareManagementService
    {
        $storage = Mockery::mock(CloudStorageService::class);
        $storage->shouldReceive('createUploadUrl')
            ->times($expectedCalls)
            ->andReturn('https://upload.example.test');

        return new CloudShareManagementService(new AppLogger, $storage, new CloudQuotaService);
    }

    private function entities(int $size): array
    {
        return [[
            'uid' => 'entity-1',
            'sourceFile' => '/tmp/video.mp4',
            'contentType' => 'video/mp4',
            'size' => $size,
        ]];
    }

    private function shareForUser(
        User $user,
        CloudShareStatus $status,
        $uploadExpiresAt = null
    ): CloudShare {
        return CloudShare::create([
            'uid' => (string) Str::uuid(),
            'user_id' => $user->uid,
            'resource_id' => 'resource-1',
            'expected_size' => 400,
            'status' => $status,
            'upload_expires_at' => $uploadExpiresAt,
            'expires_at' => now()->addHour(),
        ]);
    }

    private function subscribedUser(int $quota, ?int $used, int $backupUsed = 0): array
    {
        $user = User::factory()->create([
            'account_status' => AccountStatus::VERIFIED,
        ]);
        $plan = Plan::create([
            'title' => 'Cloud Share',
            'code' => 'SHARE-'.Str::random(8),
            'quota' => $quota,
            'duration' => 3600,
        ]);
        $plan->entitlements()->create([
            'capability' => CloudStorageKind::SHARE->capability(),
            'quota' => $quota,
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
            'expiration_date' => now()->addDay(),
        ]);
        $usage = $used === null
            ? null
            : UserCloudUsage::create([
                'uid' => (string) Str::uuid(),
                'user_id' => $user->uid,
                'share_usage' => $used,
                'backup_usage' => $backupUsed,
            ]);

        return [$user->fresh(), $usage];
    }
}
