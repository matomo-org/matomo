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

require_once PIWIK_INCLUDE_PATH . '/core/Loader.php';

\Piwik\Loader::init();

require_once PIWIK_INCLUDE_PATH . '/libs/upgradephp/upgrade.php';
require_once PIWIK_INCLUDE_PATH . '/core/testMinimumPhpVersion.php';
require_once PIWIK_INCLUDE_PATH . '/tests/PHPUnit/DatabaseTestCase.php';
require_once PIWIK_INCLUDE_PATH . '/tests/PHPUnit/IntegrationTestCase.php';
require_once PIWIK_INCLUDE_PATH . '/tests/PHPUnit/BenchmarkTestCase.php';
require_once PIWIK_INCLUDE_PATH . '/tests/PHPUnit/FakeAccess.php';
require_once PIWIK_INCLUDE_PATH . '/tests/PHPUnit/TestingEnvironment.php';

if (getenv('PIWIK_USE_XHPROF') == 1) {
    \Piwik\Profiler::setupProfilerXHProf();
}

// require test fixtures
$fixturesToLoad = array(
    '/tests/PHPUnit/UI/Fixtures/*.php',
    '/plugins/*/tests/Fixtures/*.php',
    '/plugins/*/Test/Fixtures/*.php',
);
foreach($fixturesToLoad as $fixturePath) {
    foreach (glob(PIWIK_INCLUDE_PATH . $fixturePath) as $file) {
        require_once $file;
    }
}

function prepareServerVariables()
{
    \Piwik\Config::getInstance()->init();
    $testConfig = \Piwik\Config::getInstance()->tests;

    if ('@REQUEST_URI@' === $testConfig['request_uri']) {
        // config not done yet, if Piwik is installed we can automatically configure request_uri and http_host
        $url = \Piwik\SettingsPiwik::getPiwikUrl();

        if (!empty($url)) {
            $parsedUrl = parse_url($url);
            $testConfig['request_uri'] = $parsedUrl['path'];
            $testConfig['http_host']   = $parsedUrl['host'];
            \Piwik\Config::getInstance()->tests = $testConfig;
            \Piwik\Config::getInstance()->forceSave();
        }
    }

    $_SERVER['HTTP_HOST']   = $testConfig['http_host'];
    $_SERVER['REQUEST_URI'] = $testConfig['request_uri'];
    $_SERVER['REMOTE_ADDR'] = $testConfig['remote_addr'];
}

prepareServerVariables();

// General requirement checks & help: a webserver must be running for tests to work if not running UnitTests!
if (empty($_SERVER['argv']) || !in_array('UnitTests', $_SERVER['argv'])) {
    checkPiwikSetupForTests();
}

function checkPiwikSetupForTests()
{
    if (empty($_SERVER['REQUEST_URI'])
        || $_SERVER['REQUEST_URI'] == '@REQUEST_URI@'
    ) {
        echo "WARNING: for tests to pass, you must first:
1) Install webserver on localhost, eg. apache
2) Make these Piwik files available on the webserver, at eg. http://localhost/dev/piwik/
3) Install Piwik by going through the installation process
4) Configure tests section if needed in config/config.ini.php:
[tests]
http_host   = \"localhost\"
request_uri = \"@REQUEST_URI@\"
remote_addr = \"127.0.0.1\"

Try again.";
        exit(1);
    }
    $baseUrl = \Piwik\Tests\Framework\Fixture::getRootUrl();

    \Piwik\SettingsPiwik::checkPiwikServerWorking($baseUrl, $acceptInvalidSSLCertificates = true);
}
