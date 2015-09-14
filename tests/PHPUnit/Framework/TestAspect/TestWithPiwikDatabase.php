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

    private $isFixtureSetup = false;

    public function __construct()
    {
        $this->testWithDatabase = new TestWithDatabase();
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
        if (!$this->isFixtureSetup) {
            $this->setUpFixture(get_class($testCase));
            $this->isFixtureSetup = true;
        }

        $this->testWithDatabase->setUp($testCase);
    }

    public function setUpFixture($testCaseClass)
    {
        File::$invalidateOpCacheBeforeRead = true; // TODO: move this setting to DI

        $fixture = PiwikTestCase::getTestCaseFixture($testCaseClass);

        // TODO: don't use static var, use test env var for this
        TestingEnvironmentManipulator::$extraPluginsToLoad = $fixture->extraPluginsToLoad;

        $testEnv = $fixture->getTestEnvironment();
        $testEnv->testCaseClass = $testCaseClass;
        $testEnv->fixtureClass = get_class($this);

        if ($testEnv->loadRealTranslations !== null) {
            $testEnv->loadRealTranslations = true;
        }

        $testEnv->save();

        $this->environment = $fixture->createEnvironmentInstance();

        Cache::deleteTrackerCache();
        \Piwik\Plugin\Manager::getInstance()->loadActivatedPlugins();

        // We need to be SU to create websites for tests
        Access::getInstance()->setSuperUserAccess();

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

        $fixture->performSetUp();
    }

    public function tearDownAfterClass($testCaseClass)
    {
        $this->environment->destroy();
        $this->environment = null;
    }
}