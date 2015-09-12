<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\Framework\TestAspect;

use Piwik\Config;
use Piwik\Db;
use Piwik\DbHelper;
use Piwik\Ini\IniReader;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestAspect;
use Piwik\Tracker;

/**
 * TODO
 */
class TestWithDatabase extends TestAspect
{
    /**
     * Set in setUpBeforeClass so it can be used in tearDownAfterClass.
     *
     * @var Fixture
     */
    private $fixture;

    public function setUpBeforeClass($testCaseClass)
    {
        parent::setUpBeforeClass($testCaseClass);

        $fixture = $this->getTestCaseFixture($testCaseClass);
        $this->fixture = $fixture;

        $dbName = $fixture->getDbName();
        $fixture->dbName = $dbName;

        if ($fixture->persistFixtureData) {
            $fixture->dropDatabaseInSetUp = false;
            $fixture->dropDatabaseInTearDown = false;
            $fixture->overwriteExisting = false;
            $fixture->removeExistingSuperUser = false;
        }

        if ($fixture->dbName === false) {
            $fixture->dbName = Config::getInstance()->database['dbname'];
        }

        self::connectWithoutDatabase();

        if ($fixture->dropDatabaseInSetUp) {
            self::dropDatabase($dbName);
        }

        DbHelper::createDatabase($dbName);
        DbHelper::disconnectDatabase();
        Tracker::disconnectCachedDbConnection();

        // reconnect once we're sure the database exists
        Config::getInstance()->database['dbname'] = $fixture->dbName;
        Db::createDatabaseObject();

        Db::get()->query("SET wait_timeout=28800;");

        DbHelper::createTables();

        if (!$fixture->isFixtureSetUp()) {
            DbHelper::truncateAllTables();
        }
    }

    public function tearDownAfterClass($testCaseClass)
    {
        if ($this->fixture->dropDatabaseInTearDown) {
            $this->dropDatabase($this->fixture->dbName);
        }
    }

    public static function isMethodAspect()
    {
        return false;
    }

    /**
     * Connects to MySQL w/o specifying a database.
     */
    public static function connectWithoutDatabase()
    {
        $dbConfig = Config::getInstance()->database;
        $oldDbName = $dbConfig['dbname'];
        $dbConfig['dbname'] = null;

        Db::createDatabaseObject($dbConfig);

        $dbConfig['dbname'] = $oldDbName;
    }

    public static function dropDatabase($dbName)
    {
        $iniReader = new IniReader();
        $config = $iniReader->readFile(PIWIK_INCLUDE_PATH . '/config/config.ini.php');
        $originalDbName = $config['database']['dbname'];
        if ($dbName == $originalDbName
            && $dbName != 'piwik_tests'
        ) { // santity check
            throw new \Exception("Trying to drop original database '$originalDbName'. Something's wrong w/ the tests.");
        }

        try {
            DbHelper::dropDatabase($dbName);
        } catch (\Exception $e) {
            printf("Dropping database %s failed: %s\n", $dbName, $e->getMessage());
        }
    }

    private function getTestCaseFixture($testCaseClass)
    {
        if (!isset($testCaseClass::$fixture)) {
            return new Fixture();
        } else {
            return $testCaseClass::$fixture;
        }
    }
}
