<?php
/**
 * Proxy to index.php, but will use the Test DB
 * Used by tests/PHPUnit/Integration/ImportLogsTest.php and tests/PHPUnit/Integration/UITest.php
 */

use Piwik\Tracker\Cache;

require realpath(dirname(__FILE__)) . "/includes.php";

// Wrapping the request inside ob_start() calls to ensure that the Test
// calling us waits for the full request to process before unblocking
ob_start();

Piwik_TestingEnvironment::addHooks();

\Piwik\Tracker::setTestEnvironment();
Cache::deleteTrackerCache();

// Disable index.php dispatch since we do it manually below
define('PIWIK_ENABLE_DISPATCH', false);
include PIWIK_INCLUDE_PATH . '/index.php';

$controller = \Piwik\FrontController::getInstance();

// Load all plugins that are found so UI tests are really testing real world use case
\Piwik\Config::getInstance()->Plugins['Plugins'] = \Piwik\Plugin\Manager::getInstance()->getAllPluginsNames();

$controller->init();
$controller->dispatch();

ob_flush();

