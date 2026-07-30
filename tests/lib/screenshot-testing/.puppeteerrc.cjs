const { join } = require('path');
const os = require('os');

/**
 * Pins the exact Chrome for Testing build Puppeteer downloads (on `npm ci`) and launches for UI
 * screenshot tests, so CI and local runs render with the byte-identical browser binary.
 *
 * Bumping this version is a deliberate event: it changes rendering, so ALL expected screenshots
 * (core and plugins) must be re-generated against the new browser.
 */
module.exports = {
  // Keep the browser inside the harness directory (like Puppeteer 8's node_modules/.local-chromium)
  // so it survives DDEV container rebuilds and lives in the same place on CI checkouts.
  cacheDirectory: join(__dirname, '.chrome-cache'),
  chrome: {
    version: '150.0.7871.124',
    // Chrome for Testing publishes no linux/arm64 build; skip the download there so `npm ci`
    // succeeds (tests/UI/config.dist.js then falls back to a system browser with a warning).
    skipDownload: os.arch() !== 'x64' && process.platform === 'linux',
  },
  // Unused: tests run the default "new" headless mode of the full Chrome binary.
  'chrome-headless-shell': { skipDownload: true },
};
