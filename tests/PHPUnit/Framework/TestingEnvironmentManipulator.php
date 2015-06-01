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

    public function getExtraDefinitions()
    {
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
            $diConfig,
            array('observers.global' => \DI\add($this->globalObservers))
        );
    }
}