<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Db\Sqlite;

use PDO;
use PDOException;
use PHPUnit\Framework\TestCase;
use Piwik\Db\Sqlite\ErrorCodes;
use Zend_Db_Statement_Exception;

/**
 * The SQLite errors Matomo tests for, raised by a real SQLite database, map to the MySQL error numbers.
 */
class ErrorCodesTest extends TestCase
{
    use RequiresSqlite;

    public function setUp(): void
    {
        parent::setUp();
        $this->requireSqlite();
    }

    /**
     * @dataProvider getErrors
     */
    public function testSqliteErrorsMapToMysqlErrorNumbers(string $sql, array $expected): void
    {
        $pdo = new PDO('sqlite::memory:', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $pdo->exec('CREATE TABLE t (id INTEGER PRIMARY KEY, name TEXT NOT NULL UNIQUE)');
        $pdo->exec('CREATE INDEX t__name_idx ON t (name)');
        $pdo->exec("INSERT INTO t VALUES (1, 'a')");

        try {
            $pdo->exec($sql);
            $this->fail('no exception');
        } catch (PDOException $e) {
            $this->assertSame($expected, ErrorCodes::getMysqlErrorNumbers($e), $e->getMessage());
            // an adapter wraps the PDO exception
            $wrapped = new Zend_Db_Statement_Exception($e->getMessage(), (int) $e->getCode(), $e);
            $this->assertSame($expected, ErrorCodes::getMysqlErrorNumbers($wrapped));
            // only the message is left, e.g. after Matomo rethrows it in its own exception
            $this->assertSame($expected, ErrorCodes::getMysqlErrorNumbers(new \Exception('Error query: ' . $e->getMessage())));
        }
    }

    public function getErrors(): iterable
    {
        yield 'duplicate key' => ["INSERT INTO t VALUES (2, 'a')", [1062]];
        yield 'duplicate primary key' => ["INSERT INTO t VALUES (1, 'b')", [1062]];
        yield 'not null' => ['INSERT INTO t VALUES (2, NULL)', [1048]];
        yield 'no such table' => ['SELECT * FROM missing', [1146, 1051]];
        yield 'table exists' => ['CREATE TABLE t (a INT)', [1050]];
        yield 'duplicate column' => ['ALTER TABLE t ADD COLUMN name TEXT', [1060]];
        yield 'no such column' => ['SELECT missing FROM t', [1054, 1072]];
        yield 'drop a missing column' => ['ALTER TABLE t DROP COLUMN missing', [1091]];
        yield 'insert into a missing column' => ['INSERT INTO t (missing) VALUES (1)', [1054]];
        yield 'index on a missing column' => ['CREATE INDEX t__missing ON t (missing)', [1054, 1072]];
        yield 'index exists' => ['CREATE INDEX t__name_idx ON t (name)', [1061]];
        yield 'no such index' => ['DROP INDEX missing', [1091]];
        yield 'syntax error' => ['SELEC 1', [1064]];
        yield 'incomplete input' => ['SELECT (1', [1064]];
    }

    public function testBusyAndLockedAreLockWaitTimeouts(): void
    {
        foreach ([5 => 'database is locked', 6 => 'database table is locked'] as $code => $message) {
            $e = new PDOException("SQLSTATE[HY000]: General error: $code $message");
            $e->errorInfo = ['HY000', $code, $message];

            $this->assertSame([1205], ErrorCodes::getMysqlErrorNumbers($e));
            $this->assertTrue(ErrorCodes::isErrNo($e, '1205'));
        }
    }

    public function testQueryTextIsNotMatched(): void
    {
        $e = new \Exception("SQLSTATE[HY000]: General error: 1 no such function: X In query: SELECT 'no such table: y'");

        $this->assertSame([], ErrorCodes::getMysqlErrorNumbers($e));
    }

    public function testOtherExceptionsMapToNothing(): void
    {
        $this->assertSame([], ErrorCodes::getMysqlErrorNumbers(new \Exception('Duplicate entry')));
        $this->assertFalse(ErrorCodes::isErrNo(new \Exception('SQLSTATE[23000]: 1062 Duplicate entry'), 1062));
    }
}
