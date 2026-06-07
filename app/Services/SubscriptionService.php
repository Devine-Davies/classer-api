<?php

namespace App\Services;

use App\Http\Controllers\SystemController;
use App\Jobs\MailUserSubscriptionActivated;
use App\Logging\AppLogger;
use App\Models\Order;
use App\Models\Subscription;
use App\Models\User;
use App\Models\UserCloudUsage;
use App\Models\UserSubscription;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SubscriptionService
{
    /**
     * Map product SKUs to subscription activation codes.
     *
     * @var array<string, string>
     */
    protected const SUBSCRIPTION_CODES_BY_SKU = [
        'CLS-CS-6M-001' => 'T017A42C',
    ];

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
     * Load subscription dataset and merge with DB records.
     */
    public function loadMergedSubscriptions(): Collection
    {
        $systemController = new SystemController;

        $resourceSubscriptions = collect(
            $systemController->loadFromResource('subscriptions.dataset.json')
        );

        $dbSubscriptions = Subscription::all()->keyBy('code');

        $this->logger->info('Merging subscription datasets', [
            'resource_count' => $resourceSubscriptions->count(),
            'db_count' => $dbSubscriptions->count(),
        ]);

        $merged = $resourceSubscriptions->map(function ($item) use ($dbSubscriptions) {
            $match = $dbSubscriptions->get($item['code']);
            $item['subscription_id'] = $match?->uid;

            return $item;
        });

        $this->logger->info('Merged subscription dataset complete', [
            'merged_count' => $merged->count(),
            'matched_count' => $merged->filter(fn ($item) => ! empty($item['subscription_id']))->count(),
        ]);

        return $merged;
    }

    /**
     * Activate a subscription for an existing user using a subscription code.
     *
     * @return array{user: User, subscription: Subscription}
     */
    public function activateForEmailAndCode(string $email, string $code, int $expiryDays = 120): array
    {
        $normalizedEmail = strtolower(trim($email));
        $normalizedCode = trim($code);

        if (! filter_var($normalizedEmail, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("Invalid email format: {$email}");
        }

        if ($normalizedCode === '') {
            throw new \InvalidArgumentException('Subscription code cannot be empty.');
        }

        $user = User::whereRaw('LOWER(email) = ?', [$normalizedEmail])->first();
        if (! $user) {
            throw new \InvalidArgumentException("User with email '{$normalizedEmail}' not found.");
        }

        if ($user->activeSubscription()) {
            throw new \RuntimeException("User with email '{$normalizedEmail}' already has an active subscription.");
        }

        $subscription = Subscription::where('code', $normalizedCode)->first();
        if (! $subscription) {
            throw new \InvalidArgumentException("Subscription with code '{$normalizedCode}' not found.");
        }

        DB::transaction(function () use ($user, $subscription, $expiryDays): void {
            UserSubscription::create([
                'uid' => Str::uuid(),
                'user_id' => $user->uid,
                'subscription_id' => $subscription->uid,
                'status' => 'active',
                'expiration_date' => now()->addDays($expiryDays),
                'auto_renew_date' => now()->addMonths(6),
                'auto_renew' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if (! UserCloudUsage::where('user_id', $user->uid)->exists()) {
                UserCloudUsage::create([
                    'uid' => Str::uuid(),
                    'user_id' => $user->uid,
                    'total_usage' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        MailUserSubscriptionActivated::dispatch($user, $subscription);

        $this->logger->info('Subscription activated', [
            'email' => $normalizedEmail,
            'code' => $normalizedCode,
            'user_id' => $user->uid,
            'subscription_id' => $subscription->uid,
            'expiry_days' => $expiryDays,
        ]);

        return [
            'user' => $user,
            'subscription' => $subscription,
        ];
    }

    /**
     * Attempt subscription activation by matching order item SKU values to activation codes.
     */
    public function activateFromOrderSkus(Order $order): void
    {
        $email = strtolower(trim((string) $order->customer_email));
        if ($email === '') {
            return;
        }

        $user = User::whereRaw('LOWER(email) = ?', [$email])->first();
        if (! $user || $user->activeSubscription()) {
            return;
        }

        foreach ($this->resolveSubscriptionCodesForOrder($order) as $code) {
            try {
                if ($user->activeSubscription()) {
                    return;
                }

                $this->activateForEmailAndCode($user->email, $code);
                $user->refresh();
            } catch (\Throwable $exception) {
                $this->logger->warning('Order subscription activation skipped/failed', [
                    'order_uid' => $order->uid,
                    'email' => $email,
                    'code' => $code,
                    'error' => $exception->getMessage(),
                ]);
            }
        }
    }

    /**
     * Deactivate the current active subscription for a user email.
     *
     * @return array{user: User, deactivated: bool, subscription_id: string|null}
     */
    public function deactivateForEmail(string $email): array
    {
        $normalizedEmail = strtolower(trim($email));

        if (! filter_var($normalizedEmail, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("Invalid email format: {$email}");
        }

        $user = User::whereRaw('LOWER(email) = ?', [$normalizedEmail])->first();
        if (! $user) {
            throw new \InvalidArgumentException("User with email '{$normalizedEmail}' not found.");
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
                'subscription_id' => null,
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
            'subscription_id' => $activeSub->subscription_id,
        ]);

        return [
            'user' => $user,
            'deactivated' => true,
            'subscription_id' => (string) $activeSub->subscription_id,
        ];
    }

    /**
     * @return array<int, string>
     */
    protected function resolveSubscriptionCodesForOrder(Order $order): array
    {
        $order->loadMissing('items.product');

        $codes = [];
        foreach ($order->items as $item) {
            $sku = (string) ($item->product?->sku ?? '');
            if ($sku === '' || ! isset(self::SUBSCRIPTION_CODES_BY_SKU[$sku])) {
                continue;
            }

            $codes[] = self::SUBSCRIPTION_CODES_BY_SKU[$sku];
        }

        return array_values(array_unique($codes));
    }
}
