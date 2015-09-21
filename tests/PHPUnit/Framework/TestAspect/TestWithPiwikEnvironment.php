<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Framework\TestAspect;

use Piwik\Access;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestAspect;
use Piwik\Tests\Framework\TestCase\PiwikTestCase;
use Piwik\Tests\Framework\TestingEnvironmentVariables;
use Piwik\Tracker\Cache;
use Piwik\Cache as PiwikCache;

/**
 * TODO
 */
class TestWithPiwikEnvironment extends TestAspect
{
    /**
     * @var TestWithContainer
     */
    private $testWithContainer;

    /**
     * @var Fixture
     */
    private $fixture;

    public function __construct(Fixture $fixture = null)
    {
        $this->testWithContainer = new TestWithContainer();
        $this->fixture = $fixture;
    }

    public function setUp(PiwikTestCase $testCase)
    {
        $this->initializeTestEnvironmentVariables($testCase);

        $this->testWithContainer->setUp($testCase);
        $this->getFixture(get_class($testCase))->piwikEnvironment = $this->testWithContainer->getEnvironment();

        Cache::deleteTrackerCache();
        \Piwik\Plugin\Manager::getInstance()->loadActivatedPlugins();

        // We need to be SU to create websites for tests
        Access::getInstance()->setSuperUserAccess();
    }

    public function tearDown(PiwikTestCase $testCase)
    {
        PiwikCache::getEagerCache()->flushAll();

        $this->testWithContainer->tearDown($testCase);
    }

    private function initializeTestEnvironmentVariables(PiwikTestCase $testCase)
    {
        $testCaseClass = get_class($testCase);

        $fixture = $this->getFixture($testCaseClass);

        $testVars = new TestingEnvironmentVariables();
        $testVars->delete();

        $testVars->testCaseClass = $testCaseClass;
        $testVars->fixtureClass = get_class($fixture); // TODO: should only be done for UI tests
        $testVars->pluginsToLoad = $fixture->extraPluginsToLoad;

        if (getenv('PIWIK_USE_XHPROF') == 1) {
            $testVars->useXhprof = true;
        }

        if ($testVars->loadRealTranslations !== null) {
            $testVars->loadRealTranslations = true;
        }

        $testVars->save();

        $fixture->testEnvironment = $testVars;
    }

    private function getFixture($testCaseClass)
    {
        if ($this->fixture) {
            return $this->fixture;
        }

        return PiwikTestCase::getTestCaseFixture($testCaseClass);
    }
}