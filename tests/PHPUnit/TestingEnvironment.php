<?php

use Piwik\Piwik;
use Piwik\Config;
use Piwik\Option;
use Piwik\Common;
use Piwik\Session\SessionNamespace;

if (!defined('PIWIK_TEST_MODE')) {
    define('PIWIK_TEST_MODE', true);
}

class Piwik_MockAccess
{
    private $access;

    public function __construct($access)
    {
        $this->access = $access;
        $access->setSuperUserAccess(true);
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
        Piwik::addAction('Access.createAccessSingleton', function($access) {
            if (empty(Option::get('Tests.testUseRegularAuth'))) {
                $access = new Piwik_MockAccess($access);
                \Piwik\Access::setSingletonInstance($access);
            }
        });
        Piwik::addAction('Config.createConfigSingleton', function($config) {
            \Piwik\CacheFile::$invalidateOpCacheBeforeRead = true;

            $config->setTestEnvironment();

            $pluginsToLoad = \Piwik\Plugin\Manager::getInstance()->getPluginsToLoadDuringTests();
            $config->Plugins = array('Plugins' => $pluginsToLoad);

            $trackerPluginsToLoad = array(
                'Provider', 'Goals', 'PrivacyManager', 'UserCountry', 'DevicesDetection'
            );
            $config->Plugins_Tracker = array('Plugins_Tracker' => $trackerPluginsToLoad);
            $config->log['log_writers'] = array('file');
        });
        Piwik::addAction('Request.dispatch', function() {
            \Piwik\Plugins\CoreVisualizations\Visualizations\Cloud::$debugDisableShuffle = true;
            \Piwik\Visualization\Sparkline::$enableSparklineImages = false;
            \Piwik\Plugins\ExampleUI\API::$disableRandomness = true;
        });
        Piwik::addAction('AssetManager.getStylesheetFiles', function(&$stylesheets) {
            $stylesheets[] = 'tests/resources/screenshot-override/override.css';
        });
        Piwik::addAction('AssetManager.getJavaScriptFiles', function(&$jsFiles) {
            $jsFiles[] = 'tests/resources/screenshot-override/jquery.waitforimages.js';
            $jsFiles[] = 'tests/resources/screenshot-override/override.js';
        });
        Piwik::addAction('Test.Mail.send', function($mail) {
            $outputFile = PIWIK_INCLUDE_PATH . 'tmp/' . Common::getRequestVar('module') . '.' . Common::getRequestVar('action') . '.mail.json';

            $outputContent = str_replace("=\n", "", $mail->getBodyText($textOnly = true));
            $outputContent = str_replace("=0A", "\n", $outputContent);
            $outputContent = str_replace("=3D", "=", $outputContent);

            $outputContents = array(
                'from' => $mail->getFrom(),
                'to' => $mail->getRecipients(),
                'subject' => $mail->getSubject(),
                'contents' => $outputContent
            );
            
            file_put_contents($outputFile, Common::json_encode($outputContents));
        });
    }
}