<?php

namespace Tests\Unit\Services;

use App\Jobs\Mail\MailUserSubscriptionActivated;
use App\Logging\AppLogger;
use App\Models\Order;
use App\Models\Plan;
use App\Models\User;
use App\Services\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class SubscriptionServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_plan_duration_is_applied_in_seconds_without_an_override(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-26 10:15:00'));

        $user = User::factory()->create();
        $plan = Plan::create([
            'title' => 'One Hour Plan',
            'code' => 'ONEHOUR',
            'duration' => 3600,
        ]);
        $order = Order::create([
            'quantity' => 1,
            'amount' => 0,
            'subtotal_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => 0,
            'currency' => 'gbp',
            'status' => 'paid',
            'customer_email' => $user->email,
            'paid_at' => now(),
        ]);

        $subscription = (new SubscriptionService(new AppLogger))
            ->createUserSubscription($order, $plan, $user);

        $this->assertSame('2026-08-26 11:15:00', $subscription->expiration_date->toDateTimeString());
        $this->assertDatabaseHas('user_cloud_usages', [
            'user_id' => $user->uid,
            'share_usage' => 0,
            'backup_usage' => 0,
        ]);

        Carbon::setTestNow();
    }

    public function test_it_activates_a_subscription_for_an_email_and_code(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-26 10:15:00'));
        Queue::fake();

        $user = User::factory()->create([
            'email' => 'rhys@classermedia.com',
        ]);

        $plan = Plan::create([
            'title' => 'Manual Activation Plan',
            'code' => 'T017A42C',
            'duration' => 30,
        ]);
        $plan->entitlements()->createMany([
            ['capability' => 'cloud_share', 'quota' => 100],
            ['capability' => 'cloud_backup', 'quota' => 200],
        ]);

        $service = new SubscriptionService(new AppLogger);

        $result = $service->activateForEmailAndCode($user->email, $plan->code, 45);

        $this->assertSame($user->uid, $result['user']->uid);
        $this->assertSame($user->uid, $result['subscription']->user_id);
        $this->assertSame($plan->uid, $result['subscription']->plan_id);
        $this->assertSame('2026-10-10 10:15:00', $result['subscription']->expiration_date->toDateTimeString());
        $this->assertEqualsCanonicalizing(
            ['cloud_share', 'cloud_backup'],
            $result['subscription']->plan->entitlements->pluck('capability')->all(),
        );

        $this->assertDatabaseHas('orders', [
            'uid' => $result['subscription']->order_id,
            'customer_email' => $user->email,
            'status' => 'paid',
            'total_amount' => 0,
        ]);

        $this->assertDatabaseHas('order_payments', [
            'order_id' => $result['subscription']->order_id,
            'status' => 'paid',
            'amount' => 0,
        ]);

        Queue::assertPushed(MailUserSubscriptionActivated::class);

        Carbon::setTestNow();
    }

    public function test_it_replaces_an_active_subscription_with_the_requested_plan(): void
    {
        Queue::fake();

        $user = User::factory()->create([
            'email' => 'skywalker@classermedia.com',
        ]);

        $oldPlan = Plan::create([
            'title' => 'Old Plan',
            'code' => 'OLDPLAN1',
            'duration' => 30,
        ]);
        $newPlan = Plan::create([
            'title' => 'New Plan',
            'code' => 'NEWPLAN1',
            'duration' => 30,
        ]);

        $service = new SubscriptionService(new AppLogger);
        $oldSubscription = $service->activateForEmailAndCode($user->email, $oldPlan->code, 30)['subscription'];

        $result = $service->activateForEmailAndCode($user->email, $newPlan->code, 30);

        $this->assertSame($oldPlan->uid, $result['replaced_plan_id']);
        $this->assertSame($newPlan->uid, $result['subscription']->plan_id);
        $this->assertDatabaseHas('user_subscriptions', [
            'uid' => $oldSubscription->uid,
            'status' => 'inactive',
            'cancellation_reason' => 'Replaced by manual plan activation',
        ]);
        $this->assertSame(1, $user->subscriptions()->active()->count());
    }

    public function test_an_unknown_plan_code_does_not_deactivate_the_current_subscription(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $plan = Plan::create([
            'title' => 'Current Plan',
            'code' => 'CURRENT1',
            'duration' => 30,
        ]);

        $service = new SubscriptionService(new AppLogger);
        $subscription = $service->activateForEmailAndCode($user->email, $plan->code, 30)['subscription'];

        try {
            $service->activateForEmailAndCode($user->email, 'MISSING1', 30);
            $this->fail('Expected an invalid plan code to fail activation.');
        } catch (\InvalidArgumentException $exception) {
            $this->assertStringContainsString("Plan with code 'MISSING1' not found", $exception->getMessage());
        }

        $this->assertDatabaseHas('user_subscriptions', [
            'uid' => $subscription->uid,
            'status' => 'active',
        ]);
    }
}
