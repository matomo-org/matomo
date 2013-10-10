<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
use Piwik\Config;
use Piwik\DataAccess\ArchiveTableCreator;
use Piwik\DataTable\Manager;
use Piwik\DbHelper;
use Piwik\Db;
use Piwik\Option;
use Piwik\Plugins\ScheduledReports\API;
use Piwik\Site;
use Piwik\Tracker\Cache;

/**
 * Tests extending DatabaseTestCase are much slower to run: the setUp will
 * create all Piwik tables in a freshly empty test database.
 *
 * This allows each test method to start from a clean DB and setup initial state to
 * then test it.
 *
 */
class DatabaseTestCase extends PHPUnit_Framework_TestCase
{

    /**
     * Setup the database and create the base tables for all tests
     */
    public function setUp()
    {
        parent::setUp();
        try {
            Config::getInstance()->setTestEnvironment();

            $dbConfig = Config::getInstance()->database;
            $dbName = $dbConfig['dbname'];
            $dbConfig['dbname'] = null;

            Db::createDatabaseObject($dbConfig);

            DbHelper::dropDatabase();
            DbHelper::createDatabase($dbName);
            DbHelper::disconnectDatabase();

            Db::createDatabaseObject();
            DbHelper::createTables();

//            \Piwik\Manager::getInstance()->loadPlugins(array());
            IntegrationTestCase::loadAllPlugins();

        } catch (Exception $e) {
            $this->fail("TEST INITIALIZATION FAILED: " . $e->getMessage());
        }

        include "DataFiles/SearchEngines.php";
        include "DataFiles/Languages.php";
        include "DataFiles/Countries.php";
        include "DataFiles/Currencies.php";
        include "DataFiles/LanguageToCountry.php";
    }

    /**
     * Resets all caches and drops the database
     */
    public function tearDown()
    {
        parent::tearDown();
        IntegrationTestCase::unloadAllPlugins();
        DbHelper::dropDatabase();
        Manager::getInstance()->deleteAll();
        Option::clearCache();
        API::$cache = array();
        Site::clearCache();
        Cache::deleteTrackerCache();
        Config::getInstance()->clear();
        ArchiveTableCreator::clear();
        \Piwik\Registry::unsetInstance();
    }

}
