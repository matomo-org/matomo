<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Db;

use PHPUnit\Framework\TestCase;
use Piwik\Db\Adapter\Pdo\Mysql;
use Piwik\Db\TransactionErrors;
use Piwik\Tracker\Db as TrackerDb;

class TransactionErrorsTest extends TestCase
{
    /**
     * @dataProvider getErrors
     */
    public function testClassifiesErrorsReportedByPdo(string $message, bool $endsTransaction, bool $isStatementOnly): void
    {
        $e = new \Exception($message);

        $this->assertSame($endsTransaction, TransactionErrors::endsTransaction($this->getPdoStyleDb(), $e));
        $this->assertSame($isStatementOnly, TransactionErrors::isStatementOnly($this->getPdoStyleDb(), $e));
    }

    public function getErrors(): iterable
    {
        yield 'deadlock' => ['SQLSTATE[40001]: Serialization failure: 1213 Deadlock found when trying to get lock; try restarting transaction', true, false];
        yield 'deadlock reported by Galera' => ['SQLSTATE[40001]: Serialization failure: 1213 Deadlock: wsrep aborted transaction', true, false];
        yield 'MariaDB snapshot isolation conflict' => ["SQLSTATE[HY000]: General error: 1020 Record has changed since last read in table 'log_conversion'", true, false];
        yield 'lock table full' => ['SQLSTATE[HY000]: General error: 1206 The total number of locks exceeds the lock table size', true, false];
        yield 'group replication rollback' => ['SQLSTATE[40000]: Transaction rollback: 3101 Plugin instructed the server to rollback the current transaction.', true, false];
        yield 'commit failed' => ["SQLSTATE[HY000]: General error: 1180 Got error 149 during COMMIT", true, false];
        yield 'lock wait timeout' => ['SQLSTATE[HY000]: General error: 1205 Lock wait timeout exceeded; try restarting transaction', false, true];
        yield 'other error' => ["SQLSTATE[42S22]: Column not found: 1054 Unknown column 'items' in 'field list'", false, false];
        yield 'transaction aborted by the tracker' => [TrackerDb::TRANSACTION_ABORTED_MESSAGE, true, false];
    }

    public function testFallsBackToTheMessageWhenTheErrorNumberIsLost(): void
    {
        // e.g. the mysqli adapter, which does not put the number into the message, after a caller rewrapped it
        $db = $this->getPdoStyleDb();

        $this->assertTrue(TransactionErrors::endsTransaction($db, new \Exception('Mysqli statement execute error : Deadlock found when trying to get lock; try restarting transaction')));
        $this->assertTrue(TransactionErrors::isStatementOnly($db, new \Exception('Error query: Lock wait timeout exceeded; try restarting transaction')));
        $this->assertFalse(TransactionErrors::endsTransaction($db, new \Exception('Error query: Duplicate entry')));
    }

    public function testDecidesByTheErrorNumberOnlyWhenAskedTo(): void
    {
        // the adapters append the query's parameters, which can hold any text
        $e = new \Exception("Error query: SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry '5' for key 'PRIMARY'"
            . " In query: INSERT INTO log_media (media_title) VALUES (?) Parameters: array ( 0 => 'Deadlock found when trying to get lock', )");
        $db = $this->getPdoStyleDb();

        $this->assertFalse(TransactionErrors::endsTransactionByErrorNumber($db, $e));
        $this->assertTrue(TransactionErrors::endsTransactionByErrorNumber($db, new \Exception(
            'SQLSTATE[40001]: Serialization failure: 1213 Deadlock: wsrep aborted transaction'
        )));
        $this->assertFalse(TransactionErrors::endsTransactionByErrorNumber($db, new \Exception(
            'Mysqli statement execute error : Deadlock found when trying to get lock; try restarting transaction'
        )));
    }

    private function getPdoStyleDb(): object
    {
        return new class {
            public function isErrNo($e, $errno): bool
            {
                return Mysql::isPdoErrorNumber($e, $errno);
            }
        };
    }
}
