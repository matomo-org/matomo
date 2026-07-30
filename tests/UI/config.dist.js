/*!
 * Matomo - free/libre analytics platform
 *
 * UI tests config
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * The root Matomo URL to test against.
 */
exports.piwikUrl = "http://localhost/";

/**
 * Data for the $_SERVER variable in the setup/teardown PHP scripts. Should be the same as
 * the values in your phpunit.xml file.
 */
exports.phpServer = {
    HTTP_HOST: 'localhost',
    REQUEST_URI: '/',
    REMOTE_ADDR: '127.0.0.1'
};

/**
 * The path to the PHP executable to execute when setting up & tearing down the database.
 */
exports.php = 'php';

/**
 * The folder in tests/lib that holds mocha.
 */
exports.mocha = 'mocha-3.1.2';

/**
 * The folder in tests/lib that holds chai.
 */
exports.chai = 'chai-1.9.0';

/**
 * Mocha reporters to use (can be multiple delimited by a comma).
 */
if (process.env.TESTOMATIO && process.env.SHOULD_SEND_TO_TESTOMATIO === 'true') {
  exports.reporter = 'mocha-multi-reporters';
  exports.reporterOptions = {
    reporterEnabled: 'spec, @testomatio/reporter/lib/adapter/mocha.js',
    testomatioReporterLibAdapterMochaJsReporterOptions: {
      apiKey: process.env.TESTOMATIO
    }
  };
} else {
  exports.reporter = 'spec';
  exports.reporterOptions = {};
}

/**
 * The directory that stores expected screenshots. Relative to the UI repo's root directory.
 */
exports.expectedScreenshotsDir = ["./expected-screenshots", "./expected-ui-screenshots"];

/**
 * The directory that stores processed screenshots. Relative to the UI repo's root directory.
 */
exports.processedScreenshotsDir = "./processed-ui-screenshots";

/**
 * The directory that stores screenshot diffs. Relative to the UI repo's root directory.
 */
exports.screenshotDiffDir = "./screenshot-diffs";

/**
 * Resolve the browser executable Puppeteer should launch. Prefer an explicit
 * PUPPETEER_EXECUTABLE_PATH, then the Chrome for Testing version pinned in
 * tests/lib/screenshot-testing/.puppeteerrc.cjs (downloaded by `npm ci` there). A system
 * Chrome/Chromium is a last resort (e.g. native linux/arm64, which has no Chrome for Testing
 * build): it renders differently, so UI screenshots generated with it will NOT match CI.
 */
function resolveBrowserExecutablePath() {
    if (process.env.PUPPETEER_EXECUTABLE_PATH) {
        return process.env.PUPPETEER_EXECUTABLE_PATH;
    }

    const fs = require('fs');
    const path = require('path');
    const harnessDir = path.join(__dirname, '..', 'lib', 'screenshot-testing');

    let pinnedConfig;
    try {
        pinnedConfig = require(path.join(harnessDir, '.puppeteerrc.cjs'));
        // Compute the pinned browser's path from the harness config directly: Puppeteer's own
        // executablePath() reads .puppeteerrc.cjs relative to process.cwd(), so it misses the pin
        // when not run from the harness directory (e.g. the JS test runner).
        const puppeteerDir = path.dirname(
            require.resolve('puppeteer/package.json', { paths: [harnessDir] })
        );
        const { computeExecutablePath } = require(
            require.resolve('@puppeteer/browsers', { paths: [puppeteerDir] })
        );
        const pinnedBrowser = computeExecutablePath({
            browser: 'chrome',
            buildId: pinnedConfig.chrome.version,
            cacheDir: pinnedConfig.cacheDirectory,
        });
        if (fs.existsSync(pinnedBrowser)) {
            return pinnedBrowser;
        }
    } catch (e) {
        // fall through to the system browsers below
    }

    const candidates = [
        '/usr/bin/google-chrome-stable',
        '/usr/bin/google-chrome',
        '/usr/bin/chromium',
        '/usr/bin/chromium-browser',
    ];

    const systemBrowser = candidates.find((candidate) => fs.existsSync(candidate));

    if (systemBrowser) {
        const reason = (pinnedConfig && pinnedConfig.chrome.skipDownload)
            ? 'no Chrome for Testing build exists for this platform'
            : 'the pinned Chrome for Testing browser was not found (run "npm ci" in '
                + 'tests/lib/screenshot-testing to download it)';
        console.warn('WARNING: ' + reason + '. Falling back to the system browser at '
            + systemBrowser + ' -- any UI screenshots generated with this browser will NOT match '
            + 'the CI-generated expected ones.');
    }

    return systemBrowser;
}

/**
 * The config object passed to the headless browser used by Puppeteer
 */
const browserConfig = {
    args: ['--no-sandbox', '--ignore-certificate-errors'],
    // Puppeteer 24 defaults protocolTimeout to 180s, which is below the 240s mocha test timeout, so
    // a slow CDP call (e.g. a screenshot or evaluate on a heavy page under CI load) can abort a test
    // with a ProtocolError before mocha's own timeout applies. Raise it so the mocha timeout governs.
    protocolTimeout: 300000
};

const browserExecutablePath = resolveBrowserExecutablePath();
if (browserExecutablePath) {
    browserConfig.executablePath = browserExecutablePath;
}

exports.browserConfig = browserConfig;
