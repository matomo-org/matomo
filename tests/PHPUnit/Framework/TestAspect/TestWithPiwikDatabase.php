<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Framework\TestAspect;

use Piwik\Access;
use Piwik\Application\Environment;
use Piwik\Cache\Backend\File;
use Piwik\Db;
use Piwik\DbHelper;
use Piwik\Plugins\PrivacyManager\DoNotTrackHeaderChecker;
use Piwik\Plugins\PrivacyManager\IPAnonymizer;
use Piwik\SettingsPiwik;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestAspect;
use Piwik\Tests\Framework\TestCase\PiwikTestCase;
use Piwik\Tests\Framework\TestingEnvironmentManipulator;
use Piwik\Tracker\Cache;
use Piwik\Plugins\LanguagesManager\API as APILanguageManager;

/**
 * TODO
 */
class TestWithPiwikDatabase extends TestAspect
{
    /**
     * @var Environment
     */
    private $environment;

    /**
     * @var TestWithDatabase
     */
    private $testWithDatabase;

    /**
     * @var TestWithPiwikEnvironment
     */
    private $testWithPiwikEnvironment;

    /**
     * @var Fixture
     */
    private $fixture;

    private $isFixtureSetup = false;

    private $isNewFixtureSetup = false;

    public function __construct(Fixture $fixture = null)
    {
        $this->testWithDatabase = new TestWithDatabase();
        $this->testWithPiwikEnvironment = new TestWithPiwikEnvironment($fixture);
        $this->fixture = $fixture;
    }

    public static function isMethodAspect()
    {
        return false;
    }

    public function setUpBeforeClass($testCaseClass)
    {
        $this->testWithDatabase->setUpBeforeClass($testCaseClass);
    }

    public function setUp(PiwikTestCase $testCase)
    {
        $this->testWithPiwikEnvironment->setUp($testCase);

        if (!$this->isFixtureSetup) {
            $this->isNewFixtureSetup = $this->setUpFixture(get_class($testCase));
            $this->isFixtureSetup = true;
        }

        $this->testWithDatabase->setUp($testCase);
    }

    public function tearDown(PiwikTestCase $testCase)
    {
        $this->testWithDatabase->tearDown($testCase);
        $this->testWithPiwikEnvironment->tearDown($testCase);
    }

    public function setUpFixture($testCaseClass)
    {
        File::$invalidateOpCacheBeforeRead = true; // TODO: move this setting to DI

        $fixture = $this->getFixture($testCaseClass);

        DbHelper::createTables();
        Fixture::installAndActivatePlugins();
        Fixture::updateDatabase();

        if ($fixture->configureComponents) {
            // TODO: should be handled by DI.
            IPAnonymizer::deactivate();
            $dntChecker = new DoNotTrackHeaderChecker();
            $dntChecker->deactivate();
        }

        if ($fixture->createSuperUser) {
            Fixture::createSuperUser($fixture->removeExistingSuperUser);
            if (!(Access::getInstance() instanceof FakeAccess)) {
                $fixture->loginAsSuperUser();
            }

            APILanguageManager::getInstance()->setLanguageForUser('superUserLogin', 'en');
        }

        SettingsPiwik::overwritePiwikUrl(Fixture::getRootUrl() . 'tests/PHPUnit/proxy/');

        return $fixture->performSetUp();
    }

    public function tearDownAfterClass($testCaseClass)
    {
        $this->getFixture($testCaseClass)->performTearDown();

        $this->testWithDatabase->tearDownAfterClass($testCaseClass);
    }

    /**
     * @return boolean
     */
    public function isNewFixtureSetup()
    {
        return $this->isNewFixtureSetup;
    }

    private function getFixture($testCaseClass)
    {
        if ($this->fixture) {
            return $this->fixture;
        }

        return PiwikTestCase::getTestCaseFixture($testCaseClass);
    }
}