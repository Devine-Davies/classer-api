<?php

namespace Tests\Feature;

use App\Enums\AccountStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminStatsExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_export_the_filtered_user_stats_as_csv(): void
    {
        /** @var User $admin */
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'account_status' => AccountStatus::VERIFIED,
            'created_at' => '2026-08-01 12:00:00',
        ]);

        User::factory()->create([
            'account_status' => AccountStatus::VERIFIED,
            'created_at' => '2026-08-02 12:00:00',
        ]);

        User::factory()->create([
            'account_status' => AccountStatus::VERIFIED,
            'created_at' => '2026-07-31 12:00:00',
        ]);

        config()->set('classer.admin_email', $admin->email);

        $response = $this->actingAs($admin)->get(route('admin.stats.export', [
            'domain' => 'users',
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-02',
            'interval' => 'daily',
        ]));

        $response->assertOk()
            ->assertDownload('users-stats-2026-08-01-to-2026-08-02-daily.csv');

        $csv = $response->streamedContent();

        $this->assertStringContainsString('Period,"New Users"', $csv);
        $this->assertStringContainsString('"01 Aug 2026",1', $csv);
        $this->assertStringContainsString('"02 Aug 2026",1', $csv);
        $this->assertStringNotContainsString('31 Jul 2026', $csv);
    }
}
