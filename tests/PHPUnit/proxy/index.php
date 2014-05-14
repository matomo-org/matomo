<?php
/**
 * Proxy to index.php, but will use the Test DB
 * Used by tests/PHPUnit/Integration/ImportLogsTest.php and tests/PHPUnit/Integration/UITest.php
 */

require realpath(dirname(__FILE__)) . "/includes.php";

Piwik_TestingEnvironment::addHooks();

if (empty($_GET['ignoreClearAllViewDataTableParameters'])) {
    \Piwik\ViewDataTable\Manager::clearAllViewDataTableParameters();
}

\Piwik\Profiler::setupProfilerXHProf();

include PIWIK_INCLUDE_PATH . '/index.php';