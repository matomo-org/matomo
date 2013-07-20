<?php

// make sure the test environment is loaded
require realpath(dirname(__FILE__)) . "/../../../tests/PHPUnit/TestingEnvironment.php";
Piwik_TestingEnvironment::addHooks();

// include archive.php, and let 'er rip
require realpath(dirname(__FILE__)) . "/../../../misc/cron/archive.php";