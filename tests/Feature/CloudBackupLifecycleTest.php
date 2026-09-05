<?php

namespace Tests\Feature;

use App\Enums\AccountStatus;
use App\Enums\CloudBackupStatus;
use App\Enums\CloudEntityStatus;
use App\Enums\CloudStorageKind;
use App\Exceptions\CloudStorageQuotaExceededException;
use App\Exceptions\InvalidCloudBackupStateException;
use App\Jobs\CloudBackup\CloudBackupVerifyUpload;
use App\Models\CloudBackup;
use App\Models\Order;
use App\Models\Plan;
use App\Models\User;
use App\Models\UserCloudUsage;
use App\Models\UserSubscription;
use App\Services\CloudBackupManagementService;
use App\Services\CloudQuotaService;
use App\Services\CloudStorageService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class CloudBackupLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_reserves_shared_quota_and_returns_upload_metadata(): void
    {
        [$user, $usage] = $this->subscribedUser(quota: 1_000, used: 100);
        $storage = Mockery::mock(CloudStorageService::class);
        $storage->shouldReceive('createUploadUrl')
            ->once()
            ->withArgs(fn (string $key, string $contentType): bool => str_starts_with(
                $key,
                'classermedia.com/cloud-backups/'
            ) && $contentType === 'video/mp4')
            ->andReturn('https://upload.example.test');

        $backup = $this->service($storage)->create(
            $user,
            'resource-1',
            $this->entities(400)
        );

        $this->assertSame(CloudBackupStatus::UPLOADING, $backup->status);
        $this->assertSame(400, $backup->expected_size);
        $this->assertSame(500, $usage->fresh()->backup_usage);
        $this->assertSame('https://upload.example.test', $backup->cloudEntities->first()->upload_url);
    }

    public function test_create_rejects_quota_before_generating_upload_urls(): void
    {
        [$user, $usage] = $this->subscribedUser(quota: 1_000, used: 800);
        $storage = Mockery::mock(CloudStorageService::class);
        $storage->shouldNotReceive('createUploadUrl');

        $this->expectException(CloudStorageQuotaExceededException::class);

        try {
            $this->service($storage)->create($user, 'resource-1', $this->entities(400));
        } finally {
            $this->assertSame(800, $usage->fresh()->backup_usage);
            $this->assertDatabaseCount('cloud_backups', 0);
        }
    }

    public function test_create_rejects_a_second_backup_for_the_same_resource(): void
    {
        [$user, $usage] = $this->subscribedUser(quota: 1_000, used: 400);
        $this->backupForUser($user, CloudBackupStatus::ACTIVE);
        $storage = Mockery::mock(CloudStorageService::class);
        $storage->shouldNotReceive('createUploadUrl');

        $this->expectException(InvalidCloudBackupStateException::class);

        try {
            $this->service($storage)->create($user, 'resource-1', $this->entities(400));
        } finally {
            $this->assertSame(400, $usage->fresh()->backup_usage);
            $this->assertDatabaseCount('cloud_backups', 1);
        }
    }

    public function test_complete_is_idempotent_and_dispatches_verification_once(): void
    {
        Queue::fake();
        [$user] = $this->subscribedUser(quota: 1_000, used: 400);
        $backup = $this->backupForUser($user, CloudBackupStatus::UPLOADING);
        $service = $this->service(Mockery::mock(CloudStorageService::class));

        $first = $service->complete($user, $backup);
        $second = $service->complete($user, $backup);

        $this->assertSame(CloudBackupStatus::VALIDATING, $first->status);
        $this->assertSame(CloudBackupStatus::VALIDATING, $second->status);
        $this->assertNotNull($first->completed_at);
        Queue::assertPushed(CloudBackupVerifyUpload::class, function (CloudBackupVerifyUpload $job): bool {
            return $job->connection === 'cloudbackup';
        });
    }

    public function test_verify_records_entity_metadata_and_activates_backup(): void
    {
        [$user] = $this->subscribedUser(quota: 1_000, used: 400);
        $backup = $this->backupForUser($user, CloudBackupStatus::VALIDATING);
        $this->entityForBackup($backup);
        $storage = Mockery::mock(CloudStorageService::class);
        $storage->shouldReceive('headObject')->once()->andReturn((object) [
            'size' => 400,
            'e_tag' => 'etag-1',
        ]);

        $this->service($storage)->verify($backup);

        $backup->refresh();
        $entity = $backup->cloudEntities()->first();
        $this->assertSame(CloudBackupStatus::ACTIVE, $backup->status);
        $this->assertSame(400, $backup->actual_size);
        $this->assertNotNull($backup->validated_at);
        $this->assertSame(CloudEntityStatus::ACTIVE, $entity->status);
        $this->assertSame(400, $entity->actual_size);
        $this->assertSame('etag-1', $entity->e_tag);
    }

    public function test_verify_is_idempotent_for_an_active_backup(): void
    {
        [$user] = $this->subscribedUser(quota: 1_000, used: 400);
        $backup = $this->backupForUser($user, CloudBackupStatus::ACTIVE);
        $storage = Mockery::mock(CloudStorageService::class);
        $storage->shouldNotReceive('headObject');

        $this->service($storage)->verify($backup);

        $this->assertSame(CloudBackupStatus::ACTIVE, $backup->fresh()->status);
    }

    public function test_restore_returns_fresh_download_manifest_and_records_restore(): void
    {
        [$user] = $this->subscribedUser(quota: 1_000, used: 400);
        $backup = $this->backupForUser($user, CloudBackupStatus::ACTIVE);
        $this->entityForBackup($backup, actualSize: 400);
        $storage = Mockery::mock(CloudStorageService::class);
        $storage->shouldReceive('createDownloadUrl')
            ->once()
            ->andReturn('https://download.example.test');

        $manifest = $this->service($storage)->restore($user, $backup);

        $this->assertSame('https://download.example.test', $manifest[0]['downloadUrl']);
        $this->assertSame('video.mp4', $manifest[0]['originalName']);
        $this->assertSame(CloudBackupStatus::ACTIVE, $backup->fresh()->status);
        $this->assertNotNull($backup->fresh()->last_restored_at);
    }

    public function test_delete_removes_objects_and_releases_quota_once(): void
    {
        [$user, $usage] = $this->subscribedUser(quota: 1_000, used: 500);
        $backup = $this->backupForUser($user, CloudBackupStatus::ACTIVE);
        $this->entityForBackup($backup);
        $storage = Mockery::mock(CloudStorageService::class);
        $storage->shouldReceive('deleteObject')->once()->andReturnTrue();
        $storage->shouldReceive('createUploadUrl')->once()->andReturn('https://upload.example.test');

        $service = $this->service($storage);
        $service->delete($user, $backup);

        $this->assertSame(100, $usage->fresh()->backup_usage);
        $this->assertSoftDeleted('cloud_backups', ['id' => $backup->id]);
        $this->assertDatabaseHas('cloud_backups', [
            'id' => $backup->id,
            'active_resource_key' => null,
        ]);
        $this->assertSoftDeleted('cloud_entities', ['cloudable_id' => $backup->id]);

        $replacement = $service->create($user, 'resource-1', $this->entities(400));

        $this->assertSame('resource-1', $replacement->resource_id);
        $this->assertSame(500, $usage->fresh()->backup_usage);
    }

    public function test_failed_object_deletion_keeps_backup_retryable_and_quota_reserved(): void
    {
        [$user, $usage] = $this->subscribedUser(quota: 1_000, used: 500);
        $backup = $this->backupForUser($user, CloudBackupStatus::ACTIVE);
        $this->entityForBackup($backup);
        $storage = Mockery::mock(CloudStorageService::class);
        $storage->shouldReceive('deleteObject')->once()->andReturnFalse();

        try {
            $this->service($storage)->delete($user, $backup);
            $this->fail('Expected object deletion to fail.');
        } catch (RuntimeException) {
            $this->assertSame(CloudBackupStatus::SCHEDULED_FOR_DELETION, $backup->fresh()->status);
            $this->assertSame(500, $usage->fresh()->backup_usage);
            $this->assertDatabaseHas('cloud_backups', [
                'id' => $backup->id,
                'deleted_at' => null,
            ]);
        }
    }

    public function test_owner_checks_prevent_cross_account_restore(): void
    {
        [$owner] = $this->subscribedUser(quota: 1_000, used: 400);
        [$otherUser] = $this->subscribedUser(quota: 1_000, used: 0);
        $backup = $this->backupForUser($owner, CloudBackupStatus::ACTIVE);

        $this->expectException(AuthorizationException::class);
        $this->service(Mockery::mock(CloudStorageService::class))
            ->restore($otherUser, $backup);
    }

    private function service(CloudStorageService $storage): CloudBackupManagementService
    {
        return new CloudBackupManagementService($storage, new CloudQuotaService);
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

    private function backupForUser(User $user, CloudBackupStatus $status): CloudBackup
    {
        return CloudBackup::create([
            'uid' => (string) Str::uuid(),
            'user_id' => $user->uid,
            'resource_id' => 'resource-1',
            'expected_size' => 400,
            'status' => $status,
        ]);
    }

    private function entityForBackup(CloudBackup $backup, ?int $actualSize = null): void
    {
        $backup->cloudEntities()->create([
            'uid' => (string) Str::uuid(),
            'key' => "classermedia.com/cloud-backups/{$backup->uid}/video.mp4",
            'object_role' => 'video',
            'original_name' => 'video.mp4',
            'mime_type' => 'video/mp4',
            'expected_size' => 400,
            'actual_size' => $actualSize,
            'status' => $actualSize === null
                ? CloudEntityStatus::UPLOADING
                : CloudEntityStatus::ACTIVE,
        ]);
    }

    private function subscribedUser(int $quota, int $used): array
    {
        $user = User::factory()->create([
            'account_status' => AccountStatus::VERIFIED,
        ]);
        $plan = Plan::create([
            'title' => 'Cloud Storage '.Str::random(8),
            'code' => 'BACKUP-'.Str::random(8),
            'quota' => $quota,
            'duration' => 3600,
        ]);
        $plan->entitlements()->create([
            'capability' => CloudStorageKind::BACKUP->capability(),
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
        $usage = UserCloudUsage::create([
            'uid' => (string) Str::uuid(),
            'user_id' => $user->uid,
            'share_usage' => 0,
            'backup_usage' => $used,
        ]);

        return [$user->fresh(), $usage];
    }
}
