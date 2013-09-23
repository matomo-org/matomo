<?php
/**
 * Proxy to index.php, but will use the Test DB
 * Used by tests/PHPUnit/Integration/ImportLogsTest.php and tests/PHPUnit/Integration/UITest.php
 */

// make sure the test environment is loaded
use Piwik\Tracker\Cache;

// Wrapping the request inside ob_start() calls to ensure that the Test
// calling us waits for the full request to process before unblocking
ob_start();

define('PIWIK_INCLUDE_PATH', '../../..');
define('PIWIK_USER_PATH', PIWIK_INCLUDE_PATH);
define('PIWIK_PRINT_ERROR_BACKTRACE', true);

require_once PIWIK_INCLUDE_PATH . '/libs/upgradephp/upgrade.php';
require_once PIWIK_INCLUDE_PATH . '/core/Loader.php';
require_once PIWIK_INCLUDE_PATH . '/core/EventDispatcher.php';

require_once realpath(dirname(__FILE__)) . '/../../../core/functions.php';
require_once realpath(dirname(__FILE__)) . "/../../../tests/PHPUnit/TestingEnvironment.php";
Piwik_TestingEnvironment::addHooks();

\Piwik\Tracker::setTestEnvironment();
Cache::deleteTrackerCache();

// Disable index.php dispatch since we do it manually below
define('PIWIK_ENABLE_DISPATCH', false);
include PIWIK_INCLUDE_PATH . '/index.php';

$controller = new \Piwik\FrontController;
$controller->init();
$controller->dispatch();

ob_flush();
