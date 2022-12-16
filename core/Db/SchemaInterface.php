<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
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
     *
     * @return string SQL
     */
    public function getTableCreateSql(string $tableName): string;

    /**
     * Get the SQL to create Matomo tables
     *
     * @return array  array of strings containing SQL
     */
    public function getTablesCreateSql(): array;

    /**
     * Creates a new table in the database.
     *
     * @param string $nameWithoutPrefix   The name of the table without any piwik prefix.
     * @param string $createDefinition    The table create definition
     *
     * @return void
     */
    public function createTable(string $nameWithoutPrefix, string $createDefinition): void;

    /**
     * Create database
     *
     * @param string|null $dbName Name of the database to create
     *
     * @return void
     */
    public function createDatabase(?string $dbName = null): void;

    /**
     * Drop database
     *
     * @return void
     */
    public function dropDatabase(): void;

    /**
     * Create all tables
     *
     * @return void
     */
    public function createTables(): void;

    /**
     * Creates an entry in the User table for the "anonymous" user.
     *
     * @return void
     */
    public function createAnonymousUser(): void;

    /**
     * Records the Matomo version a user used when installing this Matomo for the first time
     *
     * @return void
     */
    public function recordInstallVersion(): void;

    /**
     * Returns which Matomo version was used to install this Matomo for the first time.
     *
     * @return string
     */
    public function getInstallVersion(): string;

    /**
     * Truncate all tables
     *
     * @return void
     */
    public function truncateAllTables(): void;

    /**
     * Names of all the prefixed tables in piwik
     * Doesn't use the DB
     *
     * @return array  Table names
     */
    public function getTablesNames(): array;

    /**
     * Get list of tables installed
     *
     * @param bool $forceReload Invalidate cache
     *
     * @return array  installed Tables
     */
    public function getTablesInstalled(bool $forceReload = true): array;

    /**
     * Get list of installed columns in a table
     *
     * @param  string $tableName The name of a table.
     *
     * @return array  Installed columns indexed by the column name.
     */
    public function getTableColumns(string $tableName): array;

    /**
     * Checks whether any table exists
     *
     * @return bool  True if tables exist; false otherwise
     */
    public function hasTables(): bool;
}
