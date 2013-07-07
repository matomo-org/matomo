<?php

// include archive.php, and let 'er rip
$GLOBALS['PIWIK_CONFIG_TEST_ENVIRONMENT'] = true;
$GLOBALS['PIWIK_ACCESS_IS_SUPERUSER'] = true;
require realpath(dirname(__FILE__)) . "/../../../misc/cron/archive.php";

