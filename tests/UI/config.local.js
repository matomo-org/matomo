/*!
 * Piwik - free/libre analytics platform
 *
 * UI tests config
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * The root Piwik URL to test against.
 *
 * exports.piwikUrl = "http://localhost/";
 */

/**
 * Data for the $_SERVER variable in the setup/teardown PHP scripts. Should be the same as
 * the values in your phpunit.xml file.
 */
 exports.phpServer = {
    HTTP_HOST: 'apache.piwik',
    REQUEST_URI: '/',
    REMOTE_ADDR: '192.168.33.10'
};


/**
 * The path to the PHP executable to execute when setting up & tearing down the database.
 *
 * exports.php = 'php';
 */

/**
 * The directory that stores expected screenshots. Relative to the UI repo's root directoriy.
 *
 * exports.expectedScreenshotsDir = "./expected-ui-screenshots";
 */

/**
 * The directory that stores processed screenshots. Relative to the UI repo's root directory.
 *
 * exports.processedScreenshotsDir = "./processed-ui-screenshots";
 */

/**
 * The directory that stores screenshot diffs. Relative to the UI repo's root directory.
 *
 * exports.screenshotDiffDir = "./screenshot-diffs";
 */