---
name: php
description: 'Design, implement, review, and debug modern PHP 8.2+ and Laravel code using a functional-leaning workflow. Use for new features, refactors, bug fixes, endpoint work, queue jobs, and payment/webhook flows with test and quality gates.'
argument-hint: 'Describe the PHP/Laravel task, affected files, and constraints'
user-invocable: true
disable-model-invocation: false
---

# PHP Functional Laravel Workflow

## What This Skill Produces

This skill produces production-ready PHP/Laravel changes that are:

- Functional-first where practical
- Thin at framework edges (controllers, middleware, jobs)
- Explicit about side effects
- Typed, documented, and testable
- Verified with clear completion gates

## When to Use

Use this skill when a request involves:

- Laravel controllers, requests, jobs, events, services, models, or mail
- Business logic implementation or refactoring
- Endpoint behavior changes and validation flow
- Stripe/payment or webhook handling
- Test additions/fixes for PHP behavior
- Code review for maintainability and risk

## Inputs To Collect First

1. User-visible outcome and acceptance criteria
2. Affected domain area and entry points (routes/controllers/jobs/commands)
3. Constraints: backward compatibility, deadlines, style rules, performance
4. Required side effects: DB writes, queues, emails, external APIs
5. Verification target: feature tests, unit tests, or both

## Procedure

1. Map execution path
- Locate the request entry point and all touched layers.
- Identify where logic currently lives and where it should live.

2. Classify code by responsibility
- Keep controllers thin: validate, delegate, respond.
- Move business logic to services/actions/domain functions/value objects.
- Keep side effects at boundaries.

3. Choose implementation strategy
- If logic is deterministic and transform-based, write pure functions first.
- If logic depends on infrastructure (DB/API/files), isolate side-effect code behind explicit methods.
- If function complexity grows, split into focused methods with clear names.

4. Apply Laravel conventions
- Validation: Form Request or dedicated validator.
- Data access: Eloquent/Query Builder before raw SQL.
- Background work: jobs for expensive/non-blocking operations.
- Events: for notifications/integrations, not core business rules.

5. Enforce language quality
- Use strict typing and explicit return types.
- Prefer modern PHP features when they improve readability (readonly, enums, match, constructor promotion).
- Use descriptive names for variables and methods.

6. Document intent and behavior
- Add PHPDoc on public classes/methods where intent is not obvious.
- Document exceptions and non-obvious side effects.

7. Verify with tests
- Add or update tests around behavior, edge cases, and failure paths.
- Prefer assertions on outcomes, not implementation details.

8. Run completion gates
- Code compiles/loads without syntax errors.
- Tests covering changed behavior pass.
- No duplicate side effects in retry/re-entrant paths.
- Acceptance criteria are satisfied.

## Decision Branches

### Branch A: New Feature

1. Define API/contract first (request + response shape).
2. Implement domain logic in service/action.
3. Wire framework edges (controller/job/event) last.
4. Add feature tests for happy path + validation + failure path.

### Branch B: Bug Fix

1. Reproduce bug with a failing test when feasible.
2. Trace exact data/state transition causing failure.
3. Fix closest to root cause, not symptom.
4. Add regression coverage and verify no behavior regressions.

### Branch C: Refactor

1. Add characterization tests for current behavior.
2. Extract pure computations from mixed methods.
3. Reduce mutation and nesting, add early returns.
4. Keep public contracts stable unless change is requested.

### Branch D: Stripe/Webhook Flow

1. Treat webhook events as source of truth.
2. Verify signatures before any state changes.
3. Enforce idempotency using persisted external IDs.
4. Fulfill only after verified paid event and idempotency check.

## Quality Criteria

A task is complete only when all are true:

- Business rules are explicit and isolated from transport/framework code.
- Side effects are intentional, minimal, and easy to audit.
- Names are domain-specific (avoid generic placeholders like data/result/value).
- Error handling is explicit for known failure modes.
- Tests cover success, edge, and failure behavior.

## Output Format For Responses

When using this skill, respond with:

1. Short approach summary
2. Files changed and why
3. Full code edits
4. Test plan and what was executed
5. Remaining risks or assumptions

## References

- [PHP Functional Laravel Guidelines](./references/php-functional-laravel-guidelines.md)
