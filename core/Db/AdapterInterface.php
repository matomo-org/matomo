<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Db;

use Exception;

/**
 * Common properies and methods that must be implemented by all Matomo database adapters
 *
 * Should only contain code that will apply for all database types, code specific to one database should be placed in
 * that adapter's class in \Db\Adapter\Pdo\[dbclass]
 */
interface AdapterInterface
{

    /**
     * Reset the configuration variables in this adapter.
     */
    public function resetConfig(): void;

    /**
     * Return default port.
     *
     * @return int
     */
    public static function getDefaultPort(): int;

    /**
     * Check database server version
     *
     * @return void
     * @throws Exception if database version is less than required version
     */
    public function checkServerVersion(): void;

    /**
     * Returns true if this adapter is a recommended choice
     *
     * @return bool
     */
    public static function isRecommendedAdapter(): bool;

    /**
     * Returns true if this adapter's required extensions are enabled
     *
     * @return bool
     */
    public static function isEnabled(): bool;

    /**
     * Returns true if this adapter supports blobs as fields
     *
     * @return bool
     */
    public function hasBlobDataType(): bool;

    /**
     * Returns true if this adapter supports bulk loading
     *
     * @return bool
     */
    public function hasBulkLoader(): bool;

    /**
     * Test error number
     *
     * @param Exception $e
     * @param string    $errno
     *
     * @return bool     True if the error is valid for this adapter
     */
    public function isErrNo(Exception $e, string $errno): bool;

    /**
     * Return number of affected rows in last query
     *
     * @param mixed $queryResult Result from query()
     *
     * @return int
     */
    public function rowCount($queryResult): int;

    /**
     * Check client version compatibility against database server
     *
     * @return void
     * @throws Exception
     *
     */
    public function checkClientVersion(): void;

    /**
     * Allow any adapter specific read session parameters to be set when the connection is created
     *
     * @param array $dbConfig An array of all database configuration settings
     *
     * @return void
     * @throws Exception
     *
     */
    public function setReaderSessionParameters($dbConfig): void;

    /**
     * Runs an `OPTIMIZE TABLE` query on the supplied table or tables.
     *
     * Tables will only be optimized if the `[General] enable_sql_optimize_queries` INI config option is
     * set to **1**.
     *
     * @param string|array $tables The name of the table to optimize or an array of tables to optimize.
     *                             Table names must be prefixed (see {@link Piwik\Common::prefixTable()}).
     * @param bool         $force  If true, the `OPTIMIZE TABLE` query will be run even if InnoDB tables are being used.
     *
     * @return bool
     */
    public static function optimizeTables($tables, bool $force): bool;

    /**
     * Attempts to get a named lock. This function uses a timeout of 1s, but will
     * retry a set number of times.
     *
     * @param string $lockName The lock name.
     * @param int $maxRetries The max number of times to retry.
     * @return bool `true` if the lock was obtained, `false` if otherwise.
     * @throws \Exception if Lock name is too long
     */
    public static function getDbLock(string $lockName, int $maxRetries = 30): bool;

    /**
     * Releases a named lock.
     *
     * @param string $lockName The lock name.
     * @return bool `true` if the lock was released, `false` if otherwise.
     */
    public static function releaseDbLock(string $lockName): bool;

 /**
     * Locks the supplied table or tables.
     *
     * **NOTE:** Matomo does not require the `LOCK TABLES` privilege to be available. Matomo
     * should still work if it has not been granted.
     *
     * @param array $tablesToRead The table or tables to obtain 'read' locks on. Table names must
     *                                   be prefixed (see {@link Piwik\Common::prefixTable()}).
     * @param array $tablesToWrite The table or tables to obtain 'write' locks on. Table names must
     *                                    be prefixed (see {@link Piwik\Common::prefixTable()}).
     * @return void
     */
    public static function lockTables(array $tablesToRead, array $tablesToWrite = []): void;

    /**
     * Releases all table locks.
     *
     * **NOTE:** Matomo does not require the `LOCK TABLES` privilege to be available. It
     * should still work if this has not been granted.
     *
     * @return void
     */
    public static function unlockAllTables(): void;


    /**
     * Log additional information for deadlock exceptions
     *
     * @param $ex
     *
     * @return void
     */
    public static function logExtraInfoIfDeadlock($ex): void;

    /**
     * Intercepts certain exception messages and replaces leaky ones with ones that don't reveal too much info
     *
     * @param string $message
     *
     * @return string
     */
    public static function overriddenExceptionMessage(string $message): string;

    /**
     * Indicates whether the adapter / database is likely to allow setting the transaction level
     *
     * @return bool
     */
    public static function canLikelySetTransactionLevel(): bool;

    /**
     * Get the current transaction isolation level
     *
     * @return null|string
     */
    public static function getTransationIsolationLevel(): ?string;

    /**
     * Set the transaction isolation level to read uncommitted
     *
     * @return void
     */
    public static function setTransactionIsolationLevelReadUncommitted(): void;

    /**
     * Set the transaction isolation level to read uncommitted
     *
     * @param string $previous The previous transaction isolation status result
     *
     * @return void
     */
    public static function restorePreviousTransactionIsolationLevel(string $previous): void;

    /**
     * Returns the default database charset to use
     *
     * @return string
     */
    public static function getDefaultCharset(): string;

    /**
     * Returns sql queries to convert all installed tables to utf8mb4
     *
     * @return array
     */
    public static function getUtf8mb4ConversionQueries(): array;

    /**
     * Adds a MAX_EXECUTION_TIME hint into a SELECT query if $limit is bigger than 1
     *
     * @param string $sql
     * @param int    $limit
     *
     * @return string
     */
    public static function addMaxExecutionTimeHintToQuery(string $sql, int $limit): string;

}
