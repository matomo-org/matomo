/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
const { defineConfig, devices } = require('@playwright/test');
const { shardFiles } = require('./legacy/specs');

// Baselines are only ever written by CI. Any other browser (for example the arm64 Chromium in DDEV)
// renders slightly differently, so its screenshots get a "-local" suffix and are gitignored.
const matomoUrl = process.env.MATOMO_URL || 'http://localhost/';
const snapshotSuffix = process.env.PLAYWRIGHT_SNAPSHOT_SUFFIX ? `-${process.env.PLAYWRIGHT_SNAPSHOT_SUFFIX}` : '';
const escapeRegExp = (value) => value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');

// PLAYWRIGHT_SHARD=2/10 runs the second of ten shards of similar duration (see legacy/specs.js).
// The Mocha specs run through legacy/runtime.js, from wrappers generated into legacy/generated.
const testMatch = shardFiles(process.env.PLAYWRIGHT_SHARD).map((file) => new RegExp(`${escapeRegExp(file)}$`));

module.exports = defineConfig({
  testDir: '.',
  testMatch,
  // One Matomo instance and one global tmp/testingPathOverride.json per job, so tests can't run in parallel.
  workers: 1,
  fullyParallel: false,
  forbidOnly: !!process.env.CI,
  retries: 0,
  timeout: 120_000,
  snapshotPathTemplate: `{testDir}/screenshots/{testFilePath}/{arg}${snapshotSuffix}{ext}`,
  // normal runs fail on a missing baseline; `npm run test:update` (re)writes them
  updateSnapshots: process.env.PLAYWRIGHT_UPDATE_SNAPSHOTS || 'none',
  expect: {
    timeout: 20_000,
    toHaveScreenshot: {
      animations: 'disabled',
      caret: 'hide',
      scale: 'css',
    },
  },
  reporter: process.env.CI
    ? [['list'], ['github'], ['html', { open: 'never', outputFolder: 'playwright-report' }], ['junit', { outputFile: 'results/junit.xml' }]]
    : [['list']],
  use: {
    // relative URLs like '?module=CoreHome&action=index' go through the test proxy
    baseURL: `${matomoUrl}tests/PHPUnit/proxy/index.php`,
    viewport: { width: 1350, height: 768 },
    deviceScaleFactor: 1,
    locale: 'en-US',
    timezoneId: 'UTC',
    // present a regular Chrome: TrackingSpamPrevention blocks headless user agents by default
    userAgent: devices['Desktop Chrome'].userAgent,
    extraHTTPHeaders: { 'Accept-Language': 'en-US' },
    actionTimeout: 20_000,
    navigationTimeout: 60_000,
    trace: 'retain-on-failure',
    screenshot: 'only-on-failure',
    launchOptions: {
      executablePath: process.env.PLAYWRIGHT_CHROMIUM_EXECUTABLE || undefined,
      args: [
        // Chrome renders text on composited layers with grayscale instead of LCD anti-aliasing, and
        // whether a layer gets composited depends on timing, so screenshots flip between the two
        '--disable-lcd-text',
        ...(process.env.PLAYWRIGHT_CHROMIUM_EXECUTABLE ? ['--no-sandbox'] : []),
      ],
    },
  },
  projects: [{ name: 'chromium', use: { browserName: 'chromium' } }],
});
