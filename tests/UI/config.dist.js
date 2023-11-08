/*!
 * Matomo - free/libre analytics platform
 *
 * UI tests config
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * The root Piwik URL to test against.
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
exports.reporter = 'mocha-multi-reporter';
exports.reporterOptions = {
  reporterEnabled: 'spec, @testomatio/reporter/lib/adapter/mocha.js',
  testomatioReporterLibAdapterMochaJsReporterOptions: {
    apiKey: process.env.TESTOMATIO
  }
};

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
