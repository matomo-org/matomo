<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Framework\TestAspect;

use Piwik\Access;
use Piwik\Tests\Framework\TestAspect;
use Piwik\Tests\Framework\TestCase\PiwikTestCase;
use Piwik\Tracker\Cache;

/**
 * TODO
 */
class TestWithPiwikEnvironment extends TestAspect
{
    /**
     * @var TestWithContainer
     */
    private $testWithContainer;

    public function __construct()
    {
        $this->testWithContainer = new TestWithContainer();
    }

    public function setUp(PiwikTestCase $testCase)
    {
        $this->initializeTestEnvironmentVariables($testCase);

        $this->testWithContainer->setUp($testCase);

        Cache::deleteTrackerCache();
        \Piwik\Plugin\Manager::getInstance()->loadActivatedPlugins();

        // We need to be SU to create websites for tests
        Access::getInstance()->setSuperUserAccess();
    }

    public function tearDown(PiwikTestCase $testCase)
    {
        $this->testWithContainer->tearDown($testCase);
    }

    private function initializeTestEnvironmentVariables(PiwikTestCase $testCase)
    {
        // TODO: don't use static var, use test env var for this
        TestingEnvironmentManipulator::$extraPluginsToLoad = $fixture->extraPluginsToLoad; // TODO: change this

        $testEnv = $fixture->getTestEnvironment();
        $testEnv->testCaseClass = get_class($testCase);
        $testEnv->fixtureClass = get_class($fixture); // TODO: should be done else where

        if ($testEnv->loadRealTranslations !== null) {
            $testEnv->loadRealTranslations = true;
        }

        $testEnv->save();
        // TODO
    }
}