<?php
/**
 * Proxy to index.php, but will use the Test DB
 * Used by tests/PHPUnit/System/ImportLogsTest.php and tests/PHPUnit/System/UITest.php
 */

require realpath(dirname(__FILE__)) . "/includes.php";

\Piwik\Tests\Framework\TestingEnvironment::addHooks();

include PIWIK_INCLUDE_PATH . '/index.php';