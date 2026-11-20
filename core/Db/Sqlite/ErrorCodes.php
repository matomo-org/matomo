<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Db\Sqlite;

use PDOException;
use Piwik\Db\TransactionErrors;
use Piwik\Updater\Migration\Db as Migration;

/**
 * Maps SQLite errors to the MySQL error numbers Matomo tests for with `isErrNo()`.
 *
 * Constraint violations share the result code `SQLITE_CONSTRAINT` (19, SQLSTATE 23000 as on MySQL)
 * and schema errors the code `SQLITE_ERROR` (1); both are told apart by the start of SQLite's
 * message, which is the only text read: what follows it (e.g. the query and its parameters) is
 * never matched.
 *
 * @internal
 */
final class ErrorCodes
{
    private const BY_CODE = [
        5 => [TransactionErrors::ERROR_CODE_LOCK_WAIT_TIMEOUT], // SQLITE_BUSY
        6 => [TransactionErrors::ERROR_CODE_LOCK_WAIT_TIMEOUT], // SQLITE_LOCKED
    ];

    /**
     * Messages of SQLITE_CONSTRAINT (19), by the MySQL errors they correspond to.
     */
    private const CONSTRAINT_BY_MESSAGE = [
        '/^UNIQUE constraint failed: /' => [Migration::ERROR_CODE_DUPLICATE_ENTRY],
        '/^NOT NULL constraint failed: /' => [1048], // column cannot be null
    ];

    /**
     * Messages of SQLITE_ERROR (1), by the MySQL errors they correspond to.
     */
    private const BY_MESSAGE = [
        '/^no such table: /' => [Migration::ERROR_CODE_TABLE_NOT_EXISTS, Migration::ERROR_CODE_UNKNOWN_TABLE],
        '/^table \S+ already exists/' => [Migration::ERROR_CODE_TABLE_EXISTS],
        '/^duplicate column name: /' => [Migration::ERROR_CODE_DUPLICATE_COLUMN],
        // MySQL reports a column to drop that doesn't exist as 1091; SQLite quotes its name only then
        '/^no such column: "/' => [Migration::ERROR_CODE_COLUMN_NOT_EXISTS],
        // and an unknown column in a query as 1054, in an index as 1072
        '/^no such column: /' => [Migration::ERROR_CODE_UNKNOWN_COLUMN, Migration::ERROR_CODE_KEY_COLUMN_NOT_EXISTS],
        '/^table \S+ has no column named /' => [Migration::ERROR_CODE_UNKNOWN_COLUMN],
        '/^index \S+ already exists/' => [Migration::ERROR_CODE_DUPLICATE_KEY],
        '/^no such index: /' => [Migration::ERROR_CODE_COLUMN_NOT_EXISTS],
        '/^near .*: syntax error/' => [Migration::ERROR_CODE_SYNTAX_ERROR],
        '/^incomplete input/' => [Migration::ERROR_CODE_SYNTAX_ERROR],
    ];

    /**
     * Whether the exception is the SQLite error that corresponds to the MySQL error number.
     *
     * @param \Throwable $e
     * @param int|string $errno
     */
    public static function isErrNo($e, $errno): bool
    {
        return in_array((int) $errno, self::getMysqlErrorNumbers($e), true);
    }

    /**
     * @return int[] the MySQL error numbers the exception corresponds to, none if it isn't a known SQLite error
     */
    public static function getMysqlErrorNumbers(\Throwable $e): array
    {
        $error = self::getSqliteError($e);
        if ($error === null) {
            return [];
        }

        [$code, $message] = $error;
        if (isset(self::BY_CODE[$code])) {
            return self::BY_CODE[$code];
        }

        $messages = $code === 1 ? self::BY_MESSAGE : ($code === 19 ? self::CONSTRAINT_BY_MESSAGE : []);
        foreach ($messages as $pattern => $numbers) {
            if (preg_match($pattern, $message)) {
                return $numbers;
            }
        }

        return [];
    }

    /**
     * Returns the SQLite result code and message of the exception or of the PDO exception it wraps.
     *
     * @return array{0: int, 1: string}|null
     */
    private static function getSqliteError(\Throwable $e): ?array
    {
        for ($cause = $e; $cause !== null; $cause = $cause->getPrevious()) {
            if ($cause instanceof PDOException && isset($cause->errorInfo[1]) && is_int($cause->errorInfo[1])) {
                return [$cause->errorInfo[1], (string) ($cause->errorInfo[2] ?? '')];
            }
        }

        // the message PDO builds, e.g. "SQLSTATE[HY000]: General error: 1 no such table: x", possibly wrapped in another message
        if (preg_match('/SQLSTATE\[[0-9A-Z]{5}\]:[^:]*: (\d+) (.*)$/s', $e->getMessage(), $matches)) {
            return [(int) $matches[1], $matches[2]];
        }

        return null;
    }
}
