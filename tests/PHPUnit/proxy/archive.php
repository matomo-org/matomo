<?php
define('PIWIK_MODE_ARCHIVE', true);
define('PIWIK_ARCHIVE_NO_TRUNCATE', true);

require "./includes.php";

Piwik_TestingEnvironment::addHooks();

// include archive.php, and let 'er rip
require_once PIWIK_INCLUDE_PATH . "/misc/cron/archive.php";
