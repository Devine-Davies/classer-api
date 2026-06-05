# One-Time Payment Flow Plan

## Goal
Build a one-time payment flow where:
- a user completes a one-time Stripe payment,
- the purchase is registered in the Laravel API,
- the user receives a confirmation email.

This plan is implementation-first and aligned with the current codebase.

## Scope
In scope:
- One-time payment (no recurring billing)
- Stripe Checkout + Stripe webhook processing
- Persisting payment/order state in database
- Activating user entitlement via existing subscription models
- Sending confirmation email after successful payment
- End-to-end tests and manual verification steps

Out of scope:
- Refund workflows
- Subscription renewals
- Admin dashboards
- Multi-currency support

## Proposed API Endpoints
- POST /api/payments/one-time/checkout-session
  - Auth required
  - Input: plan_code, success_url, cancel_url
  - Output: checkout_url, checkout_session_id, order_uid

- GET /api/payments/one-time/orders/{orderUid}
  - Auth required
  - Returns order/payment status for polling after redirect

- POST /api/webhooks/stripe
  - Public endpoint
  - Verifies Stripe webhook signature
  - Source of truth for final payment status

## End-to-End Flow
1. Client requests checkout session for a selected plan.
2. API validates user + plan and creates a local payment order in pending state.
3. API creates Stripe Checkout Session and returns checkout_url.
4. Client redirects to Stripe-hosted checkout.
5. Stripe sends webhook event (checkout.session.completed / payment_intent.succeeded or failure).
6. API verifies webhook signature and checks idempotency.
7. API marks order as paid or failed.
8. On success, API creates or updates user entitlement (user_subscriptions).
9. API dispatches confirmation email job.
10. Client polls order status endpoint and reflects final state.

## Data Model Plan
Reuse existing models/tables where possible:
- subscriptions
- user_subscriptions
- payment_methods

Add new table: payment_orders
- id
- uid (unique)
- user_id
- subscription_id
- provider (stripe)
- provider_checkout_session_id (unique nullable)
- provider_payment_intent_id (unique nullable)
- amount
- currency
- status (pending, paid, failed, expired)
- paid_at
- failure_reason
- metadata (json)
- created_at
- updated_at

Add new table: payment_webhook_events
- id
- uid (unique)
- provider
- provider_event_id (unique with provider)
- event_type
- payload_hash
- processing_status (processed, failed)
- processed_at
- error_message
- created_at
- updated_at

## Webhook and Idempotency Rules
- Verify Stripe signature before processing payload.
- Insert webhook event record first.
- If the same provider_event_id already exists, return 200 and stop.
- Process state changes in a DB transaction.
- Lock payment order row when finalizing to avoid double processing.
- Never send duplicate confirmation emails.

## Email Plan
- Trigger only on first successful payment finalization.
- Reuse current mail queue pattern (mail queue).
- Email content should include:
  - user name
  - amount and currency
  - purchased plan title
  - transaction/order reference
  - support contact

## Implementation Phases
Phase 1: Foundations
- Add Stripe SDK
- Add config + env keys
- Create migrations and models for payment_orders and payment_webhook_events

Phase 2: API Endpoints
- Implement checkout-session endpoint
- Implement order-status endpoint

Phase 3: Webhook Processing
- Implement Stripe webhook endpoint
- Add signature verification + idempotency + transactional finalization
- Map paid orders to user_subscriptions

Phase 4: Notifications and Logging
- Dispatch confirmation email job on successful payment
- Add structured logging with order UID and Stripe event ID

Phase 5: Rollout
- Run in Stripe test mode
- Verify with Stripe CLI replay
- Enable via feature flag

## Testing Plan
Unit tests:
- Payment order status transitions
- Idempotency duplicate event handling
- Signature validation utility behavior

Feature tests:
- Create checkout session success/failure
- Webhook success creates entitlement + queues confirmation email
- Duplicate webhook does not create duplicate entitlement/email
- Failed payment webhook sets failed order state

Integration/manual tests:
- Stripe test card success flow
- Stripe payment failure flow
- Webhook replay from Stripe CLI
- Polling endpoint returns correct state after redirect

Regression checks:
- Existing cloud share subscription gate still works
- Existing auth flows are unaffected

## Required Env/Config
Add env vars:
- STRIPE_SECRET_KEY
- STRIPE_WEBHOOK_SECRET
- STRIPE_PUBLISHABLE_KEY
- PAYMENT_ONE_TIME_ENABLED
- PAYMENT_SUCCESS_URL_DEFAULT
- PAYMENT_CANCEL_URL_DEFAULT

## Open Decisions
- Checkout Session only, or support direct PaymentIntent confirmation too?
- One-time entitlement duration per plan (fixed period vs plan-defined).
- Allow multiple active paid entitlements or enforce one active entitlement.
- Send payment failure emails in MVP or post-MVP.

## Definition of Done
- User can complete one-time payment in Stripe test mode.
- API records paid order exactly once.
- Entitlement is created/updated exactly once.
- Confirmation email is sent once after successful payment.
- Automated tests pass for core success/failure/idempotency paths.
