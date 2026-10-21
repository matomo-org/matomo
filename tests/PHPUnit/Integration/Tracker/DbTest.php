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
use Piwik\Db;
use Piwik\Db\TransactionErrors;
use Piwik\Tests\Framework\DbErrorRaiser;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Timer;
use Piwik\Tracker;
use Piwik\Tracker\Db\DbException;

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
        DbErrorRaiser::install();
    }

    public function tearDown(): void
    {
        // ends any transaction a failed test left open, which would block dropping the procedure
        Tracker::disconnectCachedDbConnection();
        DbErrorRaiser::uninstall();
        parent::tearDown();
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

        // only the statement was rolled back, the transaction can still be used
        $this->assertFalse($db1->isTransactionAborted());
        $result = $db1->query("UPDATE $site SET name = ? WHERE idsite = ?", ['baz', $idSite1]);
        $this->assertSame(1, $db1->rowCount($result));

        $ms = $timer->getTimeMs();

        $this->assertGreaterThan(3000, $ms);
        $this->assertLessThan(5000, $ms);
    }
    /**
     * @dataProvider getErrorsThatEndTheTransaction
     */
    public function testErrorThatEndsTheTransactionStopsEveryLaterQueryAndTheCommit(int $errorNumber, string $message)
    {
        $db = Tracker\Db::connectPiwikTrackerDb();
        $xid = $db->beginTransaction();
        $this->insertOption($db, 'before_error');

        $error = $this->raiseError($db, $errorNumber, $message);
        $this->assertTrue($db->isErrNo($error, $errorNumber));
        $this->assertTrue($db->isTransactionAborted());

        // a tracking step that logs the error and carries on must not be able to write anything else
        $refusal = $this->catchException(function () use ($db) {
            $this->insertOption($db, 'after_error');
        });
        $this->assertTrue($db->isErrNo($refusal, TransactionErrors::ERROR_CODE_DEADLOCK));
        $this->assertSame(1, preg_match('/(?:\[|\s)([0-9]{4})(?:\]|\s)/', $refusal->getMessage(), $match));
        $this->assertSame('1213', $match[1], 'Checks that read the number from the message must see a deadlock too');

        $commitError = $this->catchException(function () use ($db, $xid) {
            $db->commit($xid);
        });
        $this->assertTrue($db->isErrNo($commitError, TransactionErrors::ERROR_CODE_DEADLOCK));
        $this->assertFalse($db->isInTransaction());
        $this->assertFalse($db->isTransactionAborted());

        $this->assertOptionsStored([]);
        $this->assertNewTransactionWorks();
    }

    public function getErrorsThatEndTheTransaction(): iterable
    {
        yield 'deadlock' => [1213, 'Deadlock found when trying to get lock; try restarting transaction'];
        yield 'MariaDB snapshot isolation conflict' => [1020, "Record has changed since last read in table 'log_visit'"];
        yield 'lock table full' => [1206, 'The total number of locks exceeds the lock table size'];
        yield 'group replication rollback' => [3101, 'Plugin instructed the server to rollback the current transaction.'];
        yield 'commit failed' => [1180, "Got error 149 - 'Lock deadlock; Retry transaction' during COMMIT"];
    }

    public function testRollBackAfterTheTransactionWasEndedDoesNotThrow()
    {
        $db = Tracker\Db::connectPiwikTrackerDb();
        $xid = $db->beginTransaction();
        $this->insertOption($db, 'before_error');
        $this->raiseError($db, 1213, 'Deadlock found when trying to get lock; try restarting transaction');

        $db->rollBack($xid);

        $this->assertFalse($db->isTransactionAborted());
        $this->assertOptionsStored([]);
        $this->assertNewTransactionWorks();
    }

    public function testFailedCommitLeavesTheConnectionReadyForTheNextTransaction()
    {
        $db = Tracker\Db::connectPiwikTrackerDb();
        if (!$db instanceof Tracker\Db\Pdo\Mysql) {
            $this->markTestSkipped('Only PDO keeps its own record of whether a transaction is open');
        }
        $this->makeNextCommitFail($db);

        $xid = $db->beginTransaction();
        $this->insertOption($db, 'before_error');
        $e = $this->catchException(function () use ($db, $xid) {
            $db->commit($xid);
        });

        $this->assertStringContainsString(' 1180 ', $e->getMessage());

        $xid = $db->beginTransaction();
        $this->insertOption($db, 'next_transaction');
        $db->commit($xid);

        $this->assertOptionsStored(['next_transaction']);
    }

    public function testErrorWithoutAnErrorNumberIgnoresNumbersInTheParameters()
    {
        $db = Tracker::getDatabase();
        $xid = $db->beginTransaction();

        $e = null;
        try {
            // fails in the client, so the message has no error number of its own
            $db->query("INSERT INTO `{$this->tableName}` (option_name, option_value, autoload) VALUES (?, ?, ?)", ['x 1213 y', '1']);
        } catch (\Throwable $e) {
        }
        $this->insertOption($db, 'after_error');
        $db->commit($xid);

        $this->assertNotNull($e);
        $this->assertFalse($db->isTransactionAborted());
        $this->assertOptionsStored(['after_error']);
    }

    /**
     * @dataProvider getErrorsThatDoNotEndTheTransaction
     */
    public function testErrorThatDoesNotEndTheTransactionKeepsItUsable(int $errorNumber, string $message)
    {
        $db = Tracker\Db::connectPiwikTrackerDb();
        $xid = $db->beginTransaction();
        $this->insertOption($db, 'before_error');

        $error = $this->raiseError($db, $errorNumber, $message);
        $this->assertTrue($db->isErrNo($error, $errorNumber));
        $this->assertFalse($db->isTransactionAborted());

        $this->insertOption($db, 'after_error');
        $db->commit($xid);

        $this->assertOptionsStored(['after_error', 'before_error']);
    }

    public function getErrorsThatDoNotEndTheTransaction(): iterable
    {
        yield 'lock wait timeout' => [1205, 'Lock wait timeout exceeded; try restarting transaction'];
        yield 'unknown column' => [1054, "Unknown column 'items' in 'field list'"];
    }

    public function testErrorOutsideOfATransactionDoesNotStopLaterQueries()
    {
        $db = Tracker\Db::connectPiwikTrackerDb();

        $this->raiseError($db, 1213, 'Deadlock found when trying to get lock; try restarting transaction');
        $this->assertFalse($db->isTransactionAborted());

        $this->insertOption($db, 'after_error');
        $this->assertOptionsStored(['after_error']);
    }

    public function testRealDeadlockStopsEveryLaterQuery()
    {
        foreach (['deadlock_a', 'deadlock_b', 'deadlock_pad_1', 'deadlock_pad_2', 'deadlock_pad_3'] as $name) {
            $this->insertOption(Tracker::getDatabase(), $name);
        }
        $update = "UPDATE `{$this->tableName}` SET option_value = ? WHERE option_name = ?";

        $other = $this->connectWithMysqli();
        $other->query('START TRANSACTION');
        // the server rolls back the transaction that changed fewer rows, which makes the tracker connection the victim
        $other->query("UPDATE `{$this->tableName}` SET option_value = 'other' WHERE option_name IN ('deadlock_pad_1', 'deadlock_pad_2', 'deadlock_pad_3')");
        $other->query("UPDATE `{$this->tableName}` SET option_value = 'other' WHERE option_name = 'deadlock_b'");

        $db = Tracker\Db::connectPiwikTrackerDb();
        $xid = $db->beginTransaction();
        $db->query($update, ['tracker', 'deadlock_a']);

        // waits for the tracker connection without blocking this process
        $other->query("UPDATE `{$this->tableName}` SET option_value = 'other' WHERE option_name = 'deadlock_a'", MYSQLI_ASYNC);
        usleep(300 * 1000);

        $deadlock = $this->catchException(function () use ($db, $update) {
            $db->query($update, ['tracker', 'deadlock_b']);
        }, false);

        $other->reap_async_query();
        $other->query('ROLLBACK');
        $other->close();

        if ($deadlock === null) {
            $db->rollBack($xid);
            $this->markTestSkipped('The server rolled back the other transaction, so there was no deadlock to check');
        }

        $this->assertTrue($db->isErrNo($deadlock, 1213));
        $this->assertTrue($db->isTransactionAborted());

        $this->catchException(function () use ($db) {
            $this->insertOption($db, 'after_error');
        });

        $db->rollBack($xid);

        $this->assertSame('1', Tracker::getDatabase()->fetchOne(
            "SELECT option_value FROM `{$this->tableName}` WHERE option_name = 'deadlock_a'"
        ));
        $this->assertNewTransactionWorks();
    }

    public function testErrorWhoseParametersMentionADeadlockKeepsTheTransactionUsable()
    {
        $db = Tracker::getDatabase();
        $this->insertOption($db, 'Deadlock found when trying to get lock');

        $xid = $db->beginTransaction();
        $duplicate = $this->catchException(function () use ($db) {
            $this->insertOption($db, 'Deadlock found when trying to get lock');
        });
        $this->insertOption($db, 'after_error');
        $db->commit($xid);

        $this->assertTrue($db->isErrNo($duplicate, 1062));
        $this->assertFalse($db->isTransactionAborted());
        $this->assertOptionsStored(['after_error']);
    }

    public function testCommitReportsTheAbortEvenIfTheRollbackFails()
    {
        $db = Tracker\Db::connectPiwikTrackerDb();
        $connectionId = $db->fetchOne('SELECT CONNECTION_ID()');
        $xid = $db->beginTransaction();
        $this->raiseError($db, 1213, 'Deadlock found when trying to get lock; try restarting transaction');

        // the rollback inside commit() fails on a closed connection
        Db::exec('KILL ' . (int) $connectionId);

        $e = $this->catchException(function () use ($db, $xid) {
            $db->commit($xid);
        });

        $this->assertSame(Tracker\Db::TRANSACTION_ABORTED_MESSAGE, $e->getMessage());
        $this->assertSame(1213, $e->getCode());
    }

    public function testRealSnapshotIsolationConflictStopsEveryLaterQuery()
    {
        $db = Tracker\Db::connectPiwikTrackerDb();
        try {
            $db->query('SET SESSION innodb_snapshot_isolation = ON');
        } catch (\Exception $e) {
            $this->markTestSkipped('Needs MariaDB 10.6.18 or later, which has innodb_snapshot_isolation');
        }

        $this->insertOption($db, 'snapshot_row');
        $update = "UPDATE `{$this->tableName}` SET option_value = ? WHERE option_name = 'snapshot_row'";

        $xid = $db->beginTransaction();
        // fixes the transaction's snapshot
        $db->fetchOne("SELECT option_value FROM `{$this->tableName}` WHERE option_name = 'snapshot_row'");

        $other = $this->connectWithMysqli();
        $other->query("UPDATE `{$this->tableName}` SET option_value = 'other' WHERE option_name = 'snapshot_row'");
        $other->close();

        $conflict = $this->catchException(function () use ($db, $update) {
            $db->query($update, ['tracker']);
        });

        $this->assertTrue($db->isErrNo($conflict, 1020));
        $this->assertTrue($db->isTransactionAborted());

        $this->catchException(function () use ($db) {
            $this->insertOption($db, 'after_error');
        });
        $this->catchException(function () use ($db, $xid) {
            $db->commit($xid);
        });

        $this->assertSame('other', Tracker::getDatabase()->fetchOne(
            "SELECT option_value FROM `{$this->tableName}` WHERE option_name = 'snapshot_row'"
        ));
        $this->assertOptionsStored([]);
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
            'option_value' => '3',
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
            'option_value' => '3',
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
                'option_value' => '3',
            ),
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

    /**
     * Swaps the adapter's PDO for one whose next commit fails the way the server reports it, e.g. when a cluster rejects
     * the transaction. Such an error leaves PDO believing the transaction is still open.
     */
    private function makeNextCommitFail(Tracker\Db\Pdo\Mysql $db): void
    {
        $read = function (string $name) use ($db) {
            $property = new \ReflectionProperty(Tracker\Db\Pdo\Mysql::class, $name);
            $property->setAccessible(true);
            return $property->getValue($db);
        };

        $pdo = new class ($read('dsn'), $read('username'), $read('password'), $read('mysqlOptions')) extends \PDO {
            private $failCommit = true;

            public function commit(): bool
            {
                if ($this->failCommit) {
                    $this->failCommit = false;
                    throw new \PDOException('SQLSTATE[HY000]: General error: 1180 Got error 149 during COMMIT');
                }
                return parent::commit();
            }
        };

        $connection = new \ReflectionProperty(Tracker\Db\Pdo\Mysql::class, 'connection');
        $connection->setAccessible(true);
        $connection->setValue($db, $pdo);
    }

    private function raiseError(Tracker\Db $db, int $errorNumber, string $message): \Exception
    {
        return $this->catchException(function () use ($db, $errorNumber, $message) {
            DbErrorRaiser::raise($db, $errorNumber, $message);
        });
    }

    private function catchException(callable $callback, bool $required = true): ?\Exception
    {
        try {
            $callback();
        } catch (DbException $e) {
            return $e;
        }

        if ($required) {
            $this->fail('Expected a database error');
        }

        return null;
    }

    private function insertOption(Tracker\Db $db, string $name): void
    {
        $db->query("INSERT INTO `{$this->tableName}` (option_name, option_value, autoload) VALUES (?, '1', 0)", [$name]);
    }

    private function assertOptionsStored(array $expected): void
    {
        $names = Tracker::getDatabase()->fetchAll(
            "SELECT option_name FROM `{$this->tableName}` WHERE option_name IN ('before_error', 'after_error', 'next_transaction') ORDER BY option_name"
        );
        $this->assertSame($expected, array_column($names, 'option_name'));
    }

    private function assertNewTransactionWorks(): void
    {
        $db = Tracker::getDatabase();
        $xid = $db->beginTransaction();
        $this->insertOption($db, 'next_transaction');
        $db->commit($xid);

        $this->assertSame('1', Tracker::getDatabase()->fetchOne(
            "SELECT option_value FROM `{$this->tableName}` WHERE option_name = 'next_transaction'"
        ));
    }

    private function connectWithMysqli(): \mysqli
    {
        $config = Config::getInstance()->database;
        mysqli_report(MYSQLI_REPORT_OFF);

        return new \mysqli($config['host'], $config['username'], $config['password'], $config['dbname'], (int) $config['port']);
    }

    private function insertRowId($value = '1')
    {
        $db = Tracker::getDatabase();
        return $db->query("INSERT INTO `" . $this->tableName . "` VALUES ('rowid', '$value', false)");
    }
}
