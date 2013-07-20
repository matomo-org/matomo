<?php

require_once realpath(dirname(__FILE__)) . "/../../core/EventDispatcher.php";

/**
 * Sets the test environment.
 */
class Piwik_TestingEnvironment
{
    public static function addHooks()
    {
        if (!defined('PIWIK_TEST_MODE')) {
            define('PIWIK_TEST_MODE', true);
        }

        Piwik_AddAction('Access.createAccessSingleton', function($access) {
            $access->setSuperUser(true);
        });
        Piwik_AddAction('Access.loadingSuperUserAccess', function(&$idSitesByAccess, &$login) {
            $login = 'superUserLogin';
        });
        Piwik_AddAction('Config.createConfigSingleton', function($config) {
            $config->setTestEnvironment();
        });
    }
}