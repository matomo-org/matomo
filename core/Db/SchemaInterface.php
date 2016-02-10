<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
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
     * Get the SQL to create a specific Piwik table
     *
     * @param string $tableName
     * @return string  SQL
     */
    public function getTableCreateSql($tableName);

    /**
     * Get the SQL to create Piwik tables
     *
     * @return array  array of strings containing SQL
     */
    public function getTablesCreateSql();

    /**
     * Creates a new table in the database.
     *
     * @param string $nameWithoutPrefix   The name of the table without any piwik prefix.
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
     * Truncate all tables
     */
    public function truncateAllTables();

    /**
     * Names of all the prefixed tables in piwik
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
}
