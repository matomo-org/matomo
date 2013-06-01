<?php
// Note to devs: In Phpstorm I had to manually set these here as PHPUnit is not init properly.
// Uncomment and set manually the path to Piwik if you get the WARNING message in your IDE.
//$_SERVER['REQUEST_URI'] = '/piwik-master/index.php';
//$_SERVER['HTTP_HOST'] = 'localhost';

if (!defined("PIWIK_PATH_TEST_TO_ROOT")) {
    define('PIWIK_PATH_TEST_TO_ROOT', realpath(dirname(__FILE__) . '/../..'));
}
if (!defined('PIWIK_USER_PATH')) {
    define('PIWIK_USER_PATH', PIWIK_PATH_TEST_TO_ROOT);
}
if (!defined('PIWIK_INCLUDE_PATH')) {
    define('PIWIK_INCLUDE_PATH', PIWIK_PATH_TEST_TO_ROOT);
}
if (!defined('PIWIK_INCLUDE_SEARCH_PATH')) {
    define('PIWIK_INCLUDE_SEARCH_PATH', get_include_path()
        . PATH_SEPARATOR . PIWIK_INCLUDE_PATH . '/core'
        . PATH_SEPARATOR . PIWIK_INCLUDE_PATH . '/libs'
        . PATH_SEPARATOR . PIWIK_INCLUDE_PATH . '/plugins');
}
@ini_set('include_path', PIWIK_INCLUDE_SEARCH_PATH);
@set_include_path(PIWIK_INCLUDE_SEARCH_PATH);
@ini_set('memory_limit', -1);
error_reporting(E_ALL | E_NOTICE);
@date_default_timezone_set('UTC');

$useXhprof = false;
if ($useXhprof) {
    require_once PIWIK_INCLUDE_PATH . '/tests/lib/xhprof-0.9.2/xhprof_lib/utils/xhprof_runs.php';
    
    if (!isset($profilerNamespace)) {
        $firstLineOfGitHead = file(PIWIK_INCLUDE_PATH . '/.git/HEAD');
        $firstLineOfGitHead = $firstLineOfGitHead[0];
        
        $parts = explode("/", $firstLineOfGitHead);
        $currentGitBranch = trim($parts[2]);
        
        $profilerNamespace = "piwik.$currentGitBranch";
    }
    
    xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);
    
    register_shutdown_function(function () use($profilerNamespace) {
        $xhprofData = xhprof_disable();

        $xhprofRuns = new \XHProfRuns_Default();
        $runId = $xhprofRuns->save_run($xhprofData, $profilerNamespace);
        
        echo "\n\nPROFILER RUN URL: /tests/lib/xhprof-0.9.2/xhprof_html/?source=$profilerNamespace&run=$runId\n\n";
    });
}

require_once PIWIK_INCLUDE_PATH . '/libs/upgradephp/upgrade.php';
require_once PIWIK_INCLUDE_PATH . '/core/testMinimumPhpVersion.php';
require_once PIWIK_INCLUDE_PATH . '/core/Loader.php';
require_once PIWIK_INCLUDE_PATH . '/core/FrontController.php';
require_once PIWIK_INCLUDE_PATH . '/libs/spyc.php';
require_once PIWIK_INCLUDE_PATH . '/tests/PHPUnit/DatabaseTestCase.php';
require_once PIWIK_INCLUDE_PATH . '/tests/PHPUnit/IntegrationTestCase.php';
require_once PIWIK_INCLUDE_PATH . '/tests/PHPUnit/FakeAccess.php';
require_once PIWIK_INCLUDE_PATH . '/tests/PHPUnit/MockPiwikOption.php';
require_once PIWIK_INCLUDE_PATH . '/tests/PHPUnit/MockEventDispatcher.php';

// required to build code coverage for uncovered files
require_once PIWIK_INCLUDE_PATH . '/plugins/SecurityInfo/PhpSecInfo/PhpSecInfo.php';

// require test fixtures
require_once PIWIK_INCLUDE_PATH . '/tests/PHPUnit/BaseFixture.php';
foreach (glob(PIWIK_INCLUDE_PATH . '/tests/PHPUnit/Fixtures/*.php') as $file) {
    require_once $file;
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
2) Make these Piwik files available on the webserver, at eg. http://localhost/dev/piwik/ - Piwik does need to be installed to run tests, but this URL must work.
3) Copy phpunit.xml.dist to phpunit.xml
4) Edit in phpunit.xml the @REQUEST_URI@ and replace with the webserver path to Piwik, eg. '/dev/piwik/'

Try again.
-> If you still get this message, you can work around it by specifying Host + Request_Uri at the top of this file tests/PHPUnit/bootstrap.php. <-";
        exit(1);
    }

    // Now testing if the webserver is running
    $piwikServerUrl = Test_Piwik_BaseFixture::getRootUrl();
    try {
        $fetched = Piwik_Http::sendHttpRequest($piwikServerUrl, $timeout = 3);
    } catch (Exception $e) {
        $fetched = "ERROR fetching: " . $e->getMessage();
    }
    $expectedString = 'plugins/CoreHome/templates/images/favicon.ico';

    if (strpos($fetched, $expectedString) === false) {
        echo "\nPiwik should be running at: " . $piwikServerUrl
            . "\nbut this URL returned an unexpected response: '"
            . substr($fetched, 0, 300) . "...'\n\n";
        exit;
    }
}
