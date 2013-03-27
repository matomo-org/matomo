<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */

/**
 * Database schema interface
 *
 * @package Piwik
 * @subpackage Piwik_Db
 */
interface Piwik_Db_Schema_Interface
{
    /**
     * Is this schema available?
     *
     * @return bool  True if schema is available; false otherwise
     */
    static public function isAvailable();

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
     * Create database
     *
     * @param string $dbName  Name of the database to create
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
     * Drop specific tables
     *
     * @param array $doNotDelete  Names of tables to not delete
     */
    public function dropTables($doNotDelete = array());

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
     * @param bool $forceReload  Invalidate cache
     * @return array  installed Tables
     */
    public function getTablesInstalled($forceReload = true);

    /**
     * Checks whether any table exists
     *
     * @return bool  True if tables exist; false otherwise
     */
    public function hasTables();
}
