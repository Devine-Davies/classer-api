<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class CloudStorageEntitlementMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_missing_entitlement_table_and_backfills_cloud_plans(): void
    {
        $planId = $this->createCloudSharePlan(1024);
        Schema::drop('plan_entitlements');

        $migration = require database_path('migrations/2026_09_05_120000_add_cloud_storage_entitlements_to_existing_plans.php');
        $migration->up();
        $migration->up();

        $this->assertTrue(Schema::hasTable('plan_entitlements'));
        $this->assertSame(2, DB::table('plan_entitlements')->where('plan_id', $planId)->count());
        $this->assertDatabaseHas('plan_entitlements', [
            'plan_id' => $planId,
            'capability' => 'cloud_share',
            'quota' => 1024,
        ]);
        $this->assertDatabaseHas('plan_entitlements', [
            'plan_id' => $planId,
            'capability' => 'cloud_backup',
            'quota' => 1024,
        ]);
    }

    public function test_it_preserves_existing_entitlements_when_backfilled_again(): void
    {
        $planId = $this->createCloudSharePlan(1024);
        DB::table('plan_entitlements')->insert([
            'uid' => (string) Str::uuid(),
            'plan_id' => $planId,
            'capability' => 'cloud_backup',
            'quota' => 2048,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $migration = require database_path('migrations/2026_09_05_120000_add_cloud_storage_entitlements_to_existing_plans.php');
        $migration->up();
        $migration->up();

        $this->assertSame(2, DB::table('plan_entitlements')->where('plan_id', $planId)->count());
        $this->assertDatabaseHas('plan_entitlements', [
            'plan_id' => $planId,
            'capability' => 'cloud_backup',
            'quota' => 2048,
        ]);
    }

    private function createCloudSharePlan(int $quota): string
    {
        $planId = (string) Str::uuid();

        DB::table('plans')->insert([
            'uid' => $planId,
            'title' => 'Combined Cloud Plan',
            'code' => Str::upper(Str::random(8)),
            'quota' => $quota,
            'type' => 'cloud_share',
            'duration' => 3600,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $planId;
    }
}
