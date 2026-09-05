<?php

namespace Tests\Feature;

use App\Enums\CloudEntityRole;
use App\Enums\CloudShareStatus;
use Database\Seeders\LiveBackupSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LiveBackupSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_imports_every_application_table_from_the_live_backup(): void
    {
        $backup = json_decode(
            file_get_contents(base_path('database/seeders/livebackup-data/04-09-2026_u329348820_classer_api.json')),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );

        $this->seed(LiveBackupSeeder::class);

        foreach ($backup as $entry) {
            if (($entry['type'] ?? null) !== 'table' || ($entry['name'] ?? null) === 'migrations') {
                continue;
            }

            $table = $entry['name'];

            if (! Schema::hasTable($table)) {
                continue;
            }

            $this->assertSame(
                count($entry['data'] ?? []),
                DB::table($table)->count(),
                "The {$table} table did not import every backup row.",
            );
        }

        $this->assertDatabaseMissing('cloud_share', [
            'status' => CloudShareStatus::PENDING->value,
        ]);
        $this->assertSame(
            DB::table('cloud_share')->count(),
            DB::table('cloud_share')->where('status', CloudShareStatus::ACTIVE->value)->count(),
        );
        $this->assertSame(37, DB::table('cloud_entities')->where('mime_type', 'video/mp4')->count());
        $this->assertSame(37, DB::table('cloud_entities')->where('mime_type', 'image/jpeg')->count());
        $this->assertSame(37, DB::table('cloud_entities')->where('object_role', CloudEntityRole::VIDEO->value)->count());
        $this->assertSame(37, DB::table('cloud_entities')->where('object_role', CloudEntityRole::THUMBNAIL->value)->count());
        $this->assertDatabaseHas('user_cloud_usages', [
            'share_usage' => 103033564,
            'backup_usage' => 0,
        ]);
        $this->assertSame(2, DB::table('plan_entitlements')
            ->where('capability', 'cloud_share')
            ->count());
        $this->assertSame(0, DB::table('plan_entitlements')
            ->where('capability', 'cloud_backup')
            ->count());
    }
}
