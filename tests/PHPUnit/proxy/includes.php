<?php

if (!defined('PIWIK_INCLUDE_PATH')) {
    define('PIWIK_INCLUDE_PATH', realpath(dirname(__FILE__)) . '/../../../');
}
if (!defined('PIWIK_USER_PATH')) {
    define('PIWIK_USER_PATH', PIWIK_INCLUDE_PATH);
}

require_once PIWIK_INCLUDE_PATH . '/core/bootstrap.php';

Piwik\SettingsServer::setMaxExecutionTime(0);
