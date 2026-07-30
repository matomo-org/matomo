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

function warnAboutFallbackBrowser(reason, systemBrowser) {
    const fallback = systemBrowser
        ? 'the system browser at ' + systemBrowser
        : "Puppeteer's own browser resolution";

    console.warn('WARNING: ' + reason + '. Falling back to ' + fallback + ' -- any UI screenshots '
        + 'generated with it will NOT match the CI-generated expected ones.');
}

/**
 * Resolve the browser executable Puppeteer should launch. Prefer an explicit
 * PUPPETEER_EXECUTABLE_PATH, then the Chrome for Testing version pinned in
 * tests/lib/screenshot-testing/.puppeteerrc.cjs (see `npm run install-browser` there). A system
 * Chrome/Chromium is a last resort: it renders differently, so UI screenshots generated with it
 * will NOT match CI. That is expected on platforms without a Chrome for Testing build (native
 * linux/arm64) and a setup problem everywhere else, which fails hard on CI.
 */
function resolveBrowserExecutablePath() {
    if (process.env.PUPPETEER_EXECUTABLE_PATH) {
        return process.env.PUPPETEER_EXECUTABLE_PATH;
    }

    const fs = require('fs');
    const path = require('path');
    const harnessDir = path.join(__dirname, '..', 'lib', 'screenshot-testing');

    let pinnedConfig;
    let pinnedError;
    try {
        pinnedConfig = require(path.join(harnessDir, '.puppeteerrc.cjs'));
    } catch (e) {
        pinnedError = e;
    }

    // Platforms the pin skips have no Chrome for Testing build to launch. Do not even look for one
    // there: @puppeteer/browsers maps linux/arm64 onto the linux64 download, so a browser found in
    // the cache would be an x64 binary that cannot run. An unreadable config is a setup problem
    // rather than an unsupported platform, so it must not take this branch.
    const platformSkipsPinnedBuild = !!pinnedConfig && !!pinnedConfig.chrome
        && !!pinnedConfig.chrome.skipDownload;

    if (pinnedConfig && !platformSkipsPinnedBuild) {
        try {
            // Compute the pinned browser's path from the harness config directly: Puppeteer's own
            // executablePath() reads .puppeteerrc.cjs relative to process.cwd(), so it misses the
            // pin when not run from the harness directory (e.g. the JS test runner).
            // @puppeteer/browsers is deliberately resolved through Puppeteer's own tree rather than
            // declared as a dependency here, so it always matches the version Puppeteer pins.
            const puppeteerDir = path.dirname(
                require.resolve('puppeteer/package.json', { paths: [harnessDir] })
            );
            const { computeExecutablePath } = require(
                require.resolve('@puppeteer/browsers', { paths: [puppeteerDir] })
            );
            // Apply the same environment overrides Puppeteer applies when it downloads the browser,
            // otherwise the browser gets downloaded to one location and looked for in another.
            const pinnedBrowser = computeExecutablePath({
                browser: 'chrome',
                buildId: process.env.PUPPETEER_CHROME_VERSION || pinnedConfig.chrome.version,
                cacheDir: process.env.PUPPETEER_CACHE_DIR || pinnedConfig.cacheDirectory,
            });
            if (fs.existsSync(pinnedBrowser)) {
                return pinnedBrowser;
            }
        } catch (e) {
            pinnedError = e;
        }
    }

    const candidates = [
        '/usr/bin/google-chrome-stable',
        '/usr/bin/google-chrome',
        '/usr/bin/chromium',
        '/usr/bin/chromium-browser',
    ];

    const systemBrowser = candidates.find((candidate) => fs.existsSync(candidate));

    if (platformSkipsPinnedBuild) {
        warnAboutFallbackBrowser('no Chrome for Testing build exists for this platform',
            systemBrowser);

        return systemBrowser;
    }

    // Anywhere else a missing pin is a setup problem. Warn locally so a developer can still work,
    // but fail on CI: comparing screenshots rendered by an unpinned browser is worse than no run.
    const reason = 'the pinned Chrome for Testing browser was not found'
        + (pinnedError ? ' (' + pinnedError.message + ')' : '')
        + '. Run "npm run install-browser" in tests/lib/screenshot-testing to download it';

    if (process.env.CI) {
        throw new Error(reason);
    }

    warnAboutFallbackBrowser(reason, systemBrowser);

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
