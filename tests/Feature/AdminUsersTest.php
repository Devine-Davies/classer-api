<?php

namespace Tests\Feature;

use App\Enums\AccountStatus;
use App\Models\Order;
use App\Models\Plan;
use App\Models\User;
use App\Models\UserSubscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminUsersTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_page_links_to_resources_granted_by_plan_entitlements(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'account_status' => AccountStatus::VERIFIED,
        ]);
        config()->set('classer.admin_email', $admin->email);

        $user = User::factory()->create([
            'email' => 'member@example.com',
            'account_status' => AccountStatus::VERIFIED,
        ]);
        $plan = Plan::create([
            'title' => 'Combined Cloud Plan',
            'code' => 'COMBINED',
            'duration' => 3600,
        ]);
        $plan->entitlements()->createMany([
            ['capability' => 'cloud_share', 'quota' => 100],
            ['capability' => 'cloud_backup', 'quota' => 200],
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

        $this->actingAs($admin)
            ->get(route('admin.users.show', $user->uid))
            ->assertOk()
            ->assertSee('View Cloud Shares')
            ->assertSee('View Cloud Backups')
            ->assertSee(route('admin.cloud-shares', [
                'q' => $user->email,
                'state' => 'all',
            ]))
            ->assertSee(route('admin.cloud-backups', [
                'q' => $user->email,
                'state' => 'all',
            ]));
    }
}
