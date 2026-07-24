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
 * Resolve the browser executable Puppeteer should launch. Puppeteer 24 otherwise looks for the
 * specific Chrome build it pins (in ~/.cache/puppeteer), which is not provisioned in CI -- the test
 * runner installs a system google-chrome-stable instead. Prefer an explicit
 * PUPPETEER_EXECUTABLE_PATH, then a system Chrome/Chromium, and finally fall back to Puppeteer's own
 * downloaded browser (used on fresh local setups that have neither installed).
 */
function resolveBrowserExecutablePath() {
    if (process.env.PUPPETEER_EXECUTABLE_PATH) {
        return process.env.PUPPETEER_EXECUTABLE_PATH;
    }

    const fs = require('fs');
    const candidates = [
        '/usr/bin/google-chrome-stable',
        '/usr/bin/google-chrome',
        '/usr/bin/chromium',
        '/usr/bin/chromium-browser',
    ];

    return candidates.find((candidate) => fs.existsSync(candidate));
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
