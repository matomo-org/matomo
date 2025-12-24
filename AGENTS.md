# agents.md

This document describes **how changes should be approached in Matomo**, what quality bar is expected, and how reviewers typically evaluate contributions.

It is intended for:

- **AI coding agents** (e.g. GPT-5-Codex via CLI / VS Code)
- **Matomo core engineers**
- **Reviewers** looking for a shared baseline

This is **not** a generic PHP guide. It captures Matomo-specific practices, trade-offs, and “review smells”.

---

## Purpose of `agents.md`

Matomo is a large, long-lived codebase with:

- significant legacy surface area
- strict performance requirements (tracking & archiving)
- strong privacy guarantees
- a large plugin ecosystem that depends on stability

This document exists to:

- reduce avoidable review cycles
- make expectations explicit (especially for AI-generated changes)
- encode reviewer instincts that are otherwise tribal knowledge

---

## How changes should be approached

**Be pragmatic, not dogmatic.**

- Prefer simple, local changes over architectural purity
- Respect existing patterns in the area you are modifying
- Avoid drive-by refactors unless the change genuinely requires it
- If you touch code, you own its quality (including tests)

Assume your change will run on **very large Matomo instances**.

---

## Repo entrypoints (verified)

- PHPCS ruleset: `phpcs.xml`
- PHPStan config + baseline: `phpstan.neon`, `phpstan-baseline.neon`
- PHPUnit config: `tests/PHPUnit/phpunit.xml.dist`
- Test layout: `tests/PHPUnit/Unit`, `tests/PHPUnit/Integration`, `tests/PHPUnit/System`, `tests/UI`, `tests/javascript`
- Stylelint config: `.stylelintrc.json`
- Dev/test docs: `CONTRIBUTING.md`, `tests/README.md`, `tests/README.screenshots.md`, `tests/client/README.md`, `.ddev/README.md`

---

## Code quality & static analysis

### PHPCS (mandatory)

- PHPCS is enforced in CI
- **Run PHPCS locally on files you touch**
- **Fix all PHPCS violations in touched lines**
- Do **not** introduce new PHPCS ignores

Inline ignores (`// phpcs:ignore`) should be **avoided** and are only acceptable when fixing the issue would cause widespread, unrelated churn across many files.

PHPCS output is authoritative — if it flags something in touched code, it should be fixed.

---

### PHPStan (mandatory)

- Use the **existing project configuration and baseline**
- Do **not** lower the bar
- Do **not** add new ignores
- If PHPStan flags code you touched, fix it properly

AI agents are expected to **discover and respect the project’s PHPStan setup**, not invent their own rules.

---

### Run locally what CI runs

These are the actual CI entrypoints in `.github/workflows/*`:

```
composer install --dev --prefer-dist --no-progress --no-suggest
./vendor/bin/phpcs --report-full --report-checkstyle=./phpcs-report.xml

composer install --dev --prefer-dist --no-progress --no-suggest
composer run phpstan

npm install
npx stylelint "**/*.{css,less}" -f github
```

For tests, CI uses `matomo-org/github-action-tests` (see `./.github/workflows/matomo-tests.yml`). Locally, prefer DDEV and Matomo’s test commands from `.ddev/README.md`, e.g.:

```
ddev matomo:init:tests
ddev matomo:console tests:run
ddev matomo:console tests:run-ui
ddev matomo:console tests:run-js
```

---

## Testing expectations

### General rules

- **All new features require tests**
- When modifying existing code, **fill in testing gaps** where reasonable
- No tests **requires an explicit explanation**

Testing is pragmatic, but not optional.

---

### Unit vs integration vs system tests

- **Unit tests** are preferred when feasible
- In practice, pure unit tests are often difficult due to static dependencies
- **Integration tests** are commonly used and acceptable
- **System tests are required for API methods**

Choose the **lightest test** that proves the behavior.

---

### Integration tests (important Matomo detail)

- **Each integration test class gets its own fixture / Matomo instance**
- You **do not need to reset global state or clean up between tests in the same class**
- Tests should assume isolation at the class level, not the method level

Avoid unnecessary teardown/reset logic — it usually indicates misunderstanding of the test setup.

---

### Fixtures & test design

- Use the **smallest fixture possible**
- Avoid massive or global fixtures unless truly necessary
- Avoid brittle tests tied tightly to implementation details
- Avoid over-mocking when it obscures intent

Tests should be readable and explain *why* something works, not just mirror the code.

---

### Database-related tests

- DB-touching code is expected to be tested
- Use appropriate fixtures
- Be mindful of execution time and resource usage
- DB-heavy areas are not exempt — they just require care

---

### UI / JS tests

- UI changes **should have UI tests**
- **Minimise screenshot tests**
    - Only add screenshots for:
        - visual regressions
        - style changes
        - UI interactions that cannot be asserted via DOM state
- **Prefer DOM-based assertions** (structure, attributes, visibility, state) where possible
- Avoid screenshot tests for behavior that can be validated via the DOM
- UI tests are prone to duplication — **search for existing coverage first**

Adding a screenshot test “just in case” is a common review smell.

---

## Legacy vs modern code

The guiding principle is:

> **Keep it simple.**

- Follow the existing style of the code you are modifying
- Opportunistic cleanup is encouraged when already in a file
- Do not introduce abstraction or modern patterns “just because”
- Large refactors require explicit justification

---

## Backward compatibility & public surface

### What is considered public

Assume the following are **public and relied upon** unless clearly marked otherwise:

- Public PHP APIs
- Public methods (even if not marked `@internal`)
- Database schemas (especially log tables)
- HTTP and JS tracker APIs

Exceptions exist, but they should be **explicit and justified**.

---

### Plugins & compatibility

Matomo errs on the side of **not breaking third-party plugins**.

If a change may affect plugins:

- Add a **deprecation comment**
- Provide a migration path where possible
- Document the change in:
    - `CHANGELOG.md` for core
    - the plugin changelog for plugin changes

Silent breakage is not acceptable.

---

## UI stack guidance

- **Prefer Vue** for new UI work
- Legacy JS, Twig, and mixed approaches still exist and may need to be extended
- Avoid mixing frameworks unnecessarily
- Choose the least surprising solution in context

---

## Feature flags

Feature flags are used primarily for:

- **Iterative development**
- **Unreleased or in-progress features**

There is no strict policy around naming or scope.

Do **not** over-engineer feature flag systems or invent governance where none exists.

---

## Database & performance considerations

### Tracking (very strict)

Tracking must be **fast and predictable**:

- No additional database queries
- No remote calls
- No heavy parsing
- No per-request complexity growth

Tracking should be close to constant-time per request.

---

### Archiving

When touching archiving or related code:

- Avoid **N+1 queries**
- Watch **memory growth**
- Be extremely cautious with anything that scales with number of visits or sites

Reviewer instinct: *“Will this blow up archiving on a big customer?”*

---

### Database schema changes

- Schema changes on **large tables (especially log tables)** are risky
- Only change schemas when necessary
- Prefer **additive changes** (e.g. appending columns)
- Use migration files
- Follow the pattern:
    1. Additive change
    2. Backfill
    3. Use new data

Assume some instances have **billions of rows**.

---

## Security & privacy

### Security

Matomo has a large attack surface. Pay close attention to:

- XSS
- Injection (SQL, command, etc.)
- CSRF
- Permission and capability checks

Security regressions are taken seriously.

---

### Privacy (core to Matomo)

Privacy is part of Matomo’s identity.

Do not:

- Leak sensitive data into logs
- Bypass or weaken configured privacy settings
- Accidentally re-identify users or sites
- Ignore opt-out or consent mechanisms

If a change interacts with data collection or exposure, assume heightened scrutiny.

---

## What not to do (especially for AI agents)

Avoid the following:

- Adding tracking fields casually
- Introducing schema changes without a migration strategy
- Logging sensitive or user data
- Adding feature flags unnecessarily
- Ignoring PHPCS / PHPStan and “letting CI catch it”
- Writing duplicate tests or duplicate implementations
- Over-testing meaningless behavior
- Making changes that scale poorly on large instances

---

## How reviewers think (review smells)

Reviewers will slow down or push back when they see:

- No tests and no explanation
- Tests that duplicate existing coverage
- Screenshot-heavy UI tests where DOM assertions would suffice
- Tests that are hard to read or reason about
- Over-engineered solutions to simple problems
- Changes that may cause regressions in other systems not covered by tests
- Performance or privacy risks not acknowledged

Optimise for **maintainability, safety, and predictability**.
