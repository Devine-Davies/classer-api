# Discount Code System – Implementation Plan

## Goal

Provide an admin-managed discount code system that supports:

* Product-specific discount codes
* User-specific discount codes
* General discount codes
* Single-use and multi-use codes
* Checkout validation
* Webhook-confirmed redemption tracking

The discount system should integrate cleanly with the existing product, order, payment, and Stripe architecture.

---

# Core Principles

## Server-Side Source of Truth

The frontend may display discount calculations, but the backend must always determine:

* Whether a discount code is valid
* Whether it can be applied to the selected product
* Whether it can be applied to the current user
* The final amount to be charged

The frontend must never be trusted to provide:

* Discount amounts
* Percentage values
* Final totals

---

## Webhook-Confirmed Usage

A discount code should **not** be marked as used when:

* The user enters the code
* The user starts checkout
* The Payment Intent is created

A discount code should only be marked as redeemed when:

* Payment is successfully confirmed via Stripe webhook

This keeps discount usage aligned with actual successful purchases.

---

# Phase 0 – API Contract and Pricing Lifecycle

## Current Checkout Contract

This repository currently uses a two-step checkout contract:

1. Create pending order
2. Create/reuse payment intent for that order

The discount design must fit this flow directly.

---

## Pricing Lifecycle Rule

Discount pricing has two states:

1. Preview pricing (during code apply)
2. Final pricing (at payment intent creation)

Preview pricing is for UX only.
Final pricing is authoritative and must be revalidated server-side when creating the payment intent.

---

## Authoritative Charge Rule

Before creating or reusing a payment intent, backend must:

1. Re-check discount validity (active, date window, restrictions, usage limits)
2. Recompute subtotal, discount, and total from server data only
3. Persist a final pricing snapshot on the order/payment
4. Use final computed total as payment intent amount

If revalidation fails, backend must reject intent creation and require user to review checkout.

---

## API Shape Decision

To avoid contract drift, define one explicit place to receive the discount code:

Option A (recommended):

* Add `discount_code` to create-order payload and persist immediately as candidate code

Option B:

* Add dedicated apply endpoint that stores candidate code against pending order

Regardless of option:

* Final validation still happens at payment intent creation
* Frontend never submits discount amounts or final totals

---

# Phase 1 – Database Design

## Discount Codes

Create a dedicated discount code table.

### Purpose

Stores the definition and rules for each discount code.

### Suggested Fields

```text
uid
code
discount_percentage
max_discount_percentage nullable
min_order_amount nullable

product_id nullable
assigned_user_id nullable
assigned_email nullable

is_active

usage_limit nullable
usage_count

one_use_per_customer

starts_at nullable
expires_at nullable
disabled_at nullable

internal_note nullable
created_by_user_id nullable
updated_by_user_id nullable
disabled_by_user_id nullable

created_at
updated_at
```

---

## Field Behaviour

### Product Restriction

```text
product_id = null
```

Means:

```text
Valid for any product
```

Otherwise:

```text
Only valid for the selected product
```

---

### User Restriction

```text
assigned_user_id = null
assigned_email = null
```

Means:

```text
Valid for anyone
```

Otherwise:

```text
Only valid for the assigned user or email
```

---

### Usage Limits

```text
usage_limit = null
```

Means:

```text
Unlimited uses
```

Example:

```text
usage_limit = 1
```

Means:

```text
Single-use code
```

---

### Percentage Rules

Clamp rules:

```text
discount_amount_applied <= order_subtotal
discount_percentage <= max_discount_percentage (if configured)
discount_amount_applied < order_subtotal
order_subtotal >= min_order_amount (if configured)
```

The strict `< order_subtotal` rule ensures MVP does not support 100% free checkout.

---

# Phase 2 – Redemption Tracking

## Discount Code Redemptions

Create a dedicated redemption table.

### Purpose

Tracks actual successful uses of discount codes.

### Suggested Fields

```text
uid

discount_code_id

order_id
order_payment_id nullable

user_id nullable
customer_email nullable

redeemed_at

created_at
updated_at
```

---

## Required Database Constraints

Add constraints/indexes to guarantee integrity under concurrency:

```text
unique(order_id) on discount_code_redemptions
index(discount_code_id)
index(user_id)
index(customer_email)
```

For one-use-per-customer behavior, enforce with either:

```text
unique(discount_code_id, user_id) where user_id is not null
unique(discount_code_id, customer_email) where customer_email is not null
```

or equivalent transactional locking logic if partial unique indexes are unavailable.

---

## Why a Separate Table?

Allows the system to answer:

```text
Who used this code?
Which order used it?
How many times has it been used?
Has this customer already used it?
```

Avoid storing only:

```text
used_at
```

on the discount code record.

---

# Phase 3 – Admin Management

## New Admin Section

Create:

```text
Admin → Discount Codes
```

---

## Discount Code List

Display:

* Code
* Discount %
* Product Restriction
* User Restriction
* Usage Count
* Usage Limit
* Status
* Expiry Date

---

## Create Discount Code

Admin should be able to configure:

### Basic Details

* Code
* Discount Percentage

### Restrictions

* Product
* User
* Email

### Availability

* Active / Inactive
* Start Date
* Expiry Date

### Usage Rules

* Usage Limit
* One Use Per Customer

### Internal Information

* Internal Note

Examples:

```text
Beta Tester Discount
Launch Promotion
Refund Apology
Competition Winner
```

---

## Editing Rules

If a discount code has already been redeemed:

Prevent editing:

```text
code
discount_percentage
product_id
```

Allow editing:

```text
is_active
expires_at
internal_note
```

This protects historical order accuracy.

---

# Phase 4 – Checkout Integration

## Checkout Screen

Add:

```text
Discount Code
[____________]
[Apply]
```

---

## Discount Validation API

When the user clicks Apply:

Backend validates:

* Code exists
* Code is active
* Start date reached
* Not expired
* Product restriction passes
* User/email restriction passes
* Usage limit not exceeded
* One-use-per-customer rules pass

Validation response should include:

* `is_valid`
* `code`
* `pricing_preview` (subtotal, discount, total, currency)
* `reason_code` for failures

Use stable reason codes so frontend can map errors reliably.
Reason codes must be generic and must not leak internal assignment logic.

Recommended endpoint contract:

```text
POST /api/checkout/orders/{orderUid}/discount
```

Request body:

```json
{
  "discount_code": "SAVE25",
  "customer_email": "guest@example.com"
}
```

Success response (200):

```json
{
  "status": true,
  "is_valid": true,
  "reason_code": null,
  "code": "SAVE25",
  "pricing_preview": {
    "subtotal": 10000,
    "discount": 2500,
    "total": 7500,
    "currency": "gbp"
  }
}
```

Failure response (422):

```json
{
  "status": false,
  "is_valid": false,
  "reason_code": "CODE_NOT_ELIGIBLE",
  "message": "Discount code is not eligible."
}
```

---

## Successful Validation Response

Return:

```text
Discount Percentage
Discount Amount
New Total
```

Display updated pricing on screen.

---

## Failed Validation Response

Return:

```text
Invalid Code
Expired Code
Product Not Eligible
Code Not Eligible
Usage Limit Reached
Minimum Order Not Met
Cannot Reduce Total To Zero
```

Avoid responses such as:

```text
This code was assigned to another email.
```

---

# Phase 5 – Order Storage

When a discount code is applied successfully:

Store discount information directly on the order.

The order should store candidate pricing during apply, then overwrite with final authoritative pricing at payment intent creation.

### Suggested Order Fields

```text
discount_code_id nullable

subtotal_amount
discount_amount
total_amount
amount (legacy mirror of total_amount during migration)

discount_snapshot nullable
```

`discount_snapshot` should include enough immutable data to audit historical orders.

---

## Discount Snapshot

Store a copy of the discount details used at purchase time.

Example:

```json
{
  "code": "LAUNCH25",
  "percentage": 25,
  "subtotal_amount": 9900,
  "discount_amount": 2475,
  "total_amount": 7425,
  "currency": "gbp",
  "validated_at": "2026-06-05T12:00:00Z"
}
```

This ensures historical accuracy if the discount code changes later.

---

# Phase 6 – Payment Intent Creation

When creating a Payment Intent:

Laravel must calculate:

```text
Product Price
- Discount
= Charge Amount
```

The frontend must never provide the final charge amount.

The Payment Intent amount must come entirely from server-side calculations.

If an active payment intent already exists with a stale amount:

* cancel and recreate intent, or
* update intent amount if allowed by Stripe intent state

Record which strategy is used for deterministic behavior.

When creating or updating the payment intent, include Stripe metadata:

```text
order_uid
order_payment_uid
discount_code_uid
discount_code
```

---

# Phase 7 – Webhook Processing

## Successful Payment

When Stripe confirms payment:

1. Verify webhook signature
2. Locate payment using Stripe payment_intent id
3. Resolve order from payment
4. Verify order is still redeemable for this code
5. Create redemption record (idempotent)
6. Increment usage count
7. Mark order as paid
8. Trigger fulfilment workflow

Do not trust client-submitted order identifiers during webhook finalization.

---

## Refund Behaviour

If a paid order is later refunded:

* do not restore discount usage
* do not delete redemption rows
* keep usage_count unchanged

This prevents reusing redeemed campaign inventory after refund events.

---

## Important Rule

Discount codes should only be redeemed here.

Never redeem codes during:

```text
Code Apply
Checkout Load
Payment Intent Creation
```

---

# Phase 8 – Concurrency Protection

## Race Condition Example

```text
Code Usage Limit = 1

Customer A pays
Customer B pays

Both webhook events arrive simultaneously
```

Without protection:

```text
Both payments could redeem the same code
```

---

## Protection Strategy

Use:

```text
Database Transaction
Row Locking
```

During redemption processing.

This guarantees usage limits are enforced correctly.

Also require DB-level uniqueness so integrity does not depend on application timing alone.

---

# Phase 9 – Guest Checkout Support

## Decision

Support both:

* Authenticated users
* Guest users

---

## Guest Validation

If no user account exists:

Validate using:

```text
assigned_email
```

instead of:

```text
assigned_user_id
```

This allows:

```text
Invite-only discount codes
Beta tester discounts
Manual customer rewards
```

without requiring login.

---

# Phase 10 – Testing

## Validation Tests

Verify:

* Valid code
* Invalid code
* Expired code
* Future code
* Product mismatch
* User mismatch
* Email mismatch

---

## Usage Tests

Verify:

* Single-use codes
* Unlimited-use codes
* Usage limits
* One-use-per-customer rules

---

## Payment Tests

Verify:

* Successful payment
* Failed payment
* Abandoned checkout
* Duplicate webhook events
* Stale preview discount rejected at payment intent creation
* Existing intent amount mismatch handling

---

## Redemption Tests

Verify:

* Redemptions are created only on successful webhook finalization
* Duplicate webhook replay does not create duplicate redemption
* Usage limit = 1 cannot be double-redeemed under parallel webhook execution
* one_use_per_customer blocks second purchase by same user/email
* Refunding an order does not restore discount usage

---

## Migration/Compatibility Tests

Verify:

* Legacy `amount` remains compatible during rollout
* `amount == total_amount` for discounted and non-discounted orders
* API responses remain backward compatible while new pricing fields are introduced

---

# Future Enhancements

Potential future additions:

* Free shipping discounts
* Subscription discounts
* Automatic campaigns
* Referral rewards
* Multiple discount codes per order
* Customer segment targeting
* Bulk code generation

---

# MVP Scope

Build only:

* Percentage-only discounts
* Never allow discount to reduce payable total to zero
* Product restrictions
* User/email restrictions
* Usage limits
* One-use-per-customer support
* Checkout discount application
* Order discount snapshots
* Webhook-confirmed redemption tracking
* Admin management section

The objective is to provide a reliable and auditable discount system that integrates cleanly with the existing order and Stripe payment workflow.
