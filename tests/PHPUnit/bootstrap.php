<?php

use Piwik\Container\StaticContainer;
use Piwik\Http;
use Piwik\Tests\Framework\Fixture;
use Piwik\Intl\Locale;

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

require_once PIWIK_INCLUDE_PATH . '/core/bootstrap.php';

require_once PIWIK_INCLUDE_PATH . '/libs/PiwikTracker/PiwikTracker.php';
require_once PIWIK_INCLUDE_PATH . '/tests/PHPUnit/TestingEnvironment.php';

if (getenv('PIWIK_USE_XHPROF') == 1) {
    \Piwik\Profiler::setupProfilerXHProf();
}

// setup container for tests
StaticContainer::setEnvironment('test');

// require test fixtures
$fixturesToLoad = array(
    '/tests/UI/Fixtures/*.php',
    '/plugins/*/tests/Fixtures/*.php',
    '/plugins/*/Test/Fixtures/*.php',
);
foreach($fixturesToLoad as $fixturePath) {
    foreach (glob(PIWIK_INCLUDE_PATH . $fixturePath) as $file) {
        require_once $file;
    }
}

Locale::setDefaultLocale();

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

    $url = Fixture::getRootUrl() . 'tests/PHPUnit/proxy/index.php?module=TestRunner&action=check';
    $response = Http::sendHttpRequestBy('curl', $url, 5);

    if ($response === 'OK'
        // The SQL error is for Travis...
        || strpos($response, 'Table &#039;piwik_tests.option&#039; doesn&#039;t exist') !== false
        || strpos($response, 'Table &#039;piwik_tests.piwiktests_option&#039; doesn&#039;t exist') !== false
        || strpos($response, 'Unknown database &#039;piwik_tests&#039;') !== false
    ) {

        return;
    }

    throw new Exception(sprintf(
        "Piwik should be running at %s but this URL returned an unexpected response: '%s'",
        $url,
        $response
    ));
}
