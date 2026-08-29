<?php

namespace Tests\Feature;

use App\Enums\AccountStatus;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class AdminLogsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_back_up_a_log_file(): void
    {
        $admin = $this->createAdminUser();
        config()->set('classer.admin_email', $admin->email);

        File::ensureDirectoryExists(storage_path('logs'));
        File::ensureDirectoryExists(storage_path('logs/backups'));

        $filename = 'admin-backup-test.log';
        $filePath = storage_path('logs/'.$filename);
        $fileContents = "[2026-08-29 06:23:36] production.INFO: Backup test\n";
        File::put($filePath, $fileContents);

        Carbon::setTestNow(Carbon::parse('2026-08-29 06:23:36'));

        $response = $this->actingAs($admin)->post(route('admin.logs.backup'), [
            'file' => $filename,
            'q' => '',
            'limit' => 50,
        ]);

        $response->assertRedirect(route('admin.logs', ['file' => $filename]))
            ->assertSessionHas('success', 'Log file backed up as backup-admin-backup-test-20260829_062336.log.');

        $backupPath = storage_path('logs/backups/backup-admin-backup-test-20260829_062336.log');

        $this->assertTrue(File::exists($filePath));
        $this->assertTrue(File::exists($backupPath));
        $this->assertSame($fileContents, File::get($backupPath));

        File::delete($filePath);
        File::delete($backupPath);
        Carbon::setTestNow();
    }

    protected function createAdminUser(): User
    {
        return User::create([
            'name' => 'Admin Tester',
            'email' => 'admin.'.uniqid().'@example.com',
            'password' => bcrypt('password123'),
            'account_status' => AccountStatus::VERIFIED,
        ]);
    }
}
