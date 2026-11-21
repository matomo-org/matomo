<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Framework;

use Piwik\Db;
use PHPUnit\Framework\Assert;

/**
 * Makes the database server return any error number, so tests can check how a given error is handled without
 * producing it for real. The server only reports the error: unlike a real deadlock it does not roll anything back.
 *
 * MySQL error numbers only: on SQLite nothing is installed, and a test raising an error is skipped.
 */
class DbErrorRaiser
{
    private const PROCEDURE = 'matomo_tests_raise_error';

    /**
     * Run before starting a transaction, creating a procedure commits the open one.
     */
    public static function install(): void
    {
        if (Fixture::isSqlite()) {
            return;
        }

        self::uninstall();
        Db::exec('CREATE PROCEDURE ' . self::PROCEDURE . "(IN errorNumber INT, IN errorMessage VARCHAR(255))
            SIGNAL SQLSTATE 'HY000' SET MYSQL_ERRNO = errorNumber, MESSAGE_TEXT = errorMessage");
    }

    public static function uninstall(): void
    {
        if (Fixture::isSqlite()) {
            return;
        }

        Db::exec('DROP PROCEDURE IF EXISTS ' . self::PROCEDURE);
    }

    /**
     * @param \Piwik\Tracker\Db $db
     */
    public static function raise($db, int $errorNumber, string $message): void
    {
        if (Fixture::isSqlite()) {
            Assert::markTestSkipped('Raises a MySQL error number');
        }

        $db->query('CALL ' . self::PROCEDURE . '(?, ?)', [$errorNumber, $message]);
    }

    /**
     * SQLite has no error numbers to raise. This ends the open transaction the way SQLite itself does on a full
     * disk or an I/O error: a trigger rolls it back and the statement fails.
     *
     * @param \Piwik\Tracker\Db $db
     */
    public static function endTransactionOnSqlite($db): void
    {
        $db->query('CREATE TEMP TABLE IF NOT EXISTS matomo_test_end_transaction (id INTEGER)');
        $db->query('CREATE TEMP TRIGGER IF NOT EXISTS matomo_test_end_transaction BEFORE INSERT ON matomo_test_end_transaction'
            . " BEGIN SELECT RAISE(ROLLBACK, 'Transaction rolled back by the test'); END");
        $db->query('INSERT INTO matomo_test_end_transaction VALUES (1)');
    }
}
