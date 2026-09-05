<?php

namespace Tests\End2End\Api;

use App\Enums\AccountStatus;
use App\Enums\CloudStorageKind;
use App\Models\Plan;
use App\Models\User;
use App\Models\UserCloudUsage;
use App\Models\UserSubscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserIndexApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_index_requires_authentication(): void
    {
        $response = $this->getJson('/api/user');

        $response->assertStatus(401);
    }

    public function test_authenticated_user_with_user_ability_can_fetch_profile(): void
    {
        $user = User::create([
            'uid' => (string) Str::uuid(),
            'name' => 'End2End User',
            'email' => 'e2e.user@example.com',
            'password' => bcrypt('password123'),
            'account_status' => AccountStatus::VERIFIED,
        ]);

        Sanctum::actingAs($user, ['user']);

        $response = $this->getJson('/api/user');

        $response->assertOk()->assertJsonPath('uid', $user->uid)
            ->assertJsonPath('name', $user->name)
            ->assertJsonPath('email', $user->email)
            ->assertJsonPath('accountStatus', AccountStatus::VERIFIED->value)
            ->assertJsonPath('accountStatusLabel', 'Verified')
            ->assertJsonPath('accountStatusTone', 'success')
            ->assertJsonStructure([
                'uid',
                'name',
                'email',
                'dob',
                'createdAt',
                'updatedAt',
                'accountStatus',
                'accountStatusLabel',
                'accountStatusTone',
                'subscriptions',
            ]);
    }

    public function test_authenticated_user_receives_cloud_capabilities_for_each_active_subscription(): void
    {
        $user = User::factory()->create([
            'account_status' => AccountStatus::VERIFIED,
        ]);
        UserCloudUsage::create([
            'user_id' => $user->uid,
            'share_usage' => 1000,
            'backup_usage' => 5000,
        ]);

        $sharePlan = $this->createPlanWithEntitlement(CloudStorageKind::SHARE, 100000);
        $backupPlan = $this->createPlanWithEntitlement(CloudStorageKind::BACKUP, 1000000);
        $this->createSubscription($user, $sharePlan);
        $this->createSubscription($user, $backupPlan);

        Sanctum::actingAs($user, ['user']);

        $response = $this->getJson('/api/user');

        $response->assertOk()
            ->assertJsonCount(2, 'subscriptions')
            ->assertJsonFragment([
                'type' => 'cloudShare',
                'quotaBytes' => 100000,
                'usedBytes' => 1000,
                'remainingBytes' => 99000,
            ])
            ->assertJsonFragment([
                'type' => 'cloudBackup',
                'quotaBytes' => 1000000,
                'usedBytes' => 5000,
                'remainingBytes' => 995000,
            ]);
    }

    private function createPlanWithEntitlement(CloudStorageKind $kind, int $quota): Plan
    {
        $plan = Plan::create([
            'title' => $kind === CloudStorageKind::SHARE ? 'Cloud Share' : 'Cloud Backup',
            'code' => $kind->capability(),
        ]);
        $plan->entitlements()->create([
            'capability' => $kind->capability(),
            'quota' => $quota,
        ]);

        return $plan;
    }

    private function createSubscription(User $user, Plan $plan): void
    {
        UserSubscription::create([
            'uid' => (string) Str::uuid(),
            'user_id' => $user->uid,
            'plan_id' => $plan->uid,
            'status' => 'active',
        ]);
    }
}
