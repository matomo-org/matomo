# AGENTS.md

This document describes how changes should be approached in Matomo, what quality bar is expected, and how agents should route work to Matomo-specific skills when those skills are available.

It is intended for:

- AI coding agents
- Matomo core engineers
- Reviewers looking for a shared baseline

This is not a generic PHP guide. It captures Matomo-specific priorities, trade-offs, and review instincts.

## Matomo Agent Skills

Detailed operational rules for Matomo agent work live in the `matomo-agent-skills` repository:

- SSH: `git@github.com:matomo-org/matomo-agent-skills.git`
- HTTPS: `https://github.com/matomo-org/matomo-agent-skills`

`AGENTS.md` can instruct capable agents to use those skills, but it cannot install or enable external skills by itself. Agents with skill support should install the Matomo skills from that repository and use the relevant skill before planning, editing, reviewing, or validating work. Agents without skill support should still follow the high-level rules in this file and consult the project documentation listed below.

At the start of Matomo work, agents with skill support should check which Matomo skills are installed. If one or more relevant skills are missing, warn the user before continuing and offer to install or update the missing skills from `matomo-agent-skills`. If the user declines or installation is not possible, continue with the high-level rules in this file and explicitly note which skill guidance could not be applied.

Use the most specific applicable skill:

| Work type | Skill |
| --- | --- |
| PHPStan, PHPCS, PHPCBF, PHP style/static analysis | `matomo-code-quality` |
| Running PHP, UI, or Vue/Jest tests | `matomo-test-runner` |
| Branch, PR, or git-range review | `matomo-review` |
| In-development debt or maintainability review | `matomo-debt-check` |
| Security-sensitive changes: access control, CSRF, SQL, request parsing, tokens, secrets | `matomo-security-rules` |
| Public API methods or request-facing contracts in `plugins/*/API.php` | `matomo-api-development-rules` |
| Plugin structure, layering, event registration, cross-plugin boundaries | `matomo-plugin-architecture` |
| Vue source, Vue build workflow, Vue template sinks | `matomo-vue-development-rules` |
| Twig templates, escaping, raw output, template nonce patterns | `matomo-twig-development-rules` |
| Translation keys, placeholders, translation files | `matomo-i18n-development-rules` |
| Core or plugin update migrations | `matomo-migrations-workflow` |
| Deprecations and compatibility transitions | `matomo-deprecation-rules` |
| PHPDoc, public API docs, posted event docs | `matomo-documentation` |
| Auditing screenshot-based UI tests | `matomo-ui-screenshot-audit` |
| Applying an approved screenshot UI-test audit | `matomo-ui-screenshot-patch` |

When more than one skill applies, use the narrow layer-specific skill for implementation details and the cross-cutting skill for broader policy. For example, use `matomo-twig-development-rules` for a concrete Twig `|raw` change and `matomo-security-rules` for the trust-boundary implications.

## Repo Entrypoints

- PHPCS ruleset: `phpcs.xml`
- PHPStan config and baseline: `phpstan.neon`, `phpstan-baseline.neon`
- PHPUnit config: `tests/PHPUnit/phpunit.xml.dist`
- Test layout: `tests/PHPUnit/Unit`, `tests/PHPUnit/Integration`, `tests/PHPUnit/System`, `tests/UI`, `tests/javascript`
- Stylelint config: `.stylelintrc.json`
- Dev/test docs: `CONTRIBUTING.md`, `tests/README.md`, `tests/README.screenshots.md`, `tests/client/README.md`, `.ddev/README.md`

## How Changes Should Be Approached

Be pragmatic, not dogmatic.

- Prefer simple, local changes over architectural purity.
- Respect existing patterns in the area you are modifying.
- Avoid drive-by refactors unless the change genuinely requires them.
- If you touch code, you own its quality, including tests and relevant checks.
- Assume your change will run on very large Matomo instances.

## Quality And Validation

- Run the relevant local checks for files you touch.
- Use the existing project configuration and baselines; do not lower the bar or add new ignores casually.
- PHPCS and PHPStan findings in touched code should be fixed properly.
- New features require tests.
- Behavior changes should include regression coverage where reasonable.
- No tests requires an explicit explanation.
- Choose the lightest test that proves the behavior.

For exact Matomo command selection, use `matomo-code-quality` and `matomo-test-runner`.

## Compatibility And Public Surface

Matomo has a large plugin ecosystem. Assume the following are public and relied upon unless clearly marked otherwise:

- Public PHP APIs
- Public methods, even if not marked `@internal`
- Database schemas, especially log tables
- HTTP and JavaScript tracker APIs
- Posted events and their parameter contracts

Do not silently break plugin-facing behavior. Compatibility transitions, removals, renames, dependency updates, and public API documentation should be routed through the relevant skills.

## Performance, Security, And Privacy

High-risk areas require extra care:

- Tracking must remain fast and predictable. Avoid additional database queries, remote calls, heavy parsing, or per-request complexity growth.
- Archiving changes must avoid N+1 queries, unbounded memory growth, and logic that scales poorly with visits or sites.
- Large-table schema changes, especially `log_*` tables, are risky and need a migration strategy.
- Security-sensitive code must account for XSS, injection, CSRF, permission checks, request trust boundaries, and secret exposure.
- Privacy-sensitive code must not leak sensitive data, bypass configured privacy settings, re-identify users unexpectedly, or ignore opt-out or consent mechanisms.

Use `matomo-security-rules`, `matomo-migrations-workflow`, and the relevant layer-specific skill when changes touch these areas.

## UI And Frontend

- Prefer Vue for new UI work where it fits the surrounding code.
- Legacy JavaScript, Twig, and mixed approaches still exist and may need to be extended.
- Avoid mixing frameworks unnecessarily.
- Prefer DOM-based UI test assertions where they prove the behavior.
- Add screenshot tests only for visual regressions or interactions that cannot be asserted reliably through DOM state.

Use the Vue, Twig, test-runner, and screenshot-audit skills for detailed rules and workflows.

## Review Smells

Reviewers will slow down or push back when they see:

- No tests and no explanation.
- Tests that duplicate existing coverage or mirror implementation details.
- Screenshot-heavy UI tests where DOM assertions would suffice.
- Over-engineered solutions to simple problems.
- Drive-by refactors unrelated to the task.
- Silent public API or plugin compatibility breaks.
- Tracking, archiving, migration, privacy, or security risks that are not acknowledged.
- Ignoring PHPCS, PHPStan, or failing tests and expecting CI to sort it out.

Optimize for maintainability, safety, and predictable behavior on large Matomo instances.
