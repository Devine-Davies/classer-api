<?php

namespace Tests\Feature;

use App\Enums\AccountStatus;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminPlansTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_and_update_capability_entitlements(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'account_status' => AccountStatus::VERIFIED,
        ]);
        config()->set('classer.admin_email', $admin->email);

        $this->actingAs($admin)
            ->post(route('admin.plans.create'), [
                'title' => 'Combined Cloud Plan',
                'duration' => '15552000',
                'entitlements' => [
                    'cloud_share' => ['enabled' => '1', 'quota' => 1_073_741_824],
                    'cloud_backup' => ['enabled' => '1', 'quota' => 2_147_483_648],
                ],
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $plan = Plan::query()->where('title', 'Combined Cloud Plan')->firstOrFail();

        $this->assertFalse(Schema::hasColumn('plans', 'quota'));
        $this->assertDatabaseHas('plan_entitlements', [
            'plan_id' => $plan->uid,
            'capability' => 'cloud_share',
            'quota' => 1_073_741_824,
        ]);
        $this->assertDatabaseHas('plan_entitlements', [
            'plan_id' => $plan->uid,
            'capability' => 'cloud_backup',
            'quota' => 2_147_483_648,
        ]);

        $catalogItem = $plan->catalogItem;

        $this->actingAs($admin)
            ->put(route('admin.plans.update', $plan->uid), [
                'title' => $plan->title,
                'duration' => (string) $plan->duration,
                'entitlements' => [
                    'cloud_share' => ['enabled' => '0', 'quota' => 1_073_741_824],
                    'cloud_backup' => ['enabled' => '1', 'quota' => 3_221_225_472],
                ],
                'catalogItem' => [
                    'title' => $catalogItem->title,
                    'priceAmount' => $catalogItem->price_amount,
                    'currency' => $catalogItem->currency,
                    'slug' => $catalogItem->slug,
                    'promotionPercentage' => $catalogItem->promotion_percentage,
                ],
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.plans.edit', $plan->uid));

        $this->assertDatabaseMissing('plan_entitlements', [
            'plan_id' => $plan->uid,
            'capability' => 'cloud_share',
        ]);
        $this->assertDatabaseHas('plan_entitlements', [
            'plan_id' => $plan->uid,
            'capability' => 'cloud_backup',
            'quota' => 3_221_225_472,
        ]);
    }
}
