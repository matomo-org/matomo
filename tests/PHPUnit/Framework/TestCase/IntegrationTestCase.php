<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Framework\TestCase;

use Piwik\Access;
use Piwik\Cache as PiwikCache;
use Piwik\Db;
use Piwik\EventDispatcher;
use Piwik\Option;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestingEnvironmentVariables;

/**
 * Tests extending IntegrationTestCase are much slower to run: the setUp will
 * create all Piwik tables in a freshly empty test database.
 *
 * This allows each test method to start from a clean DB and setup initial state to
 * then test it.
 *
 * @since 2.8.0
 */
abstract class IntegrationTestCase extends SystemTestCase
{
    /**
     * @var Fixture
     */
    public static $fixture;
    public static $tableData;

    /**
     * Implementation details:
     *
     * To increase speed of tests, database setup is done once in setUpBeforeClass.
     * Afterwards, the content of the tables is stored in a static class variable,
     * self::$tableData. Before each individual test, the database tables are
     * truncated and the data in self::$tableData is restored.
     *
     * If your test modifies table columns, you will need to recreate the database
     * completely. This can be accomplished by:
     *
     *     public function setUp(): void
     *     {
     *         self::$fixture->performSetUp();
     *     }
     *
     *     public function tearDown(): void
     *     {
     *         parent::tearDown();
     *         self::$fixture->performTearDown();
     *     }
     */
    public static function setUpBeforeClass(): void
    {
        static::configureFixture(static::$fixture);
        parent::setUpBeforeClass();
        static::beforeTableDataCached();

        self::$tableData = self::getDbTablesWithData();
    }

    public static function tearDownAfterClass(): void
    {
        self::$tableData = array();

        parent::tearDownAfterClass();
    }

    /**
     * Setup the database and create the base tables for all tests
     */
    public function setUp(): void
    {
        parent::setUp();

        static::$fixture->extraDefinitions = array_merge(static::provideContainerConfigBeforeClass(), $this->provideContainerConfig());
        static::$fixture->createEnvironmentInstance();

        Db::get();
        Fixture::loadAllPlugins(new TestingEnvironmentVariables(), get_class($this), self::$fixture->extraPluginsToLoad);

        Access::getInstance()->setSuperUserAccess(true);

        if (!empty(self::$tableData)) {
            self::restoreDbTables(self::$tableData);
        }

        // Note: we can't clear all in memory caches at this point
        // Otherwise fixtures can't be used to e.g. manipulate static instances
        PiwikCache::getEagerCache()->flushAll();
        PiwikCache::getTransientCache()->flushAll();
        EventDispatcher::getInstance()->clearCache();
        Option::clearCache();
    }

    /**
     * Resets all caches and drops the database
     */
    public function tearDown(): void
    {
        Fixture::clearInMemoryCaches();
        static::$fixture->destroyEnvironment();

        parent::tearDown();
    }

    /**
     * @param Fixture $fixture
     */
    protected static function configureFixture($fixture)
    {
        $fixture->createSuperUser        = false;
        $fixture->configureComponents    = false;
        $fixture->dropDatabaseInTearDown = false;

        $fixture->extraTestEnvVars['loadRealTranslations'] = false;
    }

    protected static function beforeTableDataCached()
    {
        // empty
    }

    /**
     * Use this method to return custom container configuration that you want to apply for the tests.
     * This configuration will override Fixture config and config specified in SystemTestCase::provideContainerConfig().
     *
     * @return array
     */
    public function provideContainerConfig()
    {
        return array();
    }
}

IntegrationTestCase::$fixture = new Fixture();
