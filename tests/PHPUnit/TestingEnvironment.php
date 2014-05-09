<?php

use Piwik\Common;
use Piwik\Config;
use Piwik\Piwik;

require_once PIWIK_INCLUDE_PATH . "/core/Config.php";

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
    private $behaviorOverrideProperties = array();

    public function __construct()
    {
        $overridePath = PIWIK_INCLUDE_PATH . '/tmp/testingPathOverride.json';
        if (file_exists($overridePath)) {
            $this->behaviorOverrideProperties = json_decode(file_get_contents($overridePath), true);
        }
    }

    public function __get($key)
    {
        return isset($this->behaviorOverrideProperties[$key]) ? $this->behaviorOverrideProperties[$key] : null;
    }

    public function __set($key, $value)
    {
        $this->behaviorOverrideProperties[$key] = $value;
    }

    public function save()
    {
        $overridePath = PIWIK_INCLUDE_PATH . '/tmp/testingPathOverride.json';
        file_put_contents($overridePath, json_encode($this->behaviorOverrideProperties));
    }

    public function delete()
    {
        $this->behaviorOverrideProperties = array();
        $this->save();
    }

    public function logVariables()
    {
        try {
            if (isset($_SERVER['QUERY_STRING'])) {
                \Piwik\Log::verbose("Test Environment Variables for (%s):\n%s", $_SERVER['QUERY_STRING'], print_r($this->behaviorOverrideProperties, true));
            }
        } catch (Exception $ex) {
            // ignore
        }
    }

    public static function addHooks()
    {
        $testingEnvironment = new Piwik_TestingEnvironment();

        if ($testingEnvironment->queryParamOverride) {
            foreach ($testingEnvironment->queryParamOverride as $key => $value) {
                $_GET[$key] = $value;
            }
        }

        if ($testingEnvironment->globalsOverride) {
            foreach ($testingEnvironment->globalsOverride as $key => $value) {
                $GLOBALS[$key] = $value;
            }
        }

        Config::setSingletonInstance(new Config(
            $testingEnvironment->configFileGlobal, $testingEnvironment->configFileLocal, $testingEnvironment->configFileCommon
        ));

        \Piwik\CacheFile::$invalidateOpCacheBeforeRead = true;

        Piwik::addAction('Access.createAccessSingleton', function($access) use ($testingEnvironment) {
            if (!$testingEnvironment->testUseRegularAuth) {
                $access = new Piwik_MockAccess($access);
                \Piwik\Access::setSingletonInstance($access);
            }
        });
        if (!$testingEnvironment->dontUseTestConfig) {
            Piwik::addAction('Config.createConfigSingleton', function(Config $config, &$cache, $local) use ($testingEnvironment) {
                $config->setTestEnvironment($testingEnvironment->configFileLocal, $testingEnvironment->configFileGlobal, $testingEnvironment->configFileCommon);

                if ($testingEnvironment->configFileLocal) {
                    unset($cache['General']);
                    $config->General['session_save_handler'] = 'dbtable';
                }

                $manager = \Piwik\Plugin\Manager::getInstance();
                $pluginsToLoad = $manager->getPluginsToLoadDuringTests();
                $config->Plugins = array('Plugins' => $pluginsToLoad);

                $trackerPluginsToLoad = array_filter($pluginsToLoad, function ($plugin) use ($manager) {
                    return $manager->isTrackerPlugin($manager->loadPlugin($plugin));
                });

                $config->Plugins_Tracker = array('Plugins_Tracker' => $trackerPluginsToLoad);

                $log = $config->log;
                $log['log_writers'] = array('file');
                $config->log = $log;

                $manager->unloadPlugins();

                // TODO: replace this and below w/ configOverride use
                if ($testingEnvironment->tablesPrefix) {
                    $cache['database']['tables_prefix'] = $testingEnvironment->tablesPrefix;
                }

                if ($testingEnvironment->dbName) {
                    $cache['database']['dbname'] = $testingEnvironment->dbName;
                }

                if ($testingEnvironment->configOverride) {
                    $cache = $testingEnvironment->arrayMergeRecursiveDistinct($cache, $testingEnvironment->configOverride);
                }
            });
        }
        Piwik::addAction('Request.dispatch', function() {
            \Piwik\Plugins\CoreVisualizations\Visualizations\Cloud::$debugDisableShuffle = true;
            \Piwik\Visualization\Sparkline::$enableSparklineImages = false;
            \Piwik\Plugins\ExampleUI\API::$disableRandomness = true;
        });
        Piwik::addAction('AssetManager.getStylesheetFiles', function(&$stylesheets) {
            $stylesheets[] = 'tests/resources/screenshot-override/override.css';
        });
        Piwik::addAction('AssetManager.getJavaScriptFiles', function(&$jsFiles) {
            $jsFiles[] = 'tests/resources/screenshot-override/override.js';
        });
        self::addSendMailHook();
        Piwik::addAction('Updater.checkForUpdates', function () {
            try {
                @\Piwik\Filesystem::deleteAllCacheOnUpdate();
            } catch (Exception $ex) {
                // pass
            }
        });

        $testingEnvironment->logVariables();
        $testingEnvironment->executeSetupTestEnvHook();
    }

    public static function addSendMailHook()
    {
        Piwik::addAction('Test.Mail.send', function($mail) {
            $outputFile = PIWIK_INCLUDE_PATH . '/tmp/' . Common::getRequestVar('module', '') . '.' . Common::getRequestVar('action', '') . '.mail.json';

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

    public function arrayMergeRecursiveDistinct(array $array1, array $array2)
    {
        $result = $array1;

        foreach ($array2 as $key => $value) {
            if (is_array($value)) {
                $result[$key] = isset($result[$key]) && is_array($result[$key])
                              ? $this->arrayMergeRecursiveDistinct($result[$key], $value)
                              : $value
                              ;
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * for plugins that need to inject special testing logic
     */
    public function executeSetupTestEnvHook()
    {
        Piwik::postEvent("TestingEnvironment.addHooks", array($this), $pending = true);
    }
}