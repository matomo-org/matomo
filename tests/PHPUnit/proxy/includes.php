<?php

// Good old test proxy endpoints have some commons

if (!defined('PIWIK_INCLUDE_PATH')) {
    define('PIWIK_INCLUDE_PATH', realpath(dirname(__FILE__)) . '/../../../');
}
if (!defined('PIWIK_USER_PATH')) {
    define('PIWIK_USER_PATH', PIWIK_INCLUDE_PATH);
}

require_once PIWIK_INCLUDE_PATH . '/core/Loader.php';
require_once PIWIK_INCLUDE_PATH . '/core/EventDispatcher.php';
require_once PIWIK_INCLUDE_PATH . '/core/Piwik.php';
require_once PIWIK_INCLUDE_PATH . '/libs/upgradephp/upgrade.php';
require_once PIWIK_INCLUDE_PATH . '/tests/PHPUnit/TestingEnvironment.php';

\Piwik\SettingsServer::setMaxExecutionTime(0);

// Make sure Data processed in cron core:archive command is not being purged instantly (useful for: Integration/ArchiveCronTest)
if(\Piwik\SettingsServer::isArchivePhpTriggered()) {
    \Piwik\ArchiveProcessor\Rules::$purgeDisabledByTests = true;
}