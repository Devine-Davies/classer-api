<?php

namespace App\Listeners;

use App\Events\OrderPaid;
use App\Jobs\MailOrderPaymentConfirmed;
use App\Jobs\MailPromotionalRedeemEmail;
use App\Jobs\MailUserAccountVerify;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\User;
use App\Services\PromotionRedemptionService;
use App\Services\SubscriptionService;
use App\Utils\EmailToken;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class HandleOrderPaid implements ShouldQueue
{
    /**
     * Handle the event when an order is marked as paid.
     */
    public function handle(OrderPaid $event): void
    {
        $order = $event->order->fresh();
        $payment = $event->payment->fresh();

        if (! $order instanceof Order || ! $payment instanceof OrderPayment) {
            return;
        }

        // Send payment confirmation email to the customer
        $this->sendPaymentConfirmation($order, $payment);

        // Create a customer account if one does not already exist
        $this->createCustomerAccountIfNeeded($order);

        // Activate matching subscription codes from purchased SKU items
        $this->processSubscription($order);

        // Send promotional email if the order is eligible for any promotions
        $this->sendPromotionalEmailIfEligible($order);
    }

    /**
     * Send order payment confirmation email to the customer.
     * Description: An order with customer email "customer@example.com" would trigger a payment confirmation email to that address.
     *
     * @param  Order  $order  The order that has been paid for.
     * @param  OrderPayment  $payment  The payment that has been confirmed.
     */
    protected function sendPaymentConfirmation(Order $order, OrderPayment $payment): void
    {
        MailOrderPaymentConfirmed::dispatch($order, $payment);
    }

    /**
     * Create a customer account if one does not already exist.
     * Description: An order with customer email "customer@example.com" would create a new user account with that email.
     *
     * @param  Order  $order  The order associated with the customer.
     */
    protected function createCustomerAccountIfNeeded(Order $order): void
    {
        $email = $this->normalizeEmail($order->customer_email);

        if (! $email) {
            return;
        }

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'uid' => (string) Str::uuid(),
                'name' => $order->customer_name ?: 'Customer',
                'password' => Hash::make(Str::random(32)),
                'email_verification_token' => EmailToken::generateToken(),
            ]
        );

        if ($user->wasRecentlyCreated) {
            MailUserAccountVerify::dispatch($user);
        }
    }

    /**
     * Send promotional email if the order is eligible for any promotions.
     * Description: An order containing an item with SKU "CLS-CS-6M-001" would trigger a promotional email for a 6 month Classer subscription offer.
     */
    protected function sendPromotionalEmailIfEligible(Order $order): void
    {
        /** @var PromotionRedemptionService $promotionRedemptionService */
        $promotionRedemptionService = app(PromotionRedemptionService::class);
        $issued = $promotionRedemptionService->issueForOrder($order);

        if ($issued) {
            MailPromotionalRedeemEmail::dispatch($issued['redemption'], $issued['token']);
        }
    }

    /**
     * Activate subscription(s) for a paid order by mapping purchased SKU values to activation codes.
     */
    protected function processSubscription(Order $order): void
    {
        /** @var SubscriptionService $subscriptionService */
        $subscriptionService = app(SubscriptionService::class);
        $subscriptionService->activateFromOrderSkus($order);
    }

    /**
     * Normalize an email address by trimming whitespace and converting to lowercase.
     *
     * @param  string|null  $email  The email address to normalize.
     * @return string|null The normalized email address, or null if the input is empty.
     */
    protected function normalizeEmail(?string $email): ?string
    {
        $email = strtolower(trim((string) $email));

        return $email !== '' ? $email : null;
    }
}
