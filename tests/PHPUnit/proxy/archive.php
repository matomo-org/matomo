<?php
define('PIWIK_MODE_ARCHIVE', true);
define('PIWIK_ARCHIVE_NO_TRUNCATE', true);

// make sure the test environment is loaded
require_once realpath(dirname(__FILE__)) . '/../../../core/EventDispatcher.php';
require_once realpath(dirname(__FILE__)) . "/../../../core/functions.php";
require_once realpath(dirname(__FILE__)) . "/../../../tests/PHPUnit/TestingEnvironment.php";
Piwik_TestingEnvironment::addHooks();

// include archive.php, and let 'er rip
require_once realpath(dirname(__FILE__)) . "/../../../misc/cron/archive.php";