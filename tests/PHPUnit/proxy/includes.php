<?php

if (!defined('PIWIK_INCLUDE_PATH')) {
    define('PIWIK_INCLUDE_PATH', realpath(dirname(__FILE__) . '/../../..'));
}

if (!defined('PIWIK_USER_PATH')) {
    define('PIWIK_USER_PATH', PIWIK_INCLUDE_PATH);
}
if (!defined('PIWIK_TEST_MODE')) {
    define('PIWIK_TEST_MODE', true);
}

// These proxies bootstrap Matomo against the test database, so over HTTP they
// are only served once fixture setup has marked this instance. CLI usage (test
// runner, console) is always allowed. Marker written by Fixture::performSetUp().
if (PHP_SAPI !== 'cli' && !file_exists(PIWIK_INCLUDE_PATH . '/tmp/http-test-proxies-enabled')) {
    http_response_code(403);
    exit("Matomo test proxies are disabled on this instance.\n");
}

$GLOBALS['MATOMO_PLUGIN_DIRS'] = array(
    array(
        'pluginsPathAbsolute' => realpath(PIWIK_INCLUDE_PATH . '/tests/resources/custompluginsdir'),
        'webrootDirRelativeToMatomo' => '../../resources/custompluginsdir'
    ),
);

require_once PIWIK_INCLUDE_PATH . '/core/bootstrap.php';

Piwik\SettingsServer::setMaxExecutionTime(0);
