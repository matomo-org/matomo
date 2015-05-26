<?php
define('PIWIK_ARCHIVE_NO_TRUNCATE', true);

require realpath(dirname(__FILE__)) . "/includes.php";

\Piwik\Tests\Framework\TestingEnvironment::addHooks();

// include archive.php, and let 'er rip
require_once PIWIK_INCLUDE_PATH . "/misc/cron/archive.php";
