<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Framework;

use Piwik\Application\EnvironmentManipulator;
use Piwik\Application\Kernel\GlobalSettingsProvider;
use Piwik\DbHelper;
use Piwik\Option;

/**
 * Manipulates an environment for tests.
 */
class TestingEnvironmentManipulator implements EnvironmentManipulator
{
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

    public function makeGlobalSettingsProvider()
    {
        return new GlobalSettingsProvider($this->vars->configFileGlobal, $this->vars->configFileLocal, $this->vars->configFileCommon);
    }

    public function beforeContainerCreated()
    {
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

        // Apply DI config from the fixture
        $diConfig = array();
        if ($this->vars->fixtureClass) {
            $fixtureClass = $this->vars->fixtureClass;
            if (class_exists($fixtureClass)) {
                /** @var Fixture $fixture */
                $fixture = new $fixtureClass;
                $diConfig = $fixture->provideContainerConfig();
            }
        }

        if ($this->vars->testCaseClass) {
            $testCaseClass = $this->vars->testCaseClass;
            if (class_exists($testCaseClass)) {
                $testCase = new $testCaseClass();

                if (method_exists($testCase, 'provideContainerConfigBeforeClass')) {
                    $diConfig = array_merge($diConfig, $testCaseClass::provideContainerConfigBeforeClass());
                }

                if (method_exists($testCase, 'provideContainerConfig')) {
                    $diConfig = array_merge($diConfig, $testCase->provideContainerConfig());
                }
            }
        }

        return array(
            $testVarDefinitionSource,
            $diConfig,
            array('observers.global' => \DI\add($this->globalObservers)),
        );
    }

    public function getExtraEnvironments()
    {
        return array('test');
    }
}