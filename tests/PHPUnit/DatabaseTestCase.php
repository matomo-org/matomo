<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
use Piwik\Config;
use Piwik\Db;
use Piwik\Tests\Fixture;
use Piwik\Tests\IntegrationTestCase;

/**
 * Tests extending DatabaseTestCase are much slower to run: the setUp will
 * create all Piwik tables in a freshly empty test database.
 *
 * This allows each test method to start from a clean DB and setup initial state to
 * then test it.
 *
 */
class DatabaseTestCase extends IntegrationTestCase
{
    /**
     * @var Fixture
     */
    public static $fixture;
    public static $tableData;

    public static function setUpBeforeClass()
    {
        static::configureFixture(self::$fixture);
        parent::setUpBeforeClass();

        self::$tableData = self::getDbTablesWithData();
    }

    /**
     * Setup the database and create the base tables for all tests
     */
    public function setUp()
    {
        parent::setUp();

        Config::getInstance()->setTestEnvironment();

        if (!empty(self::$tableData)) {
            self::restoreDbTables(self::$tableData);
        }
    }

    /**
     * Resets all caches and drops the database
     */
    public function tearDown()
    {
        self::$fixture->clearInMemoryCaches();

        parent::tearDown();
    }

    protected static function configureFixture($fixture)
    {
        $fixture->loadTranslations = false;
        $fixture->createSuperUser = false;
        $fixture->configureComponents = false;
    }
}

DatabaseTestCase::$fixture = new Fixture();