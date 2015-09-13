<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\Framework\TestAspect;

use Piwik\Common;
use Piwik\Config;
use Piwik\Db;
use Piwik\DbHelper;
use Piwik\Ini\IniReader;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestAspect;
use Piwik\Tests\Framework\TestCase\PiwikTestCase;
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

    /**
     * @var array
     */
    private $tableData;

    public static function isMethodAspect()
    {
        return false;
    }

    public function setUpBeforeClass($testCaseClass)
    {
        parent::setUpBeforeClass($testCaseClass);

        $fixture = PiwikTestCase::getTestCaseFixture($testCaseClass);
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
        $this->tableData = array();

        if ($this->fixture->dropDatabaseInTearDown) {
            $this->dropDatabase($this->fixture->dbName);
        }
    }

    public function setUp(PiwikTestCase $testCase)
    {
        if (empty($this->tableData)) {
            $this->tableData = $this->getDbTablesWithData();
        } else {
            $this->restoreDbTables($this->tableData);
        }
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

    private function restoreDbTables($tables)
    {
        $db = Db::fetchOne("SELECT DATABASE()");
        if (empty($db)) {
            Db::exec("USE " . Config::getInstance()->database_tests['dbname']);
        }

        DbHelper::truncateAllTables();

        // insert data
        $existingTables = DbHelper::getTablesInstalled();
        foreach ($tables as $table => $rows) {
            // create table if it's an archive table
            if (strpos($table, 'archive_') !== false && !in_array($table, $existingTables)) {
                $tableType = strpos($table, 'archive_numeric') !== false ? 'archive_numeric' : 'archive_blob';

                $createSql = DbHelper::getTableCreateSql($tableType);
                $createSql = str_replace(Common::prefixTable($tableType), $table, $createSql);
                Db::query($createSql);
            }

            if (empty($rows)) {
                continue;
            }

            $rowsSql = array();
            $bind = array();
            foreach ($rows as $row) {
                $values = array();
                foreach ($row as $value) {
                    if (is_null($value)) {
                        $values[] = 'NULL';
                    } else if (is_numeric($value)) {
                        $values[] = $value;
                    } else if (!ctype_print($value)) {
                        $values[] = "x'" . bin2hex($value) . "'";
                    } else {
                        $values[] = "?";
                        $bind[] = $value;
                    }
                }

                $rowsSql[] = "(" . implode(',', $values) . ")";
            }

            $sql = "INSERT INTO `$table` VALUES " . implode(',', $rowsSql);
            Db::query($sql, $bind);
        }
    }

    private function getDbTablesWithData()
    {
        $result = array();
        foreach (DbHelper::getTablesInstalled() as $tableName) {
            $result[$tableName] = Db::fetchAll("SELECT * FROM `$tableName`");
        }
        return $result;
    }
}
