const { join } = require('path');

/**
 * Pins the Chrome for Testing version Puppeteer downloads (on `npm ci`) and launches for UI
 * screenshot tests. CI and local linux/x64 runs (incl. DDEV as amd64 under Rosetta) use the same
 * binary; native linux/arm64 has no Chrome for Testing build and falls back to a system browser
 * (see tests/UI/config.dist.js).
 *
 * Bumping this version is a deliberate event: it changes rendering, so ALL expected screenshots
 * (core and plugins) must be re-generated against the new browser.
 */
module.exports = {
  // Keep the browser inside the harness dir so it survives DDEV container rebuilds
  // (as Puppeteer 8's node_modules/.local-chromium did).
  cacheDirectory: join(__dirname, '.chrome-cache'),
  chrome: {
    version: '150.0.7871.124',
    // No linux/arm64 build exists; skip the download there so `npm ci` succeeds.
    skipDownload: process.arch !== 'x64' && process.platform === 'linux',
  },
  // Unused: tests run the default "new" headless mode of the full Chrome binary.
  'chrome-headless-shell': { skipDownload: true },
};
