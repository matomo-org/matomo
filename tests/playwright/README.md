# Playwright UI tests

UI tests on [Playwright Test](https://playwright.dev/docs/intro), next to the Mocha/Puppeteer suite in `tests/UI`. Specs are ported one by one from `tests/UI/specs`, keeping the test names.

## Running locally

With DDEV (the fixture database is set up on the first run and then reused):

```bash
ddev matomo:playwright
ddev matomo:playwright specs/comparison.spec.js -g subtable
ddev matomo:playwright --update
ddev matomo:playwright --rebuild-fixture
```

`--update` writes baselines for new or changed screenshots, and `--rebuild-fixture` drops and rebuilds the persisted fixture database. Local screenshots are rendered differently than CI ones, so they are stored as `*-local.png` next to the CI baselines. Those files are gitignored: create them once with `--update` on a clean branch, then compare against them.

Without DDEV, set `MATOMO_URL` to a Matomo checkout that serves `tests/PHPUnit/proxy/index.php` and has `[database_tests]` configured, then run `npm ci && npx playwright test` in this directory. `PLAYWRIGHT_CHROMIUM_EXECUTABLE` selects a system Chromium instead of the one from `npx playwright install chromium`.

Debugging: every failure keeps a trace (`npx playwright show-trace test-results/<test>/trace.zip`) and the HTML report is in `playwright-report`.

## On GitHub Actions

`.github/workflows/ui-playwright.yml` runs the suite against PHP's built-in server and the MySQL preinstalled on the runner. The baselines in `screenshots/` come only from CI. To update them, push a commit whose message contains `[update-screenshots]` (or run the workflow with "update screenshots"), download the `playwright-screenshots` artifact and commit it.

## Writing specs

- A spec is one serial `describe` that shares a page (`openSession()`), because most tests continue from the previous test's state. `restoreFixture()` in `beforeAll` resets the database, so specs don't depend on each other.
- Wait for state, not time: `expect(locator).toBeVisible()`, `toHaveURL()`, `session.waitForIdle()`. The screenshot helpers wait for idle network, and `toHaveScreenshot()` waits until two captures match.
- A test fails on HTTP 5xx, uncaught page errors and requests to external hosts. Call `session.allowServerErrors()` only for a test that provokes one on purpose, or for a documented bug.
- Move the mouse away (`page.mouse.move(-10, -10)`) before screenshots that must not show hover states.
