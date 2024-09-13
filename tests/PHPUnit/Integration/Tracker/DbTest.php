<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Tracker;

use Piwik\Common;
use Piwik\Config;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Timer;
use Piwik\Tracker;

/**
 * Tracker DB test
 *
 * @group Core
 * @group TrackerDbTest
 */
class DbTest extends IntegrationTestCase
{
    private $tableName;
    public function setUp(): void
    {
        parent::setUp();
        $this->tableName = Common::prefixTable('option');
    }

    public function testInnodbLockWaitTimeout()
    {
        $idSite1 = Fixture::createWebsite('2020-01-01 02:02:02');
        $idSite2 = Fixture::createWebsite('2020-01-01 02:02:02');

        // we expect it does not take more than 3s for the lock exceeded time to happen
        // usually be like 30-50 seconds
        $tracker = Config::getInstance()->Tracker;
        $tracker['innodb_lock_wait_timeout'] = 3;
        Config::getInstance()->Tracker = $tracker;

        $db1 = Tracker\Db::connectPiwikTrackerDb();
        $db2 = Tracker\Db::connectPiwikTrackerDb();

        $timer = new Timer();

        $db1->beginTransaction();
        $db2->beginTransaction();

        $site = Common::prefixTable('site');
        $db1->query("UPDATE $site SET name = ? WHERE idsite = ?", ['foo', $idSite1]);
        $db2->query("UPDATE $site SET name = ? WHERE idsite = ?", ['foo', $idSite2]);

        try {
            $db1->query("UPDATE $site SET name = ? WHERE idsite = ?", ['bar', $idSite2]);
        } catch (\Exception $e) {
            $this->assertStringContainsString('Lock wait timeout exceeded; try restarting transaction', $e->getMessage());
            if ($this->isMysqli()) { // code is not included in error message on Mysqli
                $this->assertEquals(1205, $e->getCode());
            } else {
                $this->assertStringContainsString(' 1205 ', $e->getMessage()); // mysql error code
                $this->assertTrue($db1->isErrNo($e, 1205));
            }
            $this->assertTrue($e instanceof Tracker\Db\DbException);
        }

        $ms = $timer->getTimeMs();

        $this->assertGreaterThan(3000, $ms);
        $this->assertLessThan(5000, $ms);
    }
    public function testRowCountWhenUpdatingReturnsAllMatchedRowsNotOnlyUpdatedRows()
    {
        $db = Tracker::getDatabase();
        // insert one record
        $db->query("INSERT INTO `" . Common::prefixTable('option') . "` VALUES ('rowid', '1', false)");

        // We will now UPDATE this table and check rowCount() value
        $sqlUpdate = "UPDATE `" . Common::prefixTable('option') . "` SET option_value = 2";

        // when no record was updated, return 0
        $result = $db->query($sqlUpdate . " WHERE option_name = 'NOT FOUND'");
        $this->assertSame(0, $db->rowCount($result));

        // when one record was found and updated, returns 1
        $result = $db->query($sqlUpdate . " WHERE option_name = 'rowid'");
        $this->assertSame(1, $db->rowCount($result));

        // when one record was found but NOT actually updated (as values have not changed), we make sure to return 1
        // testing for MYSQLI_CLIENT_FOUND_ROWS and MYSQL_ATTR_FOUND_ROWS
        $result = $db->query($sqlUpdate . " WHERE option_name = 'rowid'");
        $this->assertSame(1, $db->rowCount($result));
    }

    public function testRowCountWhenInserting()
    {
        $db = Tracker::getDatabase();
        // insert one record
        $result = $this->insertRowId();

        $this->assertSame(1, $db->rowCount($result));
    }

    public function testFetchOneNotExistingTable()
    {
        $this->expectException(\Piwik\Tracker\Db\DbException::class);
        $this->expectExceptionMessage('doesn\'t exist');

        $db = Tracker::getDatabase();
        $this->insertRowId(3);
        $val = $db->fetchOne('SELECT option_value FROM foobarbaz where option_value = "rowid"');
        $this->assertEquals('3', $val);
    }

    public function testQueryErrorWhenInsertingDuplicateRow()
    {
        $this->expectException(\Piwik\Tracker\Db\DbException::class);
        $this->expectExceptionMessage('Duplicate entry');

        $this->insertRowId();
        $this->insertRowId();
    }

    public function testFetchOne()
    {
        $db = Tracker::getDatabase();
        $this->insertRowId(3);
        $val = $db->fetchOne('SELECT option_value FROM `' . $this->tableName . '` where option_name = "rowid"');
        $this->assertEquals('3', $val);
    }

    public function testFetchOneNoMatch()
    {
        $db = Tracker::getDatabase();
        $val = $db->fetchOne('SELECT option_value from `' . $this->tableName . '` where option_name = "foobar"');
        $this->assertFalse($val);
    }

    public function testFetchRow()
    {
        $db = Tracker::getDatabase();
        $this->insertRowId(3);
        $val = $db->fetchRow('SELECT option_value from `' . $this->tableName . '` where option_name = "rowid"');
        $this->assertEquals(array(
            'option_value' => '3'
        ), $val);
    }

    public function testFetchRowNoMatch()
    {
        $db = Tracker::getDatabase();
        $val = $db->fetchRow('SELECT option_value from `' . $this->tableName . '` where option_name = "foobar"');
        $this->assertFalse($val);
    }

    public function testFetch()
    {
        $db = Tracker::getDatabase();
        $this->insertRowId(3);
        $val = $db->fetch('SELECT option_value from `' . $this->tableName . '` where option_name = "rowid"');
        $this->assertEquals(array(
            'option_value' => '3'
        ), $val);
    }

    public function testFetchNoMatch()
    {
        $db = Tracker::getDatabase();
        $val = $db->fetch('SELECT option_value from `' . $this->tableName . '` where option_name = "foobar"');
        $this->assertFalse($val);
    }

    public function testFetchAll()
    {
        $db = Tracker::getDatabase();
        $this->insertRowId(3);
        $val = $db->fetchAll('SELECT option_value from `' . $this->tableName . '` where option_name = "rowid"');
        $this->assertEquals(array(
            array(
                'option_value' => '3'
            )
        ), $val);
    }

    public function testFetchAllNoMatch()
    {
        $db = Tracker::getDatabase();
        $val = $db->fetchAll('SELECT option_value from `' . $this->tableName . '` where option_name = "foobar"');
        $this->assertEquals(array(), $val);
    }

    public function testConnectionCollationDefault(): void
    {
        $config = Config::getInstance();
        $config->database['collation'] = null;
        $db = Tracker\Db::connectPiwikTrackerDb();

        // exact value depends on database used
        $currentCollation = $db->fetchOne('SELECT @@collation_connection');
        self::assertStringStartsWith('utf8', $currentCollation);
    }

    public function testConnectionCollationSetInConfig(): void
    {
        $config = Config::getInstance();
        $config->database['collation'] = $config->database['charset'] . '_swedish_ci';
        $db = Tracker\Db::connectPiwikTrackerDb();

        $currentCollation = $db->fetchOne('SELECT @@collation_connection');
        self::assertSame($config->database['collation'], $currentCollation);
    }

    private function insertRowId($value = '1')
    {
        $db = Tracker::getDatabase();
        return $db->query("INSERT INTO `" . $this->tableName . "` VALUES ('rowid', '$value', false)");
    }
}
