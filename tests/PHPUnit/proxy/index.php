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


\Piwik\Profiler::setupProfilerXHProf();


// Disable index.php dispatch since we do it manually below
define('PIWIK_ENABLE_DISPATCH', false);
include PIWIK_INCLUDE_PATH . '/index.php';

$controller = \Piwik\FrontController::getInstance();
/**
 * @return bool
 */
function loadAllPluginsButOneTheme()
{
// Load all plugins that are found so UI tests are really testing real world use case
    $pluginsToEnable = \Piwik\Plugin\Manager::getInstance()->getAllPluginsNames();

    $themesNotToEnable = array('ExampleTheme', 'LeftMenu', 'PleineLune');

    $enableMorpheus = !empty($_REQUEST['morpheus']);
    if (!$enableMorpheus) {
        $themesNotToEnable[] = 'Morpheus';
    }

    $pluginsToEnable = array_diff($pluginsToEnable, $themesNotToEnable);
    \Piwik\Config::getInstance()->Plugins['Plugins'] = $pluginsToEnable;
    return $enableMorpheus;
}

$enableMorpheus = loadAllPluginsButOneTheme();

$controller->init();
\Piwik\Filesystem::deleteAllCacheOnUpdate();

$response = $controller->dispatch();

if($enableMorpheus) {
    $replace = "action=getCss";
    $response = str_replace($replace, $replace . "&morpheus=1", $response);
}

if (!is_null($response)) {
    echo $response;
}

ob_flush();

