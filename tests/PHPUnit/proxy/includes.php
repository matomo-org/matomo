<?php

// Good old test proxy endpoints have some commons

define('PIWIK_INCLUDE_PATH', realpath(dirname(__FILE__)) . '/../../../');
define('PIWIK_USER_PATH', PIWIK_INCLUDE_PATH);
define('PIWIK_PRINT_ERROR_BACKTRACE', true);

require_once PIWIK_INCLUDE_PATH . '/core/Loader.php';
require_once PIWIK_INCLUDE_PATH . '/core/EventDispatcher.php';
require_once PIWIK_INCLUDE_PATH . '/core/Piwik.php';
require_once PIWIK_INCLUDE_PATH . '/libs/upgradephp/upgrade.php';
require_once PIWIK_INCLUDE_PATH . '/tests/PHPUnit/TestingEnvironment.php';

