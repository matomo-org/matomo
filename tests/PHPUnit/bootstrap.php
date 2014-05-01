<?php
define('PIWIK_TEST_MODE', true);
define('PIWIK_PRINT_ERROR_BACKTRACE', false);

if (!defined("PIWIK_PATH_TEST_TO_ROOT")) {
    define('PIWIK_PATH_TEST_TO_ROOT', realpath(dirname(__FILE__) . '/../..'));
}
if (!defined('PIWIK_DOCUMENT_ROOT')) {
    define('PIWIK_DOCUMENT_ROOT', PIWIK_PATH_TEST_TO_ROOT);
}
if (!defined('PIWIK_USER_PATH')) {
    define('PIWIK_USER_PATH', PIWIK_PATH_TEST_TO_ROOT);
}
if (!defined('PIWIK_INCLUDE_PATH')) {
    define('PIWIK_INCLUDE_PATH', PIWIK_PATH_TEST_TO_ROOT);
}
if (!defined('PIWIK_INCLUDE_SEARCH_PATH')) {
    define('PIWIK_INCLUDE_SEARCH_PATH', get_include_path()
        . PATH_SEPARATOR . PIWIK_INCLUDE_PATH . '/vendor/bin'
        . PATH_SEPARATOR . PIWIK_INCLUDE_PATH . '/core'
        . PATH_SEPARATOR . PIWIK_INCLUDE_PATH . '/libs'
        . PATH_SEPARATOR . PIWIK_INCLUDE_PATH . '/plugins');
}
@ini_set('include_path', PIWIK_INCLUDE_SEARCH_PATH);
@set_include_path(PIWIK_INCLUDE_SEARCH_PATH);
@ini_set('memory_limit', -1);
error_reporting(E_ALL | E_NOTICE);
@date_default_timezone_set('UTC');


require_once PIWIK_INCLUDE_PATH . '/libs/upgradephp/upgrade.php';
require_once PIWIK_INCLUDE_PATH . '/core/testMinimumPhpVersion.php';
require_once PIWIK_INCLUDE_PATH . '/core/Loader.php';
require_once PIWIK_INCLUDE_PATH . '/core/FrontController.php';
require_once PIWIK_INCLUDE_PATH . '/tests/PHPUnit/DatabaseTestCase.php';
require_once PIWIK_INCLUDE_PATH . '/tests/PHPUnit/IntegrationTestCase.php';
require_once PIWIK_INCLUDE_PATH . '/tests/PHPUnit/FakeAccess.php';
require_once PIWIK_INCLUDE_PATH . '/tests/PHPUnit/MockPiwikOption.php';
require_once PIWIK_INCLUDE_PATH . '/tests/PHPUnit/TestingEnvironment.php';
require_once file_exists(PIWIK_INCLUDE_PATH . '/vendor/autoload.php')
    ? PIWIK_INCLUDE_PATH . '/vendor/autoload.php' // Piwik is the main project
    : PIWIK_INCLUDE_PATH . '/../../autoload.php'; // Piwik is installed as a dependency

\Piwik\Profiler::setupProfilerXHProf( $mainRun = true );

// require test fixtures
require_once PIWIK_INCLUDE_PATH . '/tests/PHPUnit/Fixture.php';

$fixturesToLoad = array(
    '/tests/PHPUnit/Fixtures/*.php',
    '/tests/PHPUnit/UI/Fixtures/*.php',
    '/plugins/*/tests/Fixtures/*.php',
    '/plugins/*/Test/Fixtures/*.php',
);
foreach($fixturesToLoad as $fixturePath) {
    foreach (glob(PIWIK_INCLUDE_PATH . $fixturePath) as $file) {
        require_once $file;
    }
}

// General requirement checks & help: a webserver must be running for tests to work!
checkPiwikSetupForTests();

function checkPiwikSetupForTests()
{
    if (empty($_SERVER['REQUEST_URI'])
        || $_SERVER['REQUEST_URI'] == '@REQUEST_URI@'
    ) {
        echo "WARNING: for tests to pass, you must first:
1) Install webserver on localhost, eg. apache
2) Make these Piwik files available on the webserver, at eg. http://localhost/dev/piwik/
3) Install Piwik by going through the installation process
4) Copy phpunit.xml.dist to phpunit.xml
5) Edit in phpunit.xml the @REQUEST_URI@ and replace with the webserver path to Piwik, eg. '/dev/piwik/'

Try again.
-> If you still get this message, you can work around it by specifying Host + Request_Uri at the top of this file tests/PHPUnit/bootstrap.php. <-";
        exit(1);
    }
    $baseUrl = Fixture::getRootUrl();

    \Piwik\SettingsPiwik::checkPiwikServerWorking($baseUrl, $acceptInvalidSSLCertificates = true);
}
