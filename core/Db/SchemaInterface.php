<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Db;

/**
 * Database schema interface
 */
interface SchemaInterface
{
    /**
     * Get the SQL to create a specific Matomo table
     *
     * @param string $tableName
     * @return string  SQL
     */
    public function getTableCreateSql($tableName);

    /**
     * Get the SQL to create Matomo tables
     *
     * @return array  array of strings containing SQL
     */
    public function getTablesCreateSql();

    /**
     * Creates a new table in the database.
     *
     * @param string $nameWithoutPrefix   The name of the table without any prefix.
     * @param string $createDefinition    The table create definition
     */
    public function createTable($nameWithoutPrefix, $createDefinition);

    /**
     * Create database
     *
     * @param string $dbName Name of the database to create
     */
    public function createDatabase($dbName = null);

    /**
     * Drop database
     */
    public function dropDatabase();

    /**
     * Create all tables
     */
    public function createTables();

    /**
     * Creates an entry in the User table for the "anonymous" user.
     */
    public function createAnonymousUser();

    /**
     * Records the Matomo version a user used when installing this Matomo for the first time
     */
    public function recordInstallVersion();

    /**
     * Returns which Matomo version was used to install this Matomo for the first time.
     */
    public function getInstallVersion();

    /**
     * Returns the supported read isolation transaction level
     *
     * For example:
     *      READ COMMITTED
     *      or
     *      READ UNCOMMITTED
     */
    public function getSupportedReadIsolationTransactionLevel(): string;

    /**
     * Truncate all tables
     */
    public function truncateAllTables();

    /**
     * Names of all the prefixed tables in Matomo
     * Doesn't use the DB
     *
     * @return array  Table names
     */
    public function getTablesNames();

    /**
     * Get list of tables installed
     *
     * @param bool $forceReload Invalidate cache
     * @return array  installed Tables
     */
    public function getTablesInstalled($forceReload = true);

    /**
     * Get list of installed columns in a table
     *
     * @param  string $tableName The name of a table.
     *
     * @return array  Installed columns indexed by the column name.
     */
    public function getTableColumns($tableName);

    /**
     * Checks whether any table exists
     *
     * @return bool  True if tables exist; false otherwise
     */
    public function hasTables();

    /**
     * Adds a max execution time query hint into a SELECT query if $limit is bigger than 0
     * (floating values for limit might be rounded to full seconds depending on DB support)
     *
     * @param string $sql  query to add hint to
     * @param float $limit  time limit in seconds
     * @return string
     */
    public function addMaxExecutionTimeHintToQuery(string $sql, float $limit): string;

    /**
     * Returns if the database supports column updates in table updates.
     * Some database engines are performing sanity checks for table updates. Those might include checking if all columns used
     * already exist. In such a case queries like this might fail: `ALTER TABLE t ADD COLUMN b, ADD INDEX i (b)`
     *
     * @return bool
     */
    public function supportsComplexColumnUpdates(): bool;

    /**
     * Returns the default collation for a charset used by this database engine.
     *
     * @param string $charset
     *
     * @return string
     */
    public function getDefaultCollationForCharset(string $charset): string;

    /**
     * Return the default port used by this database engine
     *
     * @return int
     */
    public function getDefaultPort(): int;

    /**
     * Return the table options to use for a CREATE TABLE statement.
     *
     * @return string
     */
    public function getTableCreateOptions(): string;

    /**
     * Returns if performing on `OPTIMIZE TABLE` is supported for InnoDb tables
     *
     * @return bool
     */
    public function isOptimizeInnoDBSupported(): bool;

    /**
     * Runs an `OPTIMIZE TABLE` query on the supplied table or tables.
     *
     * Tables will only be optimized if the `[General] enable_sql_optimize_queries` INI config option is
     * set to **1**.
     *
     * @param array $tables The name of the table to optimize or an array of tables to optimize.
     *                      Table names must be prefixed (see {@link Piwik\Common::prefixTable()}).
     * @param bool $force If true, the `OPTIMIZE TABLE` query will be run even if InnoDB tables are being used.
     * @return bool
     */
    public function optimizeTables(array $tables, bool $force = false): bool;

    /**
     * Returns if the database engine is able to use sorted subqueries
     *
     * @return bool
     */
    public function supportsSortingInSubquery(): bool;
}
