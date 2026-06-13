<?php

namespace App\Services;

use App\Events\OrderPaid;
use App\Jobs\MailAdminErrorAlert;
use App\Logging\AppLogger;
use App\Models\DiscountCode;
use App\Models\DiscountCodeRedemption;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\StripeEvent;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class StripePaymentService
{
    /**
     * Create the Stripe payment service and set logger context.
     *
     * @param  AppLogger  $logger  Application logger wrapper.
     */
    public function __construct(protected AppLogger $logger)
    {
        $this->logger->setContext('StripePaymentService');
    }

    /**
     * Create or reuse an active Payment Intent for an order.
     *
     * @param  Order  $order  Order requiring payment intent orchestration.
     * @return array{client_secret:string, payment:OrderPayment} Payment payload for checkout.
     *
     * @throws \Throwable
     */
    public function createOrGetPaymentIntent(Order $order): array
    {
        $order->loadMissing('discountCode');
        $payment = $order->activePayment();

        if (! $payment) {
            $payment = OrderPayment::create([
                'order_id' => $order->uid,
                'amount' => $order->amount,
                'currency' => strtolower($order->currency),
                'status' => 'pending',
            ]);
        }

        $intent = null;
        $metadata = $this->buildPaymentMetadata($order, $payment);

        if ($payment->stripe_payment_intent_id) {
            $intent = $this->client()->paymentIntents->retrieve($payment->stripe_payment_intent_id);

            // If this intent was created with automatic_payment_methods (which enables Link),
            // update it to card-only so the Payment Element no longer shows Link.
            if ($intent) {
                $needsAmountUpdate = (int) ($intent->amount ?? 0) !== (int) $order->amount;
                $intentMetadata = $intent->metadata ? (array) $intent->metadata->toArray() : [];
                $needsMetadataUpdate = $this->metadataNeedsUpdate($intentMetadata, $metadata);

                if ($needsAmountUpdate || $needsMetadataUpdate || ! empty($intent->automatic_payment_methods)) {
                    $intent = $this->client()->paymentIntents->update($intent->id, [
                        'amount' => $order->amount,
                        'receipt_email' => $order->customer_email,
                        'metadata' => $metadata,
                        'payment_method_types' => ['card'],
                    ]);
                }
            }
        }

        if (! $intent) {
            $intent = $this->client()->paymentIntents->create([
                'amount' => $order->amount,
                'currency' => strtolower($order->currency),
                'receipt_email' => $order->customer_email,
                'metadata' => $metadata,
                'payment_method_types' => ['card'],
            ]);

            $payment->stripe_payment_intent_id = $intent->id;
            $payment->status = $this->mapIntentStatus($intent->status);
            $payment->save();
        }

        return [
            'client_secret' => $intent->client_secret,
            'payment' => $payment->fresh(),
        ];
    }

    /**
     * Verify and process a Stripe webhook payload.
     *
     * @param  string  $payload  Raw webhook payload body.
     * @param  string  $signature  Stripe signature header value.
     * @return object Stripe event object from SDK.
     *
     * @throws RuntimeException
     * @throws \Throwable
     */
    public function handleWebhook(string $payload, string $signature)
    {
        $webhookClass = '\\Stripe\\Webhook';

        if (! class_exists($webhookClass)) {
            throw new RuntimeException('Stripe SDK is not installed. Run composer update stripe/stripe-php.');
        }

        try {
            $event = $webhookClass::constructEvent(
                $payload,
                $signature,
                (string) config('services.stripe.webhook_secret')
            );
        } catch (\Throwable $exception) {
            $this->logger->warning('Invalid Stripe webhook payload', [
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }

        $this->processEvent($event);

        return $event;
    }

    /**
     * Process a Stripe event and synchronize payment/order state.
     *
     * @param  object  $event  Stripe event payload object.
     */
    protected function processEvent(object $event): void
    {
        if ($this->hasAlreadyProcessed($event)) {
            return;
        }

        DB::transaction(function () use ($event): void {
            $this->recordStripeEvent($event);

            $intentId = $this->resolvePaymentIntentId($event);

            if (! $intentId) {
                return;
            }

            $payment = $this->findPaymentForUpdate($intentId);

            if (! $payment) {
                $this->logger->warning('Webhook received for unknown payment intent', [
                    'event_id' => $event->id,
                    'intent_id' => $intentId,
                ]);

                return;
            }

            $order = $this->findOrderForUpdate($payment);

            if (! $order) {
                return;
            }

            $wasPaid = $payment->status === 'paid';

            $handled = $this->applyStripeEventToPaymentAndOrder($event, $payment, $order);

            if (! $handled) {
                return;
            }

            $payment->save();
            $order->save();

            if ($event->type === 'payment_intent.succeeded' && ! $wasPaid) {
                $this->redeemOrderDiscount($order, $payment);
                OrderPaid::dispatch($order, $payment);
            }
        });
    }

    /**
     * Determine whether a Stripe event has already been recorded.
     *
     * @param  object  $event  Stripe event payload object.
     * @return bool True when the event has already been processed.
     */
    protected function hasAlreadyProcessed(object $event): bool
    {
        return StripeEvent::where('stripe_event_id', $event->id)->exists();
    }

    /**
     * Record a Stripe event for webhook idempotency.
     *
     * @param  object  $event  Stripe event payload object.
     */
    protected function recordStripeEvent(object $event): void
    {
        StripeEvent::create([
            'stripe_event_id' => $event->id,
            'event_type' => $event->type,
            'status' => 'processed',
            'processed_at' => now(),
            'payload' => (array) $event->toArray(),
        ]);
    }

    /**
     * Find and lock the local payment for a Stripe PaymentIntent.
     *
     * @param  string  $intentId  Stripe PaymentIntent ID.
     * @return OrderPayment|null Locked payment record, or null when no payment matches.
     */
    protected function findPaymentForUpdate(string $intentId): ?OrderPayment
    {
        return OrderPayment::where('stripe_payment_intent_id', $intentId)
            ->lockForUpdate()
            ->first();
    }

    /**
     * Find and lock the order associated with a payment.
     *
     * @param  OrderPayment  $payment  Local payment record to update.
     * @return Order|null Locked order record, or null when no order matches.
     */
    protected function findOrderForUpdate(OrderPayment $payment): ?Order
    {
        return Order::where('uid', $payment->order_id)
            ->lockForUpdate()
            ->first();
    }

    /**
     * Resolve the relevant Payment Intent ID from a Stripe event payload, accounting for different event structures.
     *
     * @param  object  $event  Stripe event payload object.
     * @return string|null Stripe PaymentIntent ID, or null when the event has no related intent.
     */
    protected function resolvePaymentIntentId(object $event): ?string
    {
        return match ($event->type) {
            'payment_intent.processing',
            'payment_intent.payment_failed',
            'payment_intent.succeeded' => $event->data->object->id ?? null,
            'charge.refunded' => $event->data->object->payment_intent ?? null,
            default => null,
        };
    }

    /**
     * Apply relevant data from a Stripe event to the local payment and order records.
     *
     * @param  object  $event  Stripe event payload object.
     * @param  OrderPayment  $payment  Local payment record to update.
     * @param  Order  $order  Local order record to update.
     * @return bool True if the event was recognized and applied, false if the event type is unhandled.
     */
    protected function applyStripeEventToPaymentAndOrder(
        object $event,
        OrderPayment $payment,
        Order $order
    ): bool {
        $stripeObject = $event->data->object;

        return match ($event->type) {
            'payment_intent.processing' => $this->markPaymentProcessing($payment),

            'payment_intent.payment_failed' => $this->markPaymentFailed(
                $payment,
                $order,
                $stripeObject
            ),

            'payment_intent.succeeded' => $this->markPaymentPaid(
                $payment,
                $order,
                $stripeObject
            ),

            'charge.refunded' => $this->markPaymentRefunded($payment, $order),

            default => false,
        };
    }

    protected function markPaymentProcessing(OrderPayment $payment): bool
    {
        $payment->status = 'processing';

        return true;
    }

    protected function markPaymentFailed(OrderPayment $payment, Order $order, object $stripeObject): bool
    {
        $payment->status = 'failed';
        $payment->failure_code = $stripeObject->last_payment_error->code ?? null;
        $payment->failure_message = $stripeObject->last_payment_error->message ?? null;

        $order->status = 'pending';

        return true;
    }

    protected function markPaymentPaid(OrderPayment $payment, Order $order, object $stripeObject): bool
    {
        $payment->status = 'paid';
        $payment->paid_at = $payment->paid_at ?? now();
        $payment->stripe_customer_id = $stripeObject->customer ?? null;
        $payment->stripe_payment_method_id = $stripeObject->payment_method ?? null;

        $order->status = 'paid';
        $order->paid_at = $order->paid_at ?? now();

        return true;
    }

    protected function markPaymentRefunded(OrderPayment $payment, Order $order): bool
    {
        $payment->status = 'refunded';
        $payment->refunded_at = $payment->refunded_at ?? now();

        $order->status = 'refunded';

        return true;
    }

    /**
     * Map Stripe PaymentIntent status values to local payment statuses.
     *
     * @param  string  $stripeStatus  Stripe PaymentIntent status.
     * @return string Local payment status.
     */
    protected function mapIntentStatus(string $stripeStatus): string
    {
        return match ($stripeStatus) {
            'succeeded' => 'paid',
            'processing' => 'processing',
            'requires_action' => 'requires_action',
            'requires_payment_method', 'canceled' => 'failed',
            default => 'pending',
        };
    }

    /**
     * Build metadata persisted onto Stripe PaymentIntents.
     *
     * @param  Order  $order  Source order.
     * @param  OrderPayment  $payment  Source payment record.
     * @return array<string, string> Metadata payload without empty values.
     */
    protected function buildPaymentMetadata(Order $order, OrderPayment $payment): array
    {
        $metadata = [
            'order_uid' => $order->uid,
            'order_payment_uid' => $payment->uid,
            'discount_code_uid' => $order->discountCode?->uid,
            'discount_code' => $order->discountCode?->code,
        ];

        return array_filter($metadata, static fn ($value) => $value !== null && $value !== '');
    }

    /**
     * Determine whether current intent metadata should be updated.
     *
     * @param  array<string, mixed>  $existing  Existing metadata from Stripe.
     * @param  array<string, mixed>  $target  Desired metadata values.
     * @return bool True when metadata diverges.
     */
    protected function metadataNeedsUpdate(array $existing, array $target): bool
    {
        foreach ($target as $key => $value) {
            if (! array_key_exists($key, $existing) || (string) $existing[$key] !== (string) $value) {
                return true;
            }
        }

        return false;
    }

    /**
     * Redeem and record discount usage for a successfully paid order.
     *
     * @param  Order  $order  Paid order.
     * @param  OrderPayment  $payment  Payment record associated with the order.
     */
    protected function redeemOrderDiscount(Order $order, OrderPayment $payment): void
    {
        if (! $order->discount_code_id) {
            return;
        }

        $discountCode = DiscountCode::where('uid', $order->discount_code_id)->lockForUpdate()->first();

        if (! $discountCode) {
            return;
        }

        $existingRedemption = DiscountCodeRedemption::where('order_id', $order->uid)->first();

        if ($existingRedemption) {
            return;
        }

        $normalizedEmail = $order->customer_email ? strtolower((string) $order->customer_email) : null;
        $matchedUserUid = $normalizedEmail
            ? User::whereRaw('LOWER(email) = ?', [$normalizedEmail])->value('uid')
            : null;

        if ($discountCode->one_use_per_customer) {
            $alreadyUsedByCustomer = DiscountCodeRedemption::where('discount_code_id', $discountCode->uid)
                ->where(function ($query) use ($matchedUserUid, $normalizedEmail) {
                    if ($matchedUserUid) {
                        $query->orWhere('user_id', $matchedUserUid);
                    }

                    if ($normalizedEmail) {
                        $query->orWhereRaw('LOWER(customer_email) = ?', [$normalizedEmail]);
                    }
                })
                ->lockForUpdate()
                ->exists();

            if ($alreadyUsedByCustomer) {
                $this->logger->info('Skipped discount redemption due to one-use-per-customer enforcement', [
                    'order_uid' => $order->uid,
                    'discount_code_uid' => $discountCode->uid,
                ]);

                return;
            }
        }

        DiscountCodeRedemption::create([
            'discount_code_id' => $discountCode->uid,
            'order_id' => $order->uid,
            'order_payment_id' => $payment->uid,
            'user_id' => $matchedUserUid,
            'customer_email' => $normalizedEmail,
            'redeemed_at' => now(),
        ]);

        $discountCode->increment('usage_count');
    }

    /**
     * Build the Stripe client instance.
     *
     * @return object StripeClient instance.
     *
     * @throws RuntimeException
     */
    protected function client()
    {
        $secret = (string) config('services.stripe.secret');
        $stripeClientClass = '\\Stripe\\StripeClient';

        if (! $secret) {
            $this->logger->error('Stripe secret key is not configured');
            MailAdminErrorAlert::dispatch('Stripe secret key is not configured');
        }

        if (! class_exists($stripeClientClass)) {
            throw new RuntimeException('Stripe SDK is not installed. Run composer update stripe/stripe-php.');
        }

        return new $stripeClientClass($secret);
    }
}
