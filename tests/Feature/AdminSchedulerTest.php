<?php

namespace Tests\Feature;

use App\Enums\AccountStatus;
use App\Models\User;
use App\Services\Admin\SchedulerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class AdminSchedulerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_scheduler_jobs(): void
    {
        $admin = $this->createAdminUser();
        config()->set('classer.admin_email', $admin->email);
        config()->set('classer.scheduler', [
            'mail' => [
                'artisan' => [
                    'command' => 'queue:work',
                    'parameters' => [
                        'connection' => 'database',
                        '--queue' => 'mail',
                        '--stop-when-empty' => true,
                        '--sleep' => 1,
                        '--tries' => 3,
                        '--timeout' => 120,
                    ],
                ],
                'command' => 'queue:work database --queue=mail --stop-when-empty --sleep=1 --tries=3 --timeout=120',
                'expression' => '* * * * *',
                'withoutOverlapping' => 5,
                'background' => false,
                'output' => 'scheduler-mail.log',
            ],
            'cloudShareVerify' => [
                'artisan' => [
                    'command' => 'queue:work',
                    'parameters' => [
                        'connection' => 'cloudshare',
                        '--queue' => 'verify',
                        '--stop-when-empty' => true,
                        '--sleep' => 1,
                        '--tries' => 3,
                        '--timeout' => 300,
                    ],
                ],
                'command' => 'queue:work cloudshare --queue=verify --stop-when-empty --sleep=1 --tries=3 --timeout=300',
                'expression' => '0 */4 * * *',
                'withoutOverlapping' => 30,
                'background' => false,
                'output' => 'scheduler-cloud-share.log',
            ],
            'cloudShareExpire' => [
                'artisan' => [
                    'command' => 'queue:work',
                    'parameters' => [
                        'connection' => 'cloudshare',
                        '--queue' => 'expire',
                        '--stop-when-empty' => true,
                        '--sleep' => 1,
                        '--tries' => 3,
                        '--timeout' => 600,
                    ],
                ],
                'command' => 'queue:work cloudshare --queue=expire --stop-when-empty --sleep=1 --tries=3 --timeout=600',
                'expression' => '0 0 * * *',
                'withoutOverlapping' => 60,
                'background' => false,
                'output' => 'scheduler-cloud-share.log',
            ],
        ]);

        $response = $this->actingAs($admin)->get(route('admin.scheduler'));

        $response->assertOk()
            ->assertSeeText('Scheduler')
            ->assertSeeText('Run scheduler now')
            ->assertSeeText('mail')
            ->assertSeeText('Next run')
            ->assertSeeText('Cloud Share Verify')
            ->assertSeeText('Cloud Share Expire')
            ->assertSeeText('scheduler-mail.log')
            ->assertSeeText('scheduler-cloud-share.log')
            ->assertSeeText('scheduler-cloud-share.log');
    }

    public function test_admin_can_trigger_the_scheduler(): void
    {
        $admin = $this->createAdminUser();
        config()->set('classer.admin_email', $admin->email);

        $this->mock(SchedulerService::class, function ($mock): void {
            $mock->shouldReceive('triggerAllJobs')
                ->once()
                ->andReturn([
                    'exit_code' => 0,
                    'output' => '',
                ]);
        });

        $response = $this->actingAs($admin)->post(route('admin.scheduler.run'));

        $response->assertRedirect(route('admin.scheduler'))
            ->assertSessionHas('success', 'Scheduler triggered successfully.');
    }

    public function test_cloud_scheduler_job_records_an_empty_successful_run(): void
    {
        $outputFile = 'scheduler-cloud-job-test.log';
        $outputPath = storage_path('logs/'.$outputFile);
        @unlink($outputPath);

        config()->set('classer.scheduler.cloudBackupVerify', [
            'artisan' => [
                'command' => 'queue:work',
                'parameters' => [],
            ],
            'logWhenRan' => true,
            'output' => $outputFile,
        ]);
        Artisan::shouldReceive('call')->once()->with('queue:work', [])->andReturn(0);
        Artisan::shouldReceive('output')->once()->andReturn('');

        try {
            $result = app(SchedulerService::class)->triggerJob('cloudBackupVerify');

            $this->assertSame(0, $result['exit_code']);
            $this->assertStringContainsString('[cloudBackupVerify] command=queue:work exit=0', (string) file_get_contents($outputPath));
        } finally {
            @unlink($outputPath);
        }
    }

    public function test_admin_can_trigger_a_single_scheduler_job(): void
    {
        $admin = $this->createAdminUser();
        config()->set('classer.admin_email', $admin->email);

        $this->mock(SchedulerService::class, function ($mock): void {
            $mock->shouldReceive('triggerJob')
                ->once()
                ->with('mail')
                ->andReturn([
                    'exit_code' => 0,
                    'output' => '',
                ]);
        });

        $response = $this->actingAs($admin)->post(route('admin.scheduler.jobs.run', ['job' => 'mail']));

        $response->assertRedirect(route('admin.scheduler'))
            ->assertSessionHas('success', 'Scheduler job triggered successfully.');
    }

    public function test_admin_sees_an_error_when_a_single_scheduler_job_fails(): void
    {
        $admin = $this->createAdminUser();
        config()->set('classer.admin_email', $admin->email);

        $this->mock(SchedulerService::class, function ($mock): void {
            $mock->shouldReceive('triggerJob')
                ->once()
                ->with('mail')
                ->andReturn([
                    'exit_code' => 0,
                    'output' => '2026-08-29 06:23:36 App\\Jobs\\MailUserSubscriptionActivated ......... RUNNING 2026-08-29 06:23:36 App\\Jobs\\MailUserSubscriptionActivated ..... 5.37ms FAIL',
                ]);
        });

        $response = $this->actingAs($admin)->post(route('admin.scheduler.jobs.run', ['job' => 'mail']));

        $response->assertRedirect(route('admin.scheduler'))
            ->assertSessionHas('error', 'That scheduler job could not be run successfully.');
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
