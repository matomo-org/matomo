<?php
use Piwik\Application\Environment;
use Piwik\Tests\Framework\TestingEnvironmentManipulator;
use Piwik\Tests\Framework\TestingEnvironmentVariables;

define('PIWIK_ARCHIVE_NO_TRUNCATE', true);

require realpath(dirname(__FILE__)) . "/includes.php";

Environment::setGlobalEnvironmentManipulator(new TestingEnvironmentManipulator(new TestingEnvironmentVariables()));

// include archive.php, and let 'er rip
require_once PIWIK_INCLUDE_PATH . "/misc/cron/archive.php";
