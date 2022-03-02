<?php

if (!defined('PIWIK_INCLUDE_PATH')) {
    // NOTE: PIWIK_INCLUDE_PATH must end in '/' or some parts of matomo will break
    define('PIWIK_INCLUDE_PATH', realpath(dirname(__FILE__) . '/../../..') . '/');
}

if (!defined('PIWIK_USER_PATH')) {
    define('PIWIK_USER_PATH', PIWIK_INCLUDE_PATH);
}
if (!defined('PIWIK_TEST_MODE')) {
    define('PIWIK_TEST_MODE', true);
}

$GLOBALS['MATOMO_PLUGIN_DIRS'] = array(
    array(
        'pluginsPathAbsolute' => realpath(PIWIK_INCLUDE_PATH . 'tests/resources/custompluginsdir'),
        'webrootDirRelativeToMatomo' => '../../resources/custompluginsdir'
    ),
);

require_once PIWIK_INCLUDE_PATH . '/core/bootstrap.php';

Piwik\SettingsServer::setMaxExecutionTime(0);
