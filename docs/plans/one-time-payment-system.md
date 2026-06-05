# Laravel + Stripe Payment Element Implementation Plan

## Goal

Allow customers to purchase a Classer product directly from a custom checkout page while Stripe securely processes payments.

The website should:

1. Display a product page.
2. Allow the customer to enter shipping details.
3. Allow the customer to pay without leaving the website.
4. Create and track orders.
5. Confirm payments through Stripe webhooks.
6. Support future pre-orders and deposits.

## Codebase Alignment Rules

This implementation should follow the application's existing backend conventions rather than introducing a parallel pattern.

Align with the current codebase by default:

* Public/business references should use app-level `uid` values where the rest of the system already does this.
* Input validation should use dedicated `FormRequest` classes in `app/Http/Requests`.
* API responses should use `JsonResource` classes in `app/Http/Resources` where structured responses are returned.
* Thin controllers should delegate business logic to services and jobs.
* Async email delivery should continue to use queued jobs in `app/Jobs`, with email composition living in `app/Mail` and delivery orchestration following the existing `MailSenderController` pattern.
* Operational logging should use the existing `AppLogger` pattern with explicit context names.

## Prerequisites

Before implementation starts, confirm these prerequisites are in place.

### Package Dependencies

Add backend Stripe support:

* `stripe/stripe-php` in `composer.json`

Add frontend Stripe support:

* `@stripe/stripe-js` in `package.json`

If the checkout UI grows beyond a simple server-rendered page, decide whether any additional frontend package is needed or whether the Stripe Element can be initialized directly from the existing Vite setup.

### Environment and Config

Confirm environment variables exist for:

* `STRIPE_KEY`
* `STRIPE_SECRET`
* `STRIPE_WEBHOOK_SECRET`
* `APP_URL`
* `QUEUE_CONNECTION`
* mail delivery settings used by payment confirmations and admin alerts

Add a dedicated Stripe config file if the integration starts needing more than a few env vars.

### Queue and Mail Runtime

Queued email delivery is already part of the application pattern, so payment emails depend on queue processing being available.

Confirm:

* the `mail` queue is being processed in local and production environments
* `QUEUE_CONNECTION` is not left on `sync` for environments where async delivery is expected
* mail credentials and sender identity are working before payment launch

### Stripe Account Setup

Confirm Stripe account prerequisites:

* test account access
* live account access when moving toward launch
* webhook endpoint registration
* enabled payment methods for the target region
* Apple Pay domain verification if Apple Pay is kept in MVP
* business profile, statement descriptor, and refund ownership decisions

### Local Development Prerequisites

Confirm local developer workflow for:

* running migrations and seeders
* receiving Stripe webhook events locally via Stripe CLI or equivalent forwarding
* testing over a usable local URL when payment redirects or wallet flows require it
* rebuilding frontend assets through Vite after Stripe JS is added

### Product and Operational Decisions

Implementation should not start until these decisions are explicit:

* whether checkout is guest-only or can attach orders to authenticated users
* whether shipping is fixed, free, or dynamically calculated
* which single product is MVP-ready with final amount/currency
* who receives admin payment failure alerts
* whether refunds are manual admin actions only in MVP

---

# Phase 1 – Define the Purchase Flow

## Customer Journey

1. User visits product page.
2. User clicks Buy Now.
3. Laravel creates a pending order.
4. User is taken to checkout.
5. Laravel creates or retrieves the active Payment Intent for that order.
6. User enters:

   * Name
   * Email
   * Shipping Address
7. Stripe Payment Element loads.
8. User enters payment details.
9. Payment is submitted.
10. Stripe may require additional customer action such as 3D Secure.
11. Stripe webhook confirms the final payment outcome.
12. Order becomes Paid if payment succeeds.
13. Confirmation email is sent.
14. Order appears in admin dashboard.

## Lifecycle Summary

Make the lifecycle explicit in implementation:

* Buy Now
* Create pending order
* Load checkout page
* Create or retrieve Payment Intent
* Customer attempts payment
* Webhook reconciles final payment state
* Order advances only after confirmed payment success

---

# Phase 2 – Database Design

## Products

Store:

* Name
* Description
* Price
* Currency
* Active status

## Orders

Store:

* UID
* Product
* Quantity
* Amount
* Currency
* Status
* Customer Name
* Customer Email
* Shipping Address
* Stripe Payment Intent ID
* Payment Date

## Order Payments

Prefer a dedicated `order_payments` table for one-time checkout lifecycle tracking.

Store:

* UID
* Order UID or order foreign key
* Stripe Payment Intent ID
* Stripe customer ID if applicable
* Stripe payment method ID if returned
* Payment status
* Amount
* Currency
* Failure code
* Failure message
* Paid at timestamp
* Refunded at timestamp if applicable

## Payment Records Alignment

The codebase already has Stripe-aware payment storage patterns in the existing payment method model, but one-time purchases should not automatically reuse that model.

Before implementation starts, decide explicitly whether one-time payments should:

* Use a dedicated `order_payments` table for payment-intent lifecycle tracking
* Reuse `payment_methods` only when a customer explicitly saves a payment method for future use
* Or use both, with `payment_methods` reserved for reusable payment method metadata and `order_payments` for each checkout attempt

Whichever option is chosen, the plan should preserve consistency with the rest of the app:

* Use `uid` for app-facing identifiers
* Keep Stripe IDs as dedicated columns
* Keep order state separate from raw provider state
* Avoid coupling fulfilment logic directly to Stripe response payloads

## Order Statuses

* Pending
* Paid
* Processing
* Shipped
* Completed
* Refunded
* Cancelled

## Payment Statuses

Keep payment attempt state separate from order fulfilment state.

Suggested payment statuses:

* Pending
* Payment Processing
* Payment Requires Action
* Payment Failed
* Paid
* Refunded

---

# Phase 3 – Stripe Setup

## Stripe Configuration

Configure:

* Publishable Key
* Secret Key
* Webhook Secret

Enable:

* Test Mode
* Apple Pay
* Google Pay
* Cards

## Config Alignment

Configuration should be added through the existing Laravel config and environment approach.

Plan for:

* `.env` keys for Stripe secret, publishable key, and webhook secret
* a dedicated config surface if Stripe settings start growing beyond a few env vars
* clear separation between test and production values
* queue/mail configuration verification as part of payment launch readiness

---

# Phase 4 – Product Page

## Product Display

Show:

* Product image
* Product description
* Price
* Buy button

## Buy Button Behaviour

When clicked:

* Create pending order
* Redirect to checkout page

Do not create the final payment attempt at the Buy Now click itself.

The order should exist first, and the payment intent should be created or retrieved when checkout initializes.

## Route Placement

This app already separates:

* website pages in `routes/web.php`
* authenticated API endpoints in `routes/api.php`
* admin UI pages as route-per-section web views

The one-time payment flow should follow that split:

* Product and checkout pages should live in web routes
* Payment intent creation and webhook handlers should live in API routes or a dedicated webhook route surface
* Admin order pages should be added to the existing admin shell pattern rather than as a separate standalone admin area

---

# Phase 5 – Checkout Page

## Customer Information Section

Collect:

* Full Name
* Email Address
* Shipping Address

## Order Summary

Display:

* Product Name
* Quantity
* Shipping Cost
* Total Cost

## Payment Section

Embed Stripe Payment Element.

The user never leaves the website.

Supported payment methods:

* Credit Card
* Debit Card
* Apple Pay
* Google Pay

## Validation Layer

Checkout input should not be validated inline in controllers.

Plan dedicated request objects for:

* create checkout / create pending order
* create payment intent
* webhook payload verification input if needed
* admin order update actions such as fulfilment or shipping updates

---

# Phase 6 – Payment Intent Creation

When checkout loads:

1. Laravel creates or retrieves the active payment attempt for the pending order.
2. Laravel creates a Payment Intent if one does not already exist for that active attempt.
3. Laravel stores the Stripe Payment Intent ID on the order payment record.
4. Laravel returns the Client Secret.
5. Frontend initializes Stripe Payment Element.

This becomes the connection between:

```text
Order
⇅
Stripe Payment Intent
```

## Service Layer Ownership

Payment intent creation should be owned by a dedicated service rather than implemented directly inside a controller.

That service should be responsible for:

* creating or locating the pending order
* creating or locating the active order payment attempt
* creating or retrieving the Stripe payment intent
* storing Stripe identifiers on the correct payment model(s)
* enforcing amount and currency integrity from server-side product data
* returning only the minimal checkout payload needed by the frontend

This keeps controllers thin and aligns with the existing use of service classes in the app.

## Response Shape

If the frontend consumes structured checkout state, return it through explicit API resources instead of ad hoc arrays so the payment flow matches the application's existing resource pattern.

---

# Phase 7 – Payment Confirmation

When the customer submits payment:

1. Stripe validates payment.
2. Stripe handles authentication if required.
3. Stripe returns payment status.
4. Customer sees success or failure state.

Important:

Payment success in the browser should never be considered final.

---

# Phase 8 – Webhook Processing

Stripe sends events directly to Laravel.

## Successful Payment

When payment succeeds:

* Verify webhook signature.
* Pass the verified Stripe event to the payment service.
* Reconcile payment state against the current order payment attempt.
* Mark the order payment as Paid.
* Mark the order as Paid.
* Store payment reference.
* Store payment timestamp.
* Trigger fulfilment process.

This webhook becomes the single source of truth.

## Webhook Implementation Notes

Use the existing application pattern of keeping webhook controllers thin:

* Controller verifies Stripe signature.
* Controller passes the verified event to `StripePaymentService` or equivalent.
* Service reconciles payment state and performs order state updates.
* Follow-up side effects should be dispatched via jobs in `app/Jobs`.
* Email content should live in mailables in `app/Mail` rather than being assembled inline in controllers or jobs.
* Duplicate webhook events must remain idempotent so the same job or mail flow is not triggered twice.

Also align webhook processing with current operational practices:

* Wrap critical state transitions in database transactions where needed
* Log key reconciliation steps using `AppLogger` with a dedicated payment context
* Persist enough Stripe reference data to debug failures after the fact
* Trigger admin alert jobs for unexpected reconciliation errors

## Webhook Events

Explicitly support these Stripe webhook events in MVP planning:

* `payment_intent.succeeded`
* `payment_intent.payment_failed`
* `payment_intent.processing`
* `charge.refunded`

For Payment Element, `payment_intent.succeeded` should be treated as the primary event for marking an order as paid.

## Idempotency

Webhook idempotency should be enforced at the database level, not only in code.

Plan to persist processed Stripe event IDs, ideally in a dedicated `stripe_events` table with a unique event ID constraint.

That gives:

* duplicate webhook protection
* safer webhook replays
* a traceable audit record for payment event handling

---

# Phase 9 – Customer Communications

## Payment Confirmation Email

Send after webhook confirmation.

Include:

* Order Number
* Product
* Amount Paid
* Shipping Address
* Next Steps

## Shipping Confirmation Email

Send when fulfilment begins.

Include:

* Tracking Number
* Delivery Information

## Mail System Integration

This feature should plug into the existing mailing architecture already used elsewhere in the app.

Use `app/Mail` for:

* Order payment confirmation mail template
* Shipping confirmation mail template
* Optional admin notification mail template for payment failures or fulfilment exceptions

Use `app/Jobs` for:

* Dispatching customer confirmation emails after webhook-confirmed payment
* Dispatching shipping emails when fulfilment status changes to Shipped
* Dispatching admin alert emails if payment reconciliation or fulfilment steps fail

This keeps responsibilities clear:

* `app/Mail` owns message structure and content
* `app/Jobs` owns asynchronous delivery and retry behaviour
* `MailSenderController` can remain the orchestration surface if that existing pattern is preserved
* controllers/services own business state transitions

Following this pattern will keep the new payment flow consistent with the existing jobs such as the mail dispatch jobs already in the codebase.

---

# Phase 10 – Admin Dashboard

## Orders View

Display:

* Order ID
* Order UID
* Customer
* Product
* Status
* Amount
* Payment Date

## Order Detail View

Display:

* Customer Information
* Shipping Information
* Stripe References
* Fulfilment Status

## Admin Surface Alignment

The admin UI should follow the route-per-section pattern already used in the current admin shell.

Plan for:

* a dedicated admin page route for orders
* a matching authenticated admin API endpoint set for listing and viewing orders
* frontend fetches through the same admin app pattern already used by stats, trends, invites, and logs

---

# Phase 11 – Fulfilment Workflow

## Physical Products

After payment:

1. Order marked Paid.
2. Order enters fulfilment queue.
3. Product packed.
4. Tracking number assigned.
5. Status changed to Shipped.
6. Customer notified.

---

# Phase 12 – Testing

Verify:

* Successful payments
* Failed payments
* Cancelled payments
* Duplicate webhooks
* Email delivery
* Queued job dispatch and retry behaviour
* Mailables render the correct order, shipping, and support details
* Order status updates
* Request validation responses match the app's API format
* API resources expose the intended checkout/admin response shape
* Logging captures enough payment and webhook context for debugging

Test with Stripe test cards before launch.

---

# Phase 13 – Launch

## Production Checklist

* Live Stripe keys configured
* Production webhook configured
* HTTPS enabled
* Email delivery verified
* Order dashboard verified
* First real payment tested

---

# Future Enhancements

Potential future additions:

* Deposits instead of full payment
* Kickstarter-style pre-orders
* Waiting list conversion
* Discount codes
* Multiple products
* Referral system
* Subscription plans
* Shipping integrations

---

# MVP Scope

Build only:

* Product page
* Checkout page
* Stripe Payment Element
* Orders table
* Payment Intent workflow
* Stripe webhook processing
* Confirmation emails via `app/Mail` and `app/Jobs`
* Admin order list

The objective is to create a reliable one-product checkout system that can later support Classer pre-orders and hardware sales.

---

# Implementation Checklist

## Proposed Backend Files

### Models

Add or confirm models for:

* `app/Models/Order.php`
* `app/Models/OrderPayment.php`
* `app/Models/StripeEvent.php`
* optional reuse or extension of `app/Models/PaymentMethod.php` only if one-time checkout later supports saving payment methods for reuse

Model expectations:

* include `uid` fields for app-facing identifiers
* keep Stripe provider IDs in dedicated columns
* define explicit relationships for customer, order payment, and fulfilment state

### Migrations

Add migrations for:

* `database/migrations/*_create_orders_table.php`
* `database/migrations/*_create_order_payments_table.php`
* `database/migrations/*_create_stripe_events_table.php`

Migration expectations:

* integer primary keys can remain internal
* `uid` should be unique and indexed
* store Stripe payment intent ID and any webhook correlation references needed for reconciliation
* preserve status fields separately from provider raw state
* enforce unique Stripe event IDs for idempotency

### Services

Add service classes for:

* `app/Services/OrderCheckoutService.php`
* `app/Services/StripePaymentService.php`
* optional `app/Services/OrderFulfilmentService.php`

Suggested responsibilities:

* `OrderCheckoutService`: create pending order, attach product/amount/shipping data, guard server-side totals
* `StripePaymentService`: create and reconcile order payment attempts, create or retrieve payment intents, validate webhook event mappings, persist Stripe references, and enforce idempotent webhook handling
* `OrderFulfilmentService`: move paid orders into processing/shipping states and coordinate follow-up jobs

### API Controllers

Add or confirm controllers for:

* `app/Http/Controllers/Api/CheckoutController.php`
* `app/Http/Controllers/Api/StripeWebhookController.php`
* `app/Http/Controllers/Api/Admin/OrdersController.php`

Controller expectations:

* keep controllers thin
* validate with `FormRequest` classes
* delegate business logic to services
* return structured responses through resources where appropriate

### Web Controllers

Add web controllers or methods for:

* product detail page
* checkout page
* checkout success / failure return pages if needed
* admin orders section page in the existing admin shell

This may be done by extending existing web controllers if that remains cleaner than introducing entirely new ones.

### Requests

Add request classes for:

* `app/Http/Requests/CheckoutCreateRequest.php`
* `app/Http/Requests/CheckoutPaymentIntentRequest.php`
* `app/Http/Requests/AdminOrderUpdateRequest.php`

Optional:

* a dedicated webhook request wrapper if request normalization is useful, while keeping Stripe signature verification authoritative in the controller/service layer

### Resources

Add resource classes for:

* `app/Http/Resources/OrderResource.php`
* `app/Http/Resources/OrderPaymentResource.php` if needed
* `app/Http/Resources/CheckoutSessionResource.php` or equivalent lightweight checkout payload resource

### Jobs

Add queued jobs for:

* `app/Jobs/MailOrderPaymentConfirmed.php`
* `app/Jobs/MailOrderShipped.php`
* optional `app/Jobs/ProcessPaidOrder.php`
* optional `app/Jobs/MailOrderPaymentFailedAdminAlert.php` if failure alerting needs a dedicated job beyond the existing admin alert job

Job expectations:

* use the `mail` queue for email jobs
* log failures with `AppLogger`
* dispatch `MailAdminErrorAlert` on job failure when the issue needs admin visibility

### Mail

Add mailables for:

* `app/Mail/OrderPaymentConfirmed.php`
* `app/Mail/OrderShipped.php`
* optional `app/Mail/AdminOrderPaymentIssue.php`

These can either be sent directly from jobs or routed through `MailSenderController` if the existing mail orchestration pattern is kept.

---

## Proposed Route Additions

### Web Routes

Add website-facing routes for:

* product page
* checkout page
* optional checkout success page
* optional checkout pending/failure page

Add admin shell routes for:

* `/auth/admin/orders`
* optional `/auth/admin/orders/{uid}` if order detail gets its own page

### API Routes

Add API routes for:

* create pending order / checkout session
* create or fetch payment intent details
* Stripe webhook receive endpoint
* admin order list
* admin order detail
* admin order fulfilment updates such as mark processing / shipped / refunded if those actions are in MVP

---

## MVP Decisions Required

Before implementation begins, make these explicit MVP decisions:

* payment type: full payment or deposit
* whether checkout is guest-only or can attach to authenticated users
* whether refunds are manual admin actions only in MVP

The payment type decision is especially important because it affects:

* amount calculation
* checkout copy
* refund expectations
* fulfilment rules
* whether partial-balance states are needed later

---

## Suggested Build Order

Implement in this order:

1. migrations and models
2. service layer for order creation and Stripe payment intent handling
3. request/resource classes
4. API controllers and routes
5. web checkout page and Stripe Payment Element integration
6. webhook reconciliation flow
7. queued mail jobs and mailables
8. admin orders page and API integration
9. end-to-end testing with Stripe test cards and webhook replay

This order keeps the backend source of truth in place before the frontend and admin surfaces depend on it.
