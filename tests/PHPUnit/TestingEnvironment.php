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
            \Piwik\CacheFile::$invalidateOpCacheBeforeRead = true;

            $config->setTestEnvironment();

            $pluginsToLoad = array(
                "CorePluginsAdmin", "CoreAdminHome", "CoreHome", "Proxy", "API", "Widgetize", "Transitions",
                "LanguagesManager", "Actions", "Dashboard", "MultiSites", "Referers", "UserSettings", "Goals",
                "SEO", "UserCountry", "VisitsSummary", "VisitFrequency", "VisitTime", "VisitorInterest",
                "ExampleAPI", "ExamplePlugin", "ExampleRssWidget", "Provider", "Feedback", "Login", "UsersManager",
                "SitesManager", "Installation", "CoreUpdater", "PDFReports", "UserCountryMap", "Live",
                "CustomVariables", "PrivacyManager", "ImageGraph", "DoNotTrack", "Annotations", "MobileMessaging",
                "Overlay", "SegmentEditor", "DevicesDetection", "DBStats",
            );
            $config->Plugins = array('Plugins' => $pluginsToLoad);

            $config->General['session_save_handler'] = 'dbtables'; // to avoid weird session error in travis
        });
        Piwik_AddAction('Request.dispatch', function() {
            \Piwik\Plugins\CoreVisualizations\Visualizations\Cloud::$debugDisableShuffle = true;
            \Piwik\Visualization\Sparkline::$enableSparklineImages = false;
        });
        Piwik_AddAction('AssetManager.getStylesheetFiles', function(&$stylesheets) {
            $stylesheets[] = 'tests/resources/screenshot-override/override.css';
        });
        Piwik_AddAction('AssetManager.getJavaScriptFiles', function(&$jsFiles) {
            $jsFiles[] = 'tests/resources/screenshot-override/jquery.waitforimages.js';
            $jsFiles[] = 'tests/resources/screenshot-override/override.js';
        });
        Piwik_AddAction('Request.dispatch', function () {
            \Piwik\Access::setSingletonInstance(null);
            \Piwik\Access::getInstance();
        });
    }
}