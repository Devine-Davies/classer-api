<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PromotionRedemption;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PromotionRedemptionService
{
    /**
     * @var array<string, array{promotion_code:string, activation_code:string, subscription_days:int, redeem_ttl_days:int}>
     */
    protected const PROMOTIONS_BY_SKU = [
        'CLS-CS-6M-001' => [
            'promotion_code' => 'CLASSER_SHARE_6_MONTHS',
            'activation_code' => 'T017A42C',
            'subscription_days' => 180,
            'redeem_ttl_days' => 30,
        ],
    ];

    /**
     * Issue or refresh a redemption for an eligible paid order.
     *
     * @param  Order  $order  Paid order candidate.
     * @return array{redemption: PromotionRedemption, token: string}|null
     */
    public function issueForOrder(Order $order): ?array
    {
        $order->loadMissing('items.product');

        $eligible = $this->resolveEligiblePromotion($order);
        if (! $eligible) {
            return null;
        }

        $email = $this->normalizeEmail($order->customer_email);
        if (! $email) {
            return null;
        }

        $customerUid = User::whereRaw('LOWER(email) = ?', [$email])->value('uid');
        $token = Str::random(64);
        $tokenHash = hash('sha256', $token);

        $redemption = DB::transaction(function () use ($eligible, $order, $email, $customerUid, $tokenHash): ?PromotionRedemption {
            $redemption = PromotionRedemption::where('order_id', $order->uid)
                ->where('promotion_code', $eligible['promotion_code'])
                ->lockForUpdate()
                ->first();

            if ($redemption && $redemption->status === 'redeemed') {
                return null;
            }

            $attributes = [
                'source_type' => 'order',
                'source_uid' => $order->uid,
                'order_id' => $order->uid,
                'order_item_id' => $eligible['order_item_uid'],
                'user_id' => $customerUid,
                'customer_email' => $email,
                'status' => 'pending',
                'redeem_token_hash' => $tokenHash,
                'sent_at' => null,
                'redeemed_at' => null,
                'expires_at' => now()->addDays($eligible['redeem_ttl_days']),
                'metadata' => [
                    'sku' => $eligible['sku'],
                    'activation_code' => $eligible['activation_code'],
                    'subscription_days' => $eligible['subscription_days'],
                ],
            ];

            if ($redemption) {
                $redemption->fill($attributes);
                $redemption->save();

                return $redemption;
            }

            return PromotionRedemption::create(array_merge($attributes, [
                'promotion_code' => $eligible['promotion_code'],
            ]));
        });

        if (! $redemption) {
            return null;
        }

        return [
            'redemption' => $redemption,
            'token' => $token,
        ];
    }

    public function markEmailed(PromotionRedemption $redemption): void
    {
        if ($redemption->status === 'redeemed') {
            return;
        }

        $redemption->status = 'emailed';
        $redemption->sent_at = $redemption->sent_at ?? now();
        $redemption->save();
    }

    /**
     * Redeem a promotion token and activate the target subscription benefit.
     *
     * @param  string  $token  Raw token from the redeem URL.
     * @param  string|null  $providedEmail  Email entered by the user.
     * @return array{status:string, message:string}
     */
    public function redeemFromToken(string $token, ?string $providedEmail = null): array
    {
        $tokenHash = hash('sha256', $token);
        $providedEmail = $this->normalizeEmail($providedEmail);

        if ($providedEmail === null) {
            return [
                'status' => 'missing_email',
                'message' => 'Please enter the email used at checkout.',
            ];
        }

        return DB::transaction(function () use ($tokenHash, $providedEmail): array {
            $redemption = PromotionRedemption::where('redeem_token_hash', $tokenHash)
                ->lockForUpdate()
                ->first();

            if (! $redemption) {
                return [
                    'status' => 'invalid',
                    'message' => 'This promotion link is invalid.',
                ];
            }

            if ($redemption->status === 'redeemed') {
                return [
                    'status' => 'already_redeemed',
                    'message' => 'This promotion has already been redeemed.',
                ];
            }

            if ($redemption->status === 'cancelled') {
                return [
                    'status' => 'cancelled',
                    'message' => 'This promotion is no longer available.',
                ];
            }

            if ($redemption->expires_at && $redemption->expires_at->isPast()) {
                $redemption->status = 'expired';
                $redemption->save();

                return [
                    'status' => 'expired',
                    'message' => 'This promotion has expired.',
                ];
            }

            if (! in_array($redemption->status, ['pending', 'emailed'], true)) {
                return [
                    'status' => 'invalid_state',
                    'message' => 'This promotion link is invalid.',
                ];
            }

            $order = $redemption->order;
            if (! $order || $order->status !== 'paid') {
                return [
                    'status' => 'invalid_order',
                    'message' => 'This promotion could not be validated against a paid order.',
                ];
            }

            $email = $this->normalizeEmail($redemption->customer_email);
            if (! $email) {
                return [
                    'status' => 'missing_email',
                    'message' => 'This promotion is missing a customer email. Please contact support.',
                ];
            }

            if ($providedEmail !== $email) {
                return [
                    'status' => 'email_mismatch',
                    'message' => 'This promotion was issued to a different email address.',
                ];
            }

            $user = User::whereRaw('LOWER(email) = ?', [$email])->first();
            if (! $user) {
                return [
                    'status' => 'account_required',
                    'message' => 'Please create an account with your purchase email before redeeming this promotion.',
                ];
            }

            $activationCode = (string) ($redemption->metadata['activation_code'] ?? '');
            $subscriptionDays = (int) ($redemption->metadata['subscription_days'] ?? 0);

            if (! $activationCode || $subscriptionDays <= 0) {
                return [
                    'status' => 'invalid_config',
                    'message' => 'This promotion is not configured correctly. Please contact support.',
                ];
            }

            if ($user->activeSubscription()) {
                return [
                    'status' => 'already_active',
                    'message' => 'You already have an active subscription on this account.',
                ];
            }

            $exitCode = Artisan::call('subscription:activate', [
                'email' => $user->email,
                'code' => $activationCode,
                'expiry' => $subscriptionDays,
            ]);

            if ($exitCode !== 0) {
                return [
                    'status' => 'activation_failed',
                    'message' => 'We could not activate this promotion right now. Please try again shortly.',
                ];
            }

            $redemption->status = 'redeemed';
            $redemption->redeemed_at = now();
            $redemption->user_id = $user->uid;
            $redemption->save();

            return [
                'status' => 'redeemed',
                'message' => 'Your promotion has been redeemed successfully.',
            ];
        });
    }

    /**
     * @return array{promotion_code:string, activation_code:string, subscription_days:int, redeem_ttl_days:int, sku:string, order_item_uid:string}|null
     */
    protected function resolveEligiblePromotion(Order $order): ?array
    {
        foreach ($order->items as $item) {
            $sku = (string) ($item->product?->sku ?? '');
            if (! isset(self::PROMOTIONS_BY_SKU[$sku])) {
                continue;
            }

            return array_merge(self::PROMOTIONS_BY_SKU[$sku], [
                'sku' => $sku,
                'order_item_uid' => (string) $item->uid,
            ]);
        }

        return null;
    }

    protected function normalizeEmail(?string $email): ?string
    {
        $email = strtolower(trim((string) $email));

        return $email !== '' ? $email : null;
    }
}
