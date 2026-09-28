# Playwright UI tests

All UI tests run on [Playwright Test](https://playwright.dev/docs/intro). There are two kinds of test files:

- `specs/*.spec.js` are written for Playwright (locators, `toHaveScreenshot`, `openSession()`).
- The Mocha specs of core (`tests/UI/specs`) and plugins (`plugins/*/tests/UI`) run unchanged through `legacy/runtime.js`. It gives them the globals of the old harness (`page`, `testEnvironment`, `expect(...).to.matchImage()`, `describe`/`it`), on a Playwright browser instead of Puppeteer. `legacy/specs.js` generates one Playwright file per Mocha spec into `legacy/generated` (gitignored) whenever the config loads.

A Mocha spec that gets rewritten for Playwright moves to `specs/` and is added to `PORTED` in `legacy/specs.js`, so it doesn't run twice.

## Running locally

With DDEV (fixtures are set up on first use and then restored from a dump):

```bash
ddev matomo:playwright
ddev matomo:playwright legacy/generated/TagManager -g preview
ddev matomo:playwright --update specs/comparison.spec.js
ddev matomo:playwright --rebuild-fixture
```

`--update` writes baselines for new or changed screenshots, and `--rebuild-fixture` sets fixtures up again instead of restoring the dumps in `tmp/playwright-fixtures`. Local screenshots are rendered differently than CI ones, so they are stored as `*-local.png`, which is gitignored: create them once with `--update` on a clean branch, then compare against them. `PLAYWRIGHT_LEGACY_MODE=default` keeps running a Mocha spec after a failed test (on a fresh page), which helps finding many failures at once.

Without DDEV, set `MATOMO_URL` to a Matomo checkout that serves `tests/PHPUnit/proxy/index.php` and has `[database_tests]` configured, then run `npm ci && npx playwright test` in this directory. `PLAYWRIGHT_CHROMIUM_EXECUTABLE` selects a system Chromium instead of the one from `npx playwright install chromium`.

Debugging: every failure keeps a trace (`npx playwright show-trace test-results/<test>/trace.zip`) and the HTML report is in `playwright-report`.

## On GitHub Actions

`.github/workflows/ui-playwright.yml`, called by `matomo-tests.yml`, runs the tests in 10 shards against PHP's built-in server and the runner's MySQL on tmpfs. `PLAYWRIGHT_SHARD=3/10` picks a shard: `legacy/specs.js` spreads the files by the durations in `legacy/timings.json`, longest first, so the shards take about the same time.

Baselines are generated on CI only and kept as artifacts, not in git. A run for a commit with `[update-screenshots]` in its message (or started by hand with "update screenshots") writes them as `playwright-baselines-<shard>`, and later runs of the same branch compare against the newest ones.

## Writing specs

- A spec is one serial `describe` that shares a page (`openSession()`), because most tests continue from the previous test's state. `restoreFixture()` in `beforeAll` resets the database, so specs don't depend on each other.
- Wait for state, not time: `expect(locator).toBeVisible()`, `toHaveURL()`, `session.waitForIdle()`. The screenshot helpers wait for idle network, and `toHaveScreenshot()` waits until two captures match.
- A test fails on HTTP 5xx, uncaught page errors and requests to external hosts. Call `session.allowServerErrors()` only for a test that provokes one on purpose, or for a documented bug.
- Move the mouse away (`page.mouse.move(-10, -10)`) before screenshots that must not show hover states.
