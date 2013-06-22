<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
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
            Piwik::createConfigObject();
            Piwik_Config::getInstance()->setTestEnvironment();

            $dbConfig = Piwik_Config::getInstance()->database;
            $dbName = $dbConfig['dbname'];
            $dbConfig['dbname'] = null;

            Piwik::createDatabaseObject($dbConfig);

            Piwik::dropDatabase();
            Piwik::createDatabase($dbName);
            Piwik::disconnectDatabase();

            Piwik::createDatabaseObject();
            Piwik::createTables();
            Piwik::createLogObject();

//            Piwik_PluginsManager::getInstance()->loadPlugins(array());
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
        Piwik::dropDatabase();
        Piwik_DataTable_Manager::getInstance()->deleteAll();
        Piwik_Option::getInstance()->clearCache();
        Piwik_PDFReports_API::$cache = array();
        Piwik_Site::clearCache();
        Piwik_Tracker_Cache::deleteTrackerCache();
        Piwik_Config::getInstance()->clear();
        Piwik_DataAccess_ArchiveTableCreator::clear();
        Zend_Registry::_unsetInstance();
    }

}
