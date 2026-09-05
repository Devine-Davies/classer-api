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
use RuntimeException;
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

        $service = new SubscriptionService(new AppLogger);

        $result = $service->activateForEmailAndCode($user->email, $plan->code, 45);

        $this->assertSame($user->uid, $result['user']->uid);
        $this->assertSame($user->uid, $result['subscription']->user_id);
        $this->assertSame($plan->uid, $result['subscription']->plan_id);
        $this->assertSame('2026-10-10 10:15:00', $result['subscription']->expiration_date->toDateTimeString());

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

    public function test_it_rejects_a_second_activation_when_the_user_already_has_one(): void
    {
        Queue::fake();

        $user = User::factory()->create([
            'email' => 'skywalker@classermedia.com',
        ]);

        $plan = Plan::create([
            'title' => 'Duplicate Guard Plan',
            'code' => 'DUPLICATE1',
            'duration' => 30,
        ]);

        $service = new SubscriptionService(new AppLogger);

        $service->activateForEmailAndCode($user->email, $plan->code, 30);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('already has an active subscription');

        $service->activateForEmailAndCode($user->email, $plan->code, 30);
    }
}
