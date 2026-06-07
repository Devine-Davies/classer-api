# Promotional Redemption Flow – Simple Implementation Plan

## Implementation Status (2026-06-06)

Completed in code:

- Added persisted redemption records via `promotion_redemptions` migration.
- Added `PromotionRedemption` model and `PromotionRedemptionService`.
- Integrated `HandleOrderPaid` to issue redemption records for eligible SKU orders.
- Added `MailPromotionalRedeemEmail` job to send tokenized redeem links.
- Added redeem route and controller: `GET /promotions/redeem/{token}`.
- Added token validation + activation flow in `PromotionRedemptionService::redeemFromToken`.

Still pending for full parity with this plan:

- Login-bound redemption UX (current flow uses customer email lookup, not authenticated session ownership enforcement).
- Dedicated redemption form/page UX that captures login/create-account and resume behavior.
- Admin redemption visibility in order detail.

## Goal

When a customer buys an eligible product, send them a promotional redeem email. The email should contain a secure redeem URL. When the customer clicks the URL, the app validates the promotion and activates the benefit.

---

# Phase 1 – Create Promotion Redemption Record

When an order is paid, check whether the order contains an eligible product SKU.

Example eligible SKU:

```text
CLS-CS-6M-001
```

If eligible, create a record in:

```text
promotion_redemptions
```

Suggested fields:

```text
uid
promotion_code
order_id
order_item_id nullable
user_id nullable
customer_email
status
redeem_token_hash
redeemed_at nullable
expires_at nullable
created_at
updated_at
```

Recommended statuses:

```text
pending
emailed
redeemed
expired
cancelled
```

---

# Phase 2 – Send Promotional Redeem Email

After creating the redemption record, send an email to the order customer email.

The email should include a secure redeem URL.

Example URL:

```text
https://classer.com/promotions/redeem/{token}
```

The token should be:

* Random
* Long
* Unique
* Not guessable

Example:

```text
redeem_token = random 64 character string
```

After email dispatch, update the record:

```text
status = emailed
```

---

# Phase 3 – Add Redeem Route

Create a public web route for redemption.

Example route:

```text
GET /promotions/redeem/{token}
```

This page should:

1. Find the redemption record by token.
2. Check the redemption exists.
3. Check the status is not already redeemed.
4. Check the promotion has not expired.
5. Check the customer email exists.
6. Ask the user to log in or create an account if needed.
7. Activate the promotion.
8. Mark the redemption as redeemed.

---

# Phase 4 – Validate the Promotion

When the redeem URL is opened, validate:

* Redemption token exists
* Redemption status is `emailed` or `pending`
* Redemption has not expired
* Redemption has an order
* Order is paid
* Customer email exists
* Promotion has not already been redeemed

If any validation fails, show a clear message:

```text
This promotion link is invalid.
This promotion has already been redeemed.
This promotion has expired.
Please contact support.
```

---

# Phase 5 – Check Customer Email / User Account

The redemption record should store:

```text
customer_email
```

When the user clicks redeem:

## If user is logged in

Check:

```text
logged in user email == redemption customer_email
```

If yes, allow redemption.

If no, show:

```text
This promotion was sent to a different email address.
```

## If user is not logged in

Check whether a user exists with:

```text
customer_email
```

### If user exists

Ask them to log in.

### If user does not exist

Send them to account creation / verification flow.

After account creation, return them to the redemption flow.

---

# Phase 6 – Activate the Promotion

Once the user is verified:

1. Create or update the relevant subscription/access record.
2. Attach the promotion to the user.
3. Set access expiry date.

Example for 6 months of Classer Share:

```text
promotion_code = CLASSER_SHARE_6_MONTHS
starts_at = now()
ends_at = now() + 6 months
```

Then update redemption:

```text
status = redeemed
redeemed_at = now()
user_id = user.uid
```

---

# Phase 7 – Prevent Duplicate Redemption

Add database constraints to prevent double redemption.

Recommended:

```text
unique(redeem_token)
unique(order_id, promotion_code)
```

Optional if using order item:

```text
unique(order_item_id, promotion_code)
```

The redeem action should run in a database transaction with row locking.

This prevents the same token being redeemed twice if the user double-clicks or refreshes.

---

# Phase 8 – Admin Visibility

In admin order detail, show:

* Promotion code
* Customer email
* Status
* Redeemed at
* Expires at
* Linked user

This helps with support questions.

---

# MVP Scope

Build only:

* Create redemption after eligible paid order
* Send redeem email
* Public redeem URL
* Validate token
* Check matching customer email
* Require login/account creation before activation
* Activate promotion
* Mark redemption as redeemed

Avoid for now:

* Multiple promotions per item
* Manual promo reassignment
* Promo transfer between emails
* Bulk promotion campaigns
* Complex expiry rules
