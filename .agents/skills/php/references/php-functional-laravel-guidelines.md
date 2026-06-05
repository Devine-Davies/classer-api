# PHP Functional Laravel Guidelines

Use this reference when implementing or reviewing PHP/Laravel code with the `php` skill.

## Functional-First Defaults

Prefer:

- Pure functions for deterministic transformations
- Explicit inputs/outputs
- Stateless composition
- Small focused methods

Avoid:

- Hidden side effects
- Shared mutable state
- Large mixed-responsibility methods

## Architecture Rules

Controllers should:

- Validate
- Delegate
- Return response

Business logic should live in:

- Actions/services
- Domain functions
- Value objects

Keep side effects at the edges:

- Database writes
- Queue dispatches
- Mail sends
- External API calls

## PHP Standards

- Use typed properties and return types
- Prefer constructor injection
- Use modern features where they improve clarity
- Keep method names action-oriented and domain-specific

## Laravel Standards

- Validation in Form Requests or dedicated validators
- Prefer Eloquent/Query Builder over raw SQL
- Use jobs for long-running/background work
- Use events for integrations/notifications, not core domain invariants

## Testing Standards

Tests should verify:

- Business behavior
- Edge cases
- Failure paths

Avoid tests tightly coupled to implementation internals.

## Payment/Webhook Standards

- Webhooks are source of truth for payment state
- Verify signatures before processing
- Handlers must be idempotent
- Persist and dedupe external event identifiers

## Review Checklist

- Is domain logic separated from framework/transport concerns?
- Are side effects explicit and minimal?
- Are names clear and domain-relevant?
- Are failure modes handled explicitly?
- Is regression coverage present for changed behavior?
