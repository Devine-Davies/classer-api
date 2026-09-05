<?php

namespace Tests\Feature;

use App\Enums\CloudBackupStatus;
use App\Enums\CloudEntityStatus;
use App\Jobs\CloudBackup\CloudBackupVerifyUpload;
use App\Models\CloudBackup;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminCloudBackupsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_view_and_filter_cloud_backups(): void
    {
        $admin = $this->createAdminUser();
        config()->set('classer.admin_email', $admin->email);
        $backup = $this->createBackup();
        $deletedBackup = $this->createBackup();
        $deletedBackup->delete();

        $this->actingAs($admin)
            ->get(route('admin.cloud-backups'))
            ->assertOk()
            ->assertSee($backup->uid)
            ->assertSee($deletedBackup->uid);

        $this->actingAs($admin)
            ->get(route('admin.cloud-backups', ['state' => 'deleted']))
            ->assertOk()
            ->assertSee($deletedBackup->uid)
            ->assertDontSee($backup->uid);

        $this->actingAs($admin)
            ->get(route('admin.cloud-backups.show', $backup->uid))
            ->assertOk()
            ->assertSee($backup->uid)
            ->assertSee('video.mp4');
    }

    public function test_admin_can_queue_backup_verification(): void
    {
        Queue::fake();
        $admin = $this->createAdminUser();
        config()->set('classer.admin_email', $admin->email);
        $backup = $this->createBackup();

        $this->actingAs($admin)
            ->post(route('admin.cloud-backups.verify', $backup->uid))
            ->assertRedirect();

        Queue::assertPushed(CloudBackupVerifyUpload::class, function (CloudBackupVerifyUpload $job): bool {
            return $job->connection === 'cloudbackup';
        });
    }

    private function createAdminUser(): User
    {
        return User::create([
            'uid' => (string) Str::uuid(),
            'name' => 'Admin Tester',
            'email' => 'admin.'.Str::random(8).'@example.com',
            'password' => bcrypt('password123'),
            'account_status' => 1,
        ]);
    }

    private function createBackup(): CloudBackup
    {
        $user = User::factory()->create();
        $backup = CloudBackup::create([
            'uid' => (string) Str::uuid(),
            'user_id' => $user->uid,
            'resource_id' => 'resource-'.Str::random(6),
            'expected_size' => 1024,
            'status' => CloudBackupStatus::ACTIVE,
        ]);

        $backup->cloudEntities()->create([
            'uid' => (string) Str::uuid(),
            'key' => "classermedia.com/cloud-backups/{$backup->uid}/video.mp4",
            'object_role' => 'video',
            'original_name' => 'video.mp4',
            'mime_type' => 'video/mp4',
            'expected_size' => 1024,
            'status' => CloudEntityStatus::ACTIVE,
        ]);

        return $backup;
    }
}
