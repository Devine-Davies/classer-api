<?php

namespace App\Services;

use App\Jobs\Mail\MailUserSubscriptionActivated;
use App\Logging\AppLogger;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\Plan;
use App\Models\User;
use App\Models\UserCloudUsage;
use App\Models\UserSubscription;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

class SubscriptionService
{
    /**
     * Create subscription service with logger context.
     *
     * @param  AppLogger  $logger  Application logger wrapper.
     */
    public function __construct(protected AppLogger $logger)
    {
        $this->logger->setContext('SubscriptionService');
    }

    /**
     * Create a new user subscription for the given order, plan, and user.
     *
     * @param  Order  $order  The order associated with the subscription activation.
     * @param  Plan  $plan  The plan to be activated for the user.
     * @param  User  $user  The user for whom the subscription is being created.
     * @param  int|null  $expiryDays  Optional manual expiry override in days.
     * @return UserSubscription The newly created user subscription.
     */
    public function createUserSubscription(Order $order, Plan $plan, User $user, ?int $expiryDays = null): UserSubscription
    {
        $expirationDate = $expiryDays !== null
            ? now()->addDays($expiryDays)
            : now()->addSeconds((int) $plan->duration);

        $userSubscription = DB::transaction(function () use ($order, $plan, $user, $expirationDate): UserSubscription {
            $userSubscription = UserSubscription::create([
                'uid' => (string) Str::uuid(),
                'user_id' => $user->uid,
                'plan_id' => $plan->uid,
                'order_id' => $order->uid,
                'status' => 'active',
                'expiration_date' => $expirationDate,
            ]);

            UserCloudUsage::firstOrCreate(
                ['user_id' => $user->uid],
                [
                    'uid' => (string) Str::uuid(),
                    'share_usage' => 0,
                    'backup_usage' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            return $userSubscription;
        });

        $this->logger->info('Subscription activated', [
            'email' => $user->email,
            'code' => $plan->code ?? null,
            'user_id' => $user->uid,
            'plan_id' => $plan->uid,
            'subscription_id' => $userSubscription->uid,
            'expires_at' => $expirationDate->toIso8601String(),
        ]);

        return $userSubscription;
    }

    /**
     * Activate a subscription for a user email and plan code using a manually created paid order.
     *
     * @return array{user: User, subscription: UserSubscription}
     */
    public function activateForEmailAndCode(string $email, string $code, int $expiryDays = 120): array
    {
        $normalizedEmail = strtolower(trim($email));
        $normalizedCode = strtoupper(trim($code));

        if (! filter_var($normalizedEmail, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Invalid email format: {$email}");
        }

        if ($normalizedCode === '') {
            throw new InvalidArgumentException('Subscription code is required.');
        }

        if ($expiryDays < 1) {
            throw new InvalidArgumentException('Expiry must be at least 1 day.');
        }

        $user = User::whereRaw('LOWER(email) = ?', [$normalizedEmail])->first();

        if (! $user) {
            throw new InvalidArgumentException("User with email '{$normalizedEmail}' not found.");
        }

        if ($user->activeSubscription()) {
            throw new RuntimeException("User with email '{$normalizedEmail}' already has an active subscription.");
        }

        $plan = Plan::whereRaw('UPPER(code) = ?', [$normalizedCode])->first();

        if (! $plan) {
            throw new InvalidArgumentException("Plan with code '{$normalizedCode}' not found.");
        }

        [$order, $userSubscription] = DB::transaction(function () use ($user, $plan, $expiryDays): array {
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

            OrderPayment::create([
                'order_id' => $order->uid,
                'status' => 'paid',
                'amount' => 0,
                'currency' => 'gbp',
                'paid_at' => now(),
            ]);

            return [$order, $this->createUserSubscription($order, $plan, $user, $expiryDays)];
        });

        $user = $user->fresh();
        $userSubscription = $userSubscription->fresh();

        $this->logger->info('Subscription activated manually', [
            'email' => $normalizedEmail,
            'code' => $normalizedCode,
            'user_id' => $user->uid,
            'plan_id' => $plan->uid,
            'order_id' => $order->uid,
            'subscription_id' => $userSubscription->uid,
            'expiry_days' => $expiryDays,
        ]);

        MailUserSubscriptionActivated::dispatch($user, $userSubscription);

        return [
            'user' => $user,
            'subscription' => $userSubscription,
        ];
    }

    /**
     * Attempt subscription activation by matching order item SKU values to activation codes.
     */
    public function activatePlan(Order $order, Plan $plan, User $user): void
    {
        if ($user->activeSubscription()) {
            $this->logger->info('User already has an active subscription, skipping activation', [
                'email' => $user->email,
                'user_id' => $user->uid,
            ]);

            return;
        }

        try {
            // Create the subscription for the user and plan
            $userSubscription = $this->createUserSubscription($order, $plan, $user);
            $user->refresh();

            // Dispatch a job to send the subscription activation email
            MailUserSubscriptionActivated::dispatch($user, $userSubscription);
        } catch (\Throwable $exception) {
            $this->logger->warning('Order subscription activation skipped/failed', [
                'user_email' => $user->email,
                'user_id' => $user->uid,
                'plan_id' => $plan->uid,
                'exception' => $exception,
            ]);
        }
    }

    /**
     * Deactivate the current active subscription for a user email.
     *
     * @return array{user: User, deactivated: bool, plan_id: string|null}
     */
    public function deactivateForEmail(string $email): array
    {
        $normalizedEmail = strtolower(trim($email));

        if (! filter_var($normalizedEmail, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Invalid email format: {$email}");
        }

        $user = User::whereRaw('LOWER(email) = ?', [$normalizedEmail])->first();
        if (! $user) {
            throw new InvalidArgumentException("User with email '{$normalizedEmail}' not found.");
        }

        $activeSub = UserSubscription::where('user_id', $user->uid)
            ->where('status', 'active')
            ->first();

        if (! $activeSub) {
            $this->logger->info('No active subscription found for deactivation', [
                'email' => $normalizedEmail,
                'user_id' => $user->uid,
            ]);

            return [
                'user' => $user,
                'deactivated' => false,
                'plan_id' => null,
            ];
        }

        DB::transaction(function () use ($activeSub): void {
            $activeSub->update([
                'status' => 'inactive',
                'updated_at' => now(),
            ]);
        });

        $this->logger->info('Subscription deactivated', [
            'email' => $normalizedEmail,
            'user_id' => $user->uid,
            'plan_id' => $activeSub->plan_id,
        ]);

        return [
            'user' => $user,
            'deactivated' => true,
            'plan_id' => (string) $activeSub->plan_id,
        ];
    }
}
