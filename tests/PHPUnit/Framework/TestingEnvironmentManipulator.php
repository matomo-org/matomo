<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Framework;

use Interop\Container\ContainerInterface;
use Piwik\Application\Environment;
use Piwik\Application\EnvironmentManipulator;
use Piwik\Application\Kernel\GlobalSettingsProvider;
use Piwik\Application\Kernel\PluginList;
use Piwik\Config;
use Piwik\DbHelper;
use Piwik\Option;
use Piwik\Plugin;

class FakePluginList extends PluginList
{
    private $plugins;

    public function __construct(GlobalSettingsProvider $globalSettingsProvider, $plugins)
    {
        parent::__construct($globalSettingsProvider);

        $this->plugins = $this->sortPlugins($plugins);

        $section = $globalSettingsProvider->getSection('Plugins');
        $section['Plugins'] = $this->plugins;
        $globalSettingsProvider->setSection('Plugins', $section);
    }
}

/**
 * Manipulates an environment for tests.
 */
class TestingEnvironmentManipulator implements EnvironmentManipulator
{
    /**
     * @var string[]
     */
    public static $extraPluginsToLoad = array();

    /**
     * @var TestingEnvironmentVariables
     */
    private $vars;

    private $globalObservers;

    public function __construct(TestingEnvironmentVariables $testingEnvironment, array $globalObservers = array())
    {
        $this->vars = $testingEnvironment;
        $this->globalObservers = $globalObservers;
    }

    public function makeGlobalSettingsProvider(GlobalSettingsProvider $original)
    {
        if ($this->vars->configFileGlobal
            || $this->vars->configFileLocal
            || $this->vars->configFileCommon
        ) {
            return new GlobalSettingsProvider($this->vars->configFileGlobal, $this->vars->configFileLocal, $this->vars->configFileCommon);
        } else {
            return $original;
        }
    }

    public function makePluginList(GlobalSettingsProvider $globalSettingsProvider)
    {
        return new FakePluginList($globalSettingsProvider, $this->getPluginsToLoadDuringTest());
    }

    public function beforeContainerCreated()
    {
        $this->vars->reload();

        if ($this->vars->queryParamOverride) {
            foreach ($this->vars->queryParamOverride as $key => $value) {
                $_GET[$key] = $value;
            }
        }

        if ($this->vars->globalsOverride) {
            foreach ($this->vars->globalsOverride as $key => $value) {
                $GLOBALS[$key] = $value;
            }
        }

        if ($this->vars->hostOverride) {
            \Piwik\Url::setHost($this->vars->hostOverride);
        }

        if ($this->vars->useXhprof) {
            \Piwik\Profiler::setupProfilerXHProf($mainRun = false, $setupDuringTracking = true);
        }

        \Piwik\Cache\Backend\File::$invalidateOpCacheBeforeRead = true;
    }

    public function onEnvironmentBootstrapped()
    {
        if (empty($_GET['ignoreClearAllViewDataTableParameters'])) { // TODO: should use testingEnvironment variable, not query param
            try {
                \Piwik\ViewDataTable\Manager::clearAllViewDataTableParameters();
            } catch (\Exception $ex) {
                // ignore (in case DB is not setup)
            }
        }

        if ($this->vars->optionsOverride) {
            try {
                foreach ($this->vars->optionsOverride as $name => $value) {
                    Option::set($name, $value);
                }
            } catch (\Exception $ex) {
                // ignore (in case DB is not setup)
            }
        }

        \Piwik\Plugins\CoreVisualizations\Visualizations\Cloud::$debugDisableShuffle = true;
        \Piwik\Visualization\Sparkline::$enableSparklineImages = false;
        \Piwik\Plugins\ExampleUI\API::$disableRandomness = true;

        if ($this->vars->deleteArchiveTables
            && !$this->vars->_archivingTablesDeleted
        ) {
            $this->vars->_archivingTablesDeleted = true;
            DbHelper::deleteArchiveTables();
        }
    }

    public function getExtraDefinitions()
    {
        $testVarDefinitionSource = new TestingEnvironmentVariablesDefinitionSource($this->vars);

        $diConfigs = array($testVarDefinitionSource);
        if ($this->vars->testCaseClass) {
            $testCaseClass = $this->vars->testCaseClass;
            if ($this->classExists($testCaseClass)) {
                $testCase = new $testCaseClass();

                // Apply DI config from the fixture
                if (isset($testCaseClass::$fixture)) {
                    $diConfigs[] = $testCaseClass::$fixture->provideContainerConfig();
                }

                if (method_exists($testCaseClass, 'provideContainerConfigBeforeClass')) {
                    $diConfigs[] = $testCaseClass::provideContainerConfigBeforeClass();
                }

                if (method_exists($testCase, 'provideContainerConfig')) {
                    $diConfigs[] = $testCase->provideContainerConfig();
                }
            }
        } else if ($this->vars->fixtureClass) {
            $fixtureClass = $this->vars->fixtureClass;

            if ($this->classExists($fixtureClass)) {
                $fixture = new $fixtureClass();

                if (method_exists($fixture, 'provideContainerConfig')) {
                    $diConfigs[] = $fixture->provideContainerConfig();
                }
            }
        }

        $plugins = $this->getPluginsToLoadDuringTest();
        $diConfigs[] = array(
            'observers.global' => \DI\add($this->globalObservers),

            'Piwik\Config' => \DI\decorate(function (Config $config, ContainerInterface $c) use ($plugins) {
                /** @var PluginList $pluginList */
                $pluginList = $c->get('Piwik\Application\Kernel\PluginList');
                $plugins = $pluginList->sortPlugins($plugins);

                // set the plugins to load, has to be done to Config, since Config will reload files on construction.
                // TODO: probably shouldn't need to do this, will wait until 3.0 to remove.
                $config->Plugins['Plugins'] = $plugins;

                return $config;
            }),
        );

        return $diConfigs;
    }

    public function getExtraEnvironments()
    {
        $result = array('test');

        $extraEnvironments = $this->vars->extraDiEnvironments ?: array();
        $result = array_merge($result, $extraEnvironments);

        return $result;
    }

    private function getPluginsToLoadDuringTest()
    {
        $plugins = $this->vars->getCoreAndSupportedPlugins();

        // make sure the plugin that executed this method is included in the plugins to load
        $extraPlugins = array_merge(
            self::$extraPluginsToLoad,
            $this->vars->pluginsToLoad ?: array(),
            array(
                Plugin::getPluginNameFromBacktrace(debug_backtrace()),
                Plugin::getPluginNameFromNamespace($this->vars->testCaseClass),
                Plugin::getPluginNameFromNamespace($this->vars->fixtureClass),
                Plugin::getPluginNameFromNamespace(get_called_class())
            )
        );
        foreach ($extraPlugins as $pluginName) {
            if (empty($pluginName)) {
                continue;
            }

            if (in_array($pluginName, $plugins)) {
                continue;
            }

            $plugins[] = $pluginName;
        }

        return $plugins;
    }

    private function classExists($klass)
    {
        if (class_exists($klass)) {
            return true;
        } else if (empty($klass)) {
            return false;
        } else {
            throw new \Exception("TestingEnvironmentManipulator: Autoloader cannot find class '$klass'. "
                . "Is the namespace correct? Is the file in the correct folder?");
        }
    }
}
