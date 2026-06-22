<?php

namespace App\Listeners;

use App\Events\OrderPaid;
use App\Jobs\MailOrderPaymentConfirmed;
use App\Jobs\MailPromotionalRedeemEmail;
use App\Jobs\MailUserAccountVerify;
use App\Logging\AppLogger;
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
     * Create a new listener instance.
     *
     * @param  AppLogger  $logger  Application logger wrapper.
     */
    public function __construct(protected AppLogger $logger)
    {
        $this->logger = $logger;
        $this->logger->setContext('HandleOrderPaid Listener');
        $this->logger->info('HandleOrderPaid listener initialized');
    }

    /**
     * Handle the event when an order is marked as paid.
     */
    public function handle(OrderPaid $event): void
    {
        $order = $event->order->fresh();
        $payment = $event->payment->fresh();

        if (! $order instanceof Order || ! $payment instanceof OrderPayment) {
            $this->logger->error('Invalid event data: Order or Payment is not an instance of the expected class', [
                'order' => $order,
                'payment' => $payment,
            ]);
            return;
        }

        // Send payment confirmation email to the customer
        $this->sendPaymentConfirmation($order, $payment);

        // Create a customer account if one does not already exist
        $this->createCustomerAccountIfNeeded($order);

        // Activate matching subscription codes from purchased SKU items
        $this->processSubscription($order);
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
     * Activate subscription(s) for a paid order by mapping purchased SKU values to activation codes.
     */
    protected function processSubscription(Order $order): void
    {
        $customerEmail = $this->normalizeEmail($order->customer_email);
        $customerName = $order->customer_name ?: 'Customer';
        $planCatItem = $order->items->first(function ($item) {
            return $item->catalogItem?->sellable_type === 'App\Models\Plan';
            // return $item->catalogItem?->sellable_type === Plan::class;
        });

        if(! $planCatItem) {
            $this->logger->info('No plan catalog item found in order, skipping subscription activation', [
                'order_uid' => $order->uid,
            ]);
            return;
        }

        $plan = $planCatItem->catalogItem->sellable;

        if (! $plan) {
            $this->logger->error('No plan found for catalog item, skipping subscription activation', [
                'order_uid' => $order->uid,
                'catalog_item_uid' => $planCatItem->catalogItem?->uid,
            ]);
            return;
        }

        $user = User::whereRaw('LOWER(email) = ?', [$customerEmail])->first();

        if (! $user) {
            $this->logger->error('No user found for email, skipping subscription activation', [
                'order_uid' => $order->uid,
                'customer_email' => $customerEmail,
            ]);
            return;
        }

        /** @var SubscriptionService $subscriptionService */
        $subscriptionService = app(SubscriptionService::class);
        $subscriptionService->activatePlan($order, $plan, $user);
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
