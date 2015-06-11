<?php
/**
 * Proxy to index.php, but will use the Test DB
 * Used by tests/PHPUnit/System/ImportLogsTest.php and tests/PHPUnit/System/UITest.php
 */

use Piwik\Application\Environment;
use Piwik\Tests\Framework\TestingEnvironmentManipulator;
use Piwik\Tests\Framework\TestingEnvironmentVariables;

require realpath(dirname(__FILE__)) . "/includes.php";

Environment::setGlobalEnvironmentManipulator(new TestingEnvironmentManipulator(new TestingEnvironmentVariables()));

include PIWIK_INCLUDE_PATH . '/index.php';