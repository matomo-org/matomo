<?php
/**
 * Proxy to index.php, but will use the Test DB
 * Used by tests/PHPUnit/System/ImportLogsTest.php and tests/PHPUnit/System/UITest.php
 */

use Piwik\Application\Environment;
use Piwik\Tests\Framework\TestingEnvironmentManipulator;
use Piwik\Tests\Framework\TestingEnvironmentVariables;

define('PIWIK_ENABLE_DISPATCH', false);

require realpath(dirname(__FILE__)) . "/includes.php";
$testEnvironment = new TestingEnvironmentVariables();
$testEnvironment->configFileLocal = PIWIK_INCLUDE_PATH . "tmp/test.config.ini.php";
$testEnvironment->save();

Environment::setGlobalEnvironmentManipulator(new TestingEnvironmentManipulator($testEnvironment));

include PIWIK_INCLUDE_PATH . '/index.php';