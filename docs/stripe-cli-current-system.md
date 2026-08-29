# Stripe CLI guide for current Classer checkout flow

This guide explains how to use Stripe CLI with the current one-time checkout implementation in this repository.

## What this project expects

Current API endpoints:
- Checkout API: /api/checkout/orders
- Create/retrieve intent for order: /api/checkout/orders/{orderUid}/intent
- Webhook endpoint: /api/stripe/webhook

Webhook events handled by the service:
- payment_intent.processing
- payment_intent.payment_failed
- payment_intent.succeeded
- charge.refunded

Important behavior:
- The webhook handler only updates records when the incoming payment intent id matches an existing order_payments.stripe_payment_intent_id.
- If Stripe sends an event for an unknown intent id, the request still succeeds but no order is updated.

## Prerequisites

1. Stripe CLI installed
2. Stripe account logged in from CLI
3. App running locally and reachable on your webhook URL
4. Environment variables configured:
- STRIPE_KEY
- STRIPE_SECRET
- STRIPE_WEBHOOK_SECRET

Optional but recommended:
- Queue worker running for mail queue if you want to observe confirmation emails.

## 1) Log in to Stripe CLI

Command:
stripe login

## 2) Start webhook forwarding to this app

Use your local app URL and forward to the project webhook route.

Command:
stripe listen --forward-to http://localhost/api/stripe/webhook

If your app runs on another port or host, replace the URL accordingly.

When stripe listen starts, copy the signing secret shown in output (starts with whsec_) and set it as STRIPE_WEBHOOK_SECRET.

## 3) Create a real payment intent through the app

Best path for realistic testing:
1. Open the checkout page in browser
2. Create an order and initialize payment intent via the normal UI flow
3. Complete payment with a Stripe test card

This guarantees the payment intent id exists in your order_payments table and webhook reconciliation can update order status.

## 4) Trigger events from CLI

### A. Connectivity check

Command:
stripe trigger payment_intent.succeeded

Expected result:
- Webhook endpoint should receive the event
- If intent id does not exist in local order_payments, no order changes happen (this is expected)

### B. Processing and failed scenarios

Commands:
stripe trigger payment_intent.processing
stripe trigger payment_intent.payment_failed

Expected result:
- Useful for endpoint plumbing checks
- State changes occur only when event intent id maps to an existing local order payment

### C. Refunded scenario

Refund works best when tied to a real checkout intent created by your app.

Command pattern:
stripe refunds create --payment-intent <your_real_payment_intent_id>

Then Stripe should emit charge.refunded and update local order/payment to refunded.

## 5) Verify results in the app

Verification options:
- Admin UI Orders page: /admin/orders
- Admin API orders list: /api/admin/orders (requires admin auth token)

Expected state transitions:
- payment_intent.succeeded -> order status paid, payment status paid
- payment_intent.payment_failed -> payment status failed, order remains pending
- charge.refunded -> order status refunded, payment status refunded

## 6) Useful CLI commands

List recent events:
stripe events list

Inspect event details:
stripe events retrieve <event_id>

Inspect payment intent:
stripe payment_intents retrieve <payment_intent_id>

## Troubleshooting

1. Webhook returns invalid
- Usually signature mismatch
- Confirm STRIPE_WEBHOOK_SECRET matches current stripe listen session

2. Event received but order not updated
- Intent id likely not present in order_payments.stripe_payment_intent_id
- Run checkout flow first to create a real local intent

3. No local mail confirmation observed
- Payment succeeded but mail job uses queue
- Ensure queue worker is running for mail queue

4. Runtime error about Stripe SDK missing
- Install backend SDK dependency in app runtime:
composer update stripe/stripe-php

## Suggested local workflow

1. Start app
2. Start stripe listen forwarding to /api/stripe/webhook
3. Create a checkout order in UI and complete payment
4. Confirm order status in admin orders page
5. Trigger refund on the same payment intent and verify refunded status
