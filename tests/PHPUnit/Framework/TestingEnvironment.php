<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\Framework;

use Piwik\Plugin\Manager as PluginManager;
use Exception;
use Piwik\Option;
use Piwik\Container\StaticContainer;
use Piwik\DbHelper;
use Piwik\Common;
use Piwik\Config\IniFileChain;
use Piwik\Config;
use Piwik\Piwik;
use Piwik\Tests\Framework\Mock\TestConfig;
use Piwik_MockAccess;

/**
 * Sets the test environment.
 */
class TestingEnvironment
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
        $settings = new \Piwik\Application\Kernel\GlobalSettingsProvider();
        $pluginManager = new PluginManager(new \Piwik\Application\Kernel\PluginList($settings));

        $disabledPlugins = $pluginManager->getCorePluginsDisabledByDefault();
        $disabledPlugins[] = 'LoginHttpAuth';
        $disabledPlugins[] = 'ExampleVisualization';

        $disabledPlugins = array_diff($disabledPlugins, array(
            'DBStats', 'ExampleUI', 'ExampleCommand', 'ExampleSettingsPlugin'
        ));

        $plugins = array_filter($pluginManager->readPluginsDirectory(), function ($pluginName) use ($disabledPlugins, $pluginManager) {
            if (in_array($pluginName, $disabledPlugins)) {
                return false;
            }

            return $pluginManager->isPluginBundledWithCore($pluginName)
            || $pluginManager->isPluginOfficialAndNotBundledWithCore($pluginName);
        });

        sort($plugins);

        return $plugins;
    }

    public static function addHooks()
    {
        $testingEnvironment = new TestingEnvironment();

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

        \Piwik\Application\Kernel\GlobalSettingsProvider::getSingletonInstance(
            $testingEnvironment->configFileGlobal,
            $testingEnvironment->configFileLocal,
            $testingEnvironment->configFileCommon
        );

        // Apply DI config from the fixture
        $diConfig = array();
        if ($testingEnvironment->fixtureClass) {
            $fixtureClass = $testingEnvironment->fixtureClass;
            if (class_exists($fixtureClass)) {
                /** @var Fixture $fixture */
                $fixture = new $fixtureClass;
                $diConfig = $fixture->provideContainerConfig();
            }
        }

        if ($testingEnvironment->testCaseClass) {
            $testCaseClass = $testingEnvironment->testCaseClass;
            if (class_exists($testCaseClass)) {
                $testCase = new $testCaseClass();
                if (method_exists($testCase, 'provideContainerConfig')) {
                    $diConfig = array_merge($diConfig, $testCase->provideContainerConfig());
                }
            }
        }

        if (!empty($diConfig)) {
            StaticContainer::addDefinitions($diConfig);
        }

        \Piwik\Cache\Backend\File::$invalidateOpCacheBeforeRead = true;

        Piwik::addAction('Access.createAccessSingleton', function ($access) use ($testingEnvironment) {
            if (!$testingEnvironment->testUseRegularAuth) {
                $access = new Piwik_MockAccess($access);
                \Piwik\Access::setSingletonInstance($access);
            }
        });

        $pluginsToLoad = $testingEnvironment->getCoreAndSupportedPlugins();
        if (!empty($testingEnvironment->pluginsToLoad)) {
            $pluginsToLoad = array_unique(array_merge($pluginsToLoad, $testingEnvironment->pluginsToLoad));
        }

        sort($pluginsToLoad);

        if (!$testingEnvironment->dontUseTestConfig) {
            Piwik::addAction('Config.createConfigSingleton', function (IniFileChain $chain) use ($testingEnvironment, $pluginsToLoad) {
                $general =& $chain->get('General');
                $plugins =& $chain->get('Plugins');
                $log =& $chain->get('log');
                $database =& $chain->get('database');

                if ($testingEnvironment->configFileLocal) {
                    $general['session_save_handler'] = 'dbtable';
                }

                $plugins['Plugins'] = $pluginsToLoad;

                $log['log_writers'] = array('file');

                // TODO: replace this and below w/ configOverride use
                if ($testingEnvironment->tablesPrefix) {
                    $database['tables_prefix'] = $testingEnvironment->tablesPrefix;
                }

                if ($testingEnvironment->dbName) {
                    $database['dbname'] = $testingEnvironment->dbName;
                }

                if ($testingEnvironment->configOverride) {
                    $cache =& $chain->getAll();
                    $cache = $testingEnvironment->arrayMergeRecursiveDistinct($cache, $testingEnvironment->configOverride);
                }
            });

            Config::setSingletonInstance(new TestConfig(
                $testingEnvironment->configFileGlobal, $testingEnvironment->configFileLocal, $testingEnvironment->configFileCommon,
                $testingEnvironment
            ));
        } else {
            \Piwik\Application\Kernel\GlobalSettingsProvider::unsetSingletonInstance();

            Config::setSingletonInstance(new Config(
                $testingEnvironment->configFileGlobal, $testingEnvironment->configFileLocal, $testingEnvironment->configFileCommon
            ));
        }
        Piwik::addAction('Request.dispatch', function () use ($testingEnvironment) {
            if (empty($_GET['ignoreClearAllViewDataTableParameters'])) { // TODO: should use testingEnvironment variable, not query param
                try {
                    \Piwik\ViewDataTable\Manager::clearAllViewDataTableParameters();
                } catch (\Exception $ex) {
                    // ignore (in case DB is not setup)
                }
            }

            if ($testingEnvironment->optionsOverride) {
                try {
                    foreach ($testingEnvironment->optionsOverride as $name => $value) {
                        Option::set($name, $value);
                    }
                } catch (\Exception $ex) {
                    // ignore (in case DB is not setup)
                }
            }

            \Piwik\Plugins\CoreVisualizations\Visualizations\Cloud::$debugDisableShuffle = true;
            \Piwik\Visualization\Sparkline::$enableSparklineImages = false;
            \Piwik\Plugins\ExampleUI\API::$disableRandomness = true;
        });
        Piwik::addAction('AssetManager.getStylesheetFiles', function (&$stylesheets) {
            $stylesheets[] = 'tests/resources/screenshot-override/override.css';
        });
        Piwik::addAction('AssetManager.getJavaScriptFiles', function (&$jsFiles) {
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
        Piwik::addAction('Environment.bootstrapped', function () use ($testingEnvironment) {
            $testingEnvironment->logVariables();
            $testingEnvironment->executeSetupTestEnvHook();
        });
    }

    public static function addSendMailHook()
    {
        Piwik::addAction('Test.Mail.send', function (\Zend_Mail $mail) {
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
                    : $value;
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