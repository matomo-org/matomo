<?php
/**
 * This php file is used to unit test Piwik::serverStaticFile()
 * To make a comprehensive test suit for Piwik::serverStaticFile() (ie. being able to test combinations of request
 * headers, being able to test response headers and so on) we need to simulate static file requests in a controlled
 * environment
 * The php code which simulates requests using Piwik::serverStaticFile() is provided in the same file (ie. this one)
 * as the unit testing code for Piwik::serverStaticFile()
 * This decision has a structural impact on the usual unit test file structure
 * serverStaticFile.test.php has been created to avoid making too many modifications to /tests/core/Piwik.test.php
 */
use Piwik\Common;
use Piwik\ProxyHttp;

define('PIWIK_DOCUMENT_ROOT', dirname(__FILE__).'/../../');
if(file_exists(PIWIK_DOCUMENT_ROOT . '/bootstrap.php')) {
    require_once PIWIK_DOCUMENT_ROOT . '/bootstrap.php';
}
if (!defined('PIWIK_INCLUDE_PATH')) {
    define('PIWIK_INCLUDE_PATH', PIWIK_DOCUMENT_ROOT);
}

require_once PIWIK_INCLUDE_PATH . '/core/bootstrap.php';

// This is Piwik logo, the static file used in this test suit
define("TEST_FILE_LOCATION", dirname(__FILE__) . "/lipsum.txt");
define("TEST_FILE_CONTENT_TYPE", "text/plain");

// Defines http request parameters
define("FILE_MODE_REQUEST_VAR", "fileMode");
define("SRV_MODE_REQUEST_VAR", "serverMode");
define("ZLIB_OUTPUT_REQUEST_VAR", "zlibOutput");

// These constants define which action will be performed by the static server.
define("NULL_FILE_SRV_MODE", "nullFile");
define("GHOST_FILE_SRV_MODE", "ghostFile");
define("TEST_FILE_SRV_MODE", "testFile");
define("PARTIAL_TEST_FILE_SRV_MODE", "partialTestFile");
define("WHOLE_TEST_FILE_WITH_RANGE_SRV_MODE", "wholeTestFileWithRange");

define("PARTIAL_BYTE_START", 1204);
define("PARTIAL_BYTE_END", 14724);

/**
 * If the static file server has been requested, the response sent back to the browser will be the content produced by
 * the execution of Piwik:serverStaticFile(). In this case, unit tests won't be executed
 */
// Getting the server mode
$staticFileServerMode = Common::getRequestVar(SRV_MODE_REQUEST_VAR, "");

// Setting zlib output compression as requested
ini_set('zlib.output_compression', Common::getRequestVar(ZLIB_OUTPUT_REQUEST_VAR, '0'));

if ($staticFileServerMode === "") {
    throw new Exception("When this testing file is used as a static file server, the request parameter " .
        SRV_MODE_REQUEST_VAR . " must be provided.");
}

$environment = new \Piwik\Application\Environment(null);
$environment->init();

switch ($staticFileServerMode) {
    // The static file server calls Piwik::serverStaticFile with a null file
    case NULL_FILE_SRV_MODE:

        ProxyHttp::serverStaticFile(null, TEST_FILE_CONTENT_TYPE);
        break;

    // The static file server calls Piwik::serverStaticFile with a non-existing file
    case GHOST_FILE_SRV_MODE:

        ProxyHttp::serverStaticFile(TEST_FILE_LOCATION . ".ghost", TEST_FILE_CONTENT_TYPE);
        break;

    // The static file server calls Piwik::serverStaticFile with the test file
    case TEST_FILE_SRV_MODE:

        ProxyHttp::serverStaticFile(TEST_FILE_LOCATION, TEST_FILE_CONTENT_TYPE);
        break;

    case PARTIAL_TEST_FILE_SRV_MODE:

        ProxyHttp::serverStaticFile(TEST_FILE_LOCATION, TEST_FILE_CONTENT_TYPE, $expireFarFutureDays = 100, PARTIAL_BYTE_START, PARTIAL_BYTE_END);
        break;

    case WHOLE_TEST_FILE_WITH_RANGE_SRV_MODE:

        ProxyHttp::serverStaticFile(TEST_FILE_LOCATION, TEST_FILE_CONTENT_TYPE, $expireFarFutureDays = 100, 0, filesize(TEST_FILE_LOCATION));
        break;
}