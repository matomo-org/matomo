<?php

if (!defined('PIWIK_TEST_MODE')) {
    define('PIWIK_TEST_MODE', true);
}

/**
 * Sets the test environment.
 */
class Piwik_TestingEnvironment
{
    public static function addHooks()
    {
        Piwik_AddAction('Access.createAccessSingleton', function($access) {
            $access->setSuperUser(true);
        });
        Piwik_AddAction('Access.loadingSuperUserAccess', function(&$idSitesByAccess, &$login) {
            $login = 'superUserLogin';
        });
        Piwik_AddAction('Config.createConfigSingleton', function($config) {
            $config->setTestEnvironment();
        });
        Piwik_AddAction('FrontController.dispatch', function() {
            \Piwik\Plugins\CoreVisualizations\Visualizations\Cloud::$debugDisableShuffle = true;
        });
        Piwik_AddAction('AssetManager.getCssFiles', function(&$cssFiles) {
            $cssFiles[] = 'tests/resources/screenshot-override/override.css';
        });
        Piwik_AddAction('AssetManager.getJsFiles', function(&$jsFiles) {
            $jsFiles[] = 'tests/resources/screenshot-override/jquery.waitforimages.js';
            $jsFiles[] = 'tests/resources/screenshot-override/override.js';
        });
    }
}