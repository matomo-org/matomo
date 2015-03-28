<?php

use Piwik\Common;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Piwik;
use Piwik\Option;
use Piwik\Plugin\Manager as PluginManager;
use Piwik\DbHelper;
use Piwik\Tests\Framework\Fixture;

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

    public function __isset($name)
    {
        return isset($this->behaviorOverrideProperties[$name]);
    }

    public function save()
    {
        @mkdir(PIWIK_INCLUDE_PATH . '/tmp');

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
            if (isset($_SERVER['QUERY_STRING'])
                && !$this->dontUseTestConfig
            ) {
                @\Piwik\Log::debug("Test Environment Variables for (%s):\n%s", $_SERVER['QUERY_STRING'], print_r($this->behaviorOverrideProperties, true));
            }
        } catch (Exception $ex) {
            // ignore
        }
    }

    public function getCoreAndSupportedPlugins()
    {
        $disabledPlugins = PluginManager::getInstance()->getCorePluginsDisabledByDefault();
        $disabledPlugins[] = 'LoginHttpAuth';
        $disabledPlugins[] = 'ExampleVisualization';

        $disabledPlugins = array_diff($disabledPlugins, array(
            'DBStats', 'ExampleUI', 'ExampleCommand', 'ExampleSettingsPlugin'
        ));

        $plugins = array_filter(PluginManager::getInstance()->readPluginsDirectory(), function ($pluginName) use ($disabledPlugins) {
            if (in_array($pluginName, $disabledPlugins)) {
                return false;
            }

            return PluginManager::getInstance()->isPluginBundledWithCore($pluginName)
                || PluginManager::getInstance()->isPluginOfficialAndNotBundledWithCore($pluginName);
        });

        sort($plugins);

        return $plugins;
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

        if ($testingEnvironment->hostOverride) {
            \Piwik\Url::setHost($testingEnvironment->hostOverride);
        }

        if ($testingEnvironment->useXhprof) {
            \Piwik\Profiler::setupProfilerXHProf($mainRun = false, $setupDuringTracking = true);
        }

        Config::setSingletonInstance(new Config(
            $testingEnvironment->configFileGlobal, $testingEnvironment->configFileLocal, $testingEnvironment->configFileCommon
        ));

        // Apply DI config from the fixture
        if ($testingEnvironment->fixtureClass) {
            $fixtureClass = $testingEnvironment->fixtureClass;
            if (class_exists($fixtureClass)) {
                /** @var Fixture $fixture */
                $fixture = new $fixtureClass;
                $diConfig = $fixture->provideContainerConfig();
                if (!empty($diConfig)) {
                    StaticContainer::addDefinitions($diConfig);
                }
            }
        }

        \Piwik\Cache\Backend\File::$invalidateOpCacheBeforeRead = true;

        Piwik::addAction('Access.createAccessSingleton', function($access) use ($testingEnvironment) {
            if (!$testingEnvironment->testUseRegularAuth) {
                $access = new Piwik_MockAccess($access);
                \Piwik\Access::setSingletonInstance($access);
            }
        });
        if (!$testingEnvironment->dontUseTestConfig) {
            Piwik::addAction('Config.createConfigSingleton', function(Config $config, &$cache, &$local) use ($testingEnvironment) {
                $config->setTestEnvironment($testingEnvironment->configFileLocal, $testingEnvironment->configFileGlobal, $testingEnvironment->configFileCommon);

                if ($testingEnvironment->configFileLocal) {
                    $local['General']['session_save_handler'] = 'dbtable';
                }

                $manager = \Piwik\Plugin\Manager::getInstance();
                $pluginsToLoad = $testingEnvironment->getCoreAndSupportedPlugins();
                if (!empty($testingEnvironment->pluginsToLoad)) {
                    $pluginsToLoad = array_unique(array_merge($pluginsToLoad, $testingEnvironment->pluginsToLoad));
                }

                sort($pluginsToLoad);

                $local['Plugins'] = array('Plugins' => $pluginsToLoad);

                $local['log']['log_writers'] = array('file');

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

            if ($testingEnvironment->fixtureClass) {
                $fixtureClass = $testingEnvironment->fixtureClass;
                if (class_exists($fixtureClass)) {
                    /** @var Fixture $fixture */
                    $fixture = new $fixtureClass;
                    $fixture->initializePlatform();
                }
            }
        }
        Piwik::addAction('Request.dispatch', function() use ($testingEnvironment) {
            if (empty($_GET['ignoreClearAllViewDataTableParameters'])) { // TODO: should use testingEnvironment variable, not query param
                \Piwik\ViewDataTable\Manager::clearAllViewDataTableParameters();
            }

            if ($testingEnvironment->optionsOverride) {
                foreach ($testingEnvironment->optionsOverride as $name => $value) {
                    Option::set($name, $value);
                }
            }

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
        Piwik::addAction('Platform.initialized', function () use ($testingEnvironment) {
            static $archivingTablesDeleted = false;

            if ($testingEnvironment->deleteArchiveTables
                && !$archivingTablesDeleted
            ) {
                $archivingTablesDeleted = true;
                DbHelper::deleteArchiveTables();
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
                'from'     => $mail->getFrom(),
                'to'       => $mail->getRecipients(),
                'subject'  => $mail->getSubject(),
                'contents' => $outputContent
            );

            file_put_contents($outputFile, json_encode($outputContents));
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
