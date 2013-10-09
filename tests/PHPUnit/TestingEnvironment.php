<?php

if (!defined('PIWIK_TEST_MODE')) {
    define('PIWIK_TEST_MODE', true);
}

class Piwik_MockAccess
{
    private $access;

    public function __construct($access)
    {
        $this->access = $access;
        $access->setSuperUser(true);
    }

    public function __call($name, $arguments)
    {
        return call_user_func_array(array($this->access, $name), $arguments);
    }

    public function reloadAccess($auth = null)
    {
        return true;
    }

    public function getLogin()
    {
        return 'superUserLogin';
    }
}

/**
 * Sets the test environment.
 */
class Piwik_TestingEnvironment
{
    public static function addHooks()
    {
        \Piwik\Piwik::addAction('Access.createAccessSingleton', function($access) {
            $access = new Piwik_MockAccess($access);
            \Piwik\Access::setSingletonInstance($access);
        });
        \Piwik\Piwik::addAction('Config.createConfigSingleton', function($config) {
            \Piwik\CacheFile::$invalidateOpCacheBeforeRead = true;

            $config->setTestEnvironment();

            $pluginsToLoad = array(
                "CorePluginsAdmin", "CoreAdminHome", "CoreHome", "Proxy", "API", "Widgetize", "Transitions",
                "LanguagesManager", "Actions", "Dashboard", "MultiSites", "Referrers", "UserSettings", "Goals",
                "SEO", "UserCountry", "VisitsSummary", "VisitFrequency", "VisitTime", "VisitorInterest",
                "ExampleAPI", "ExamplePlugin", "ExampleRssWidget", "Provider", "Feedback", "Login", "UsersManager",
                "SitesManager", "Installation", "CoreUpdater", "ScheduledReports", "UserCountryMap", "Live",
                "CustomVariables", "PrivacyManager", "ImageGraph", "DoNotTrack", "Annotations", "MobileMessaging",
                "Overlay", "SegmentEditor", "DevicesDetection", "DBStats", 'ExampleUI'
            );
            $config->Plugins = array('Plugins' => $pluginsToLoad);

            $config->General['session_save_handler'] = 'dbtables'; // to avoid weird session error in travis
            $config->superuser['email'] = 'hello@example.org';
        });
        \Piwik\Piwik::addAction('Request.dispatch', function() {
            \Piwik\Plugins\CoreVisualizations\Visualizations\Cloud::$debugDisableShuffle = true;
            \Piwik\Visualization\Sparkline::$enableSparklineImages = false;
            \Piwik\Plugins\ExampleUI\API::$disableRandomness = true;
        });
        \Piwik\Piwik::addAction('AssetManager.getStylesheetFiles', function(&$stylesheets) {
            $stylesheets[] = 'tests/resources/screenshot-override/override.css';
        });
        \Piwik\Piwik::addAction('AssetManager.getJavaScriptFiles', function(&$jsFiles) {
            $jsFiles[] = 'tests/resources/screenshot-override/jquery.waitforimages.js';
            $jsFiles[] = 'tests/resources/screenshot-override/override.js';
        });
    }
}