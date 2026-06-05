# PHP Functional Laravel Skill

## Purpose

Use this skill when writing, reviewing, debugging, refactoring, or designing PHP and Laravel applications.

The goal is to produce code that is:

- Functional where practical
- Readable
- Maintainable
- Testable
- Well documented
- Consistent
- Production ready

Follow modern PHP 8.2+ conventions and Laravel best practices.

---

# Core Principles

## Functional First

Prefer functional programming principles where they improve clarity and maintainability.

### Prefer

- Pure functions
- Immutability
- Explicit inputs and outputs
- Function composition
- Stateless transformations
- Predictable behaviour

### Avoid

- Hidden side effects
- Global state
- Unnecessary mutation
- Business logic spread across multiple layers
- Large methods with multiple responsibilities

### Good

```php
public function calculateOrderTotal(
    array $lineItems
): int {
    return array_reduce(
        $lineItems,
        fn (int $total, array $lineItem): int =>
            $total + $lineItem['price'],
        0
    );
}
```

### Bad

```php
public function calculateOrderTotal(): void
{
    $this->total = 0;

    foreach ($this->items as $item) {
        $this->total += $item['price'];
    }
}
```

---

# Architecture

## Thin Controllers

Controllers should only:

- Validate requests
- Call actions/services
- Return responses

Controllers should not contain business logic.

### Good

```php
public function store(
    CreateOrderRequest $request,
    CreateOrderAction $createOrder
): JsonResponse {
    $order = $createOrder(
        $request->validated()
    );

    return response()->json($order);
}
```

---

## Business Logic

Business logic belongs in:

- Actions
- Services
- Domain functions
- Value Objects

Business logic should not live in:

- Controllers
- Jobs
- Commands
- Event Listeners
- Middleware

---

## Side Effects

Keep side effects at the edges.

Examples of side effects:

- Database writes
- File system operations
- Stripe calls
- Email sending
- Queue dispatching
- External APIs

Pure logic should be separated from side effects whenever possible.

---

# PHP Standards

## Modern PHP Features

Prefer:

- Constructor property promotion
- Readonly properties
- Enums
- Match expressions
- Typed properties
- Typed return values
- Named arguments
- First-class callables

---

## Dependency Injection

Prefer constructor injection.

Avoid service locators and hidden dependencies.

### Good

```php
public function __construct(
    private readonly StripeClient $stripeClient
) {
}
```

---

# Documentation Standards

## PHPDoc Required

Every public:

- Class
- Interface
- Enum
- Method
- Function

should include PHPDoc.

PHPDoc should explain intent, not repeat code.

### Good

```php
/**
 * Creates a Stripe Checkout session for a one-time purchase.
 *
 * @param Product $product Product being purchased.
 * @param User $user Purchasing user.
 *
 * @return CheckoutSession
 */
public function createCheckoutSession(
    Product $product,
    User $user
): CheckoutSession {
    ...
}
```

---

## Exceptions

Document exceptions.

```php
/**
 * @throws StripeException
 */
```

---

## Side Effects

Document side effects where relevant.

```php
/**
 * Persists the order and dispatches fulfilment events.
 */
```

---

# Naming Conventions

## Variables

Variable names must communicate intent.

Prefer business language.

### Good

```php
$checkoutPayload
$paymentIntentId
$purchaseRecord
$videoMetadata
$highlightMoments
$customerEmailAddress
```

### Avoid

```php
$data
$result
$value
$item
$obj
$tmp
$response
```

---

## Methods

Method names should describe actions.

### Good

```php
createOrder()
calculatePrice()
buildCheckoutPayload()
importVideoMetadata()
generateHighlights()
validatePurchaseRequest()
```

### Avoid

```php
process()
execute()
run()
doStuff()
handleData()
```

---

## Booleans

Boolean variables should start with:

- is
- has
- can
- should
- was
- requires

Examples:

```php
$isPaymentComplete
$hasUploadedFootage
$canAccessLibrary
$shouldRetryImport
$requiresManualReview
```

---

## Collections

Collections should be plural.

```php
$videoFiles
$purchaseRecords
$highlightMoments
$customerOrders
$validationErrors
```

---

# Function Standards

## Single Responsibility

Functions should do one thing.

If a function requires multiple paragraphs to explain, split it.

---

## Small Functions

Prefer small focused functions.

Ideal length:

- Under 30 lines

Avoid giant methods.

---

## Early Returns

Prefer:

```php
if (! $isValid) {
    return null;
}
```

Instead of:

```php
if ($isValid) {
    ...
}
```

---

## Minimise Nesting

Avoid deep nesting.

Extract logic into functions.

---

# Laravel Standards

## Validation

Use:

- Form Requests
- Dedicated validators

Avoid validation inside controllers.

---

## Database Access

Prefer:

- Eloquent
- Query Builder

Avoid raw SQL unless necessary.

---

## Queues

Use Jobs for:

- Imports
- Exports
- AI processing
- Video processing
- Notifications

---

## Events

Use events for:

- Notifications
- Analytics
- Integrations

Do not hide core business logic inside events.

---

# Testing Standards

Code should be written to be testable.

Prefer dependency injection and pure functions.

---

## Tests Should Verify

- Behaviour
- Business rules
- Edge cases
- Failure paths

Not implementation details.

---

# Security Standards

Always:

- Validate external input
- Escape output when needed
- Use parameter binding
- Verify webhook signatures
- Protect secrets
- Use Laravel authorization features

Never:

- Hardcode secrets
- Trust request payloads
- Trust webhook payloads without verification

---

# Stripe Standards

## Preferred Flow

For one-time purchases:

1. Create internal order
2. Create Stripe Checkout Session
3. Redirect user
4. Listen for webhook
5. Verify signature
6. Mark order as paid
7. Trigger fulfilment

---

## Source of Truth

Stripe webhooks are the source of truth.

Never fulfil an order from the success page redirect alone.

---

## Idempotency

Webhook handlers must be idempotent.

Store:

- checkout_session_id
- payment_intent_id
- stripe_event_id

Prevent duplicate processing.

---

# Code Generation Requirements

When generating code:

1. Briefly explain the approach.
2. Show file paths.
3. Provide complete code.
4. Include PHPDoc.
5. Use strict types.
6. Use descriptive variable names.
7. Separate pure logic from side effects.
8. Mention required migrations/configuration.
9. Explain how to test the solution.
10. Follow Laravel conventions.
11. Prefer functional patterns where practical.

---

# Classer-Specific Guidance

When working on Classer:

- Keep business logic independent from UI.
- Separate import, processing, storage, and presentation concerns.
- Keep video-processing workflows composable.
- Prefer pipeline-style transformations.
- Design services to be testable without file systems or external APIs.
- Isolate integrations such as Stripe, S3, FFmpeg, AI services, and hardware communication behind dedicated abstractions.
- Prioritise clarity and maintainability over clever abstractions.