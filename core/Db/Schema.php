<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Db;

use Piwik\Config;
use Piwik\Singleton;

/**
 * Schema abstraction
 *
 * @method static \Piwik\Db\Schema getInstance()
 */
class Schema extends Singleton
{
    public const DEFAULT_SCHEMA = 'Mysql';

    /**
     * Type of database schema
     *
     * @var SchemaInterface
     */
    private $schema = null;

    /**
     * Get schema class name
     *
     * @param string $schemaName
     * @return string
     */
    private static function getSchemaClassName($schemaName): string
    {
        // Upgrade from pre 2.0.4
        if (
            strtolower($schemaName) == 'myisam'
            || empty($schemaName)
        ) {
            $schemaName = self::DEFAULT_SCHEMA;
        }

        $class = str_replace(' ', '\\', ucwords(str_replace('_', ' ', strtolower($schemaName))));
        return '\Piwik\Db\Schema\\' . $class;
    }

    /**
     * Return the default port for the provided database schema
     *
     * @param string $schemaName
     * @return int
     */
    public static function getDefaultPortForSchema(string $schemaName): int
    {
        $schemaClassName = self::getSchemaClassName($schemaName);
        /** @var SchemaInterface $schemaClass */
        $schemaClass = new $schemaClassName();
        return $schemaClass->getDefaultPort();
    }

    /**
     * Load schema
     */
    private function loadSchema(): void
    {
        $config     = Config::getInstance();
        $dbInfos    = $config->database;
        $schemaName = trim($dbInfos['schema']);

        $className    = self::getSchemaClassName($schemaName);
        $this->schema = new $className();
    }

    /**
     * Returns an instance that subclasses Schema
     *
     * @return SchemaInterface
     */
    private function getSchema(): SchemaInterface
    {
        if ($this->schema === null) {
            $this->loadSchema();
        }

        return $this->schema;
    }

    /**
     * Get the table options to use for a CREATE TABLE statement.
     *
     * @return string
     */
    public function getTableCreateOptions(): string
    {
        return $this->getSchema()->getTableCreateOptions();
    }

    /**
     * Get the SQL to create a specific Piwik table
     *
     * @param string $tableName name of the table to create
     * @return string  SQL
     */
    public function getTableCreateSql($tableName)
    {
        return $this->getSchema()->getTableCreateSql($tableName);
    }

    /**
     * Get the SQL to create Piwik tables
     *
     * @return array   array of strings containing SQL
     */
    public function getTablesCreateSql()
    {
        return $this->getSchema()->getTablesCreateSql();
    }

    /**
     * Creates a new table in the database.
     *
     * @param string $nameWithoutPrefix   The name of the table without any prefix.
     * @param string $createDefinition    The table create definition
     */
    public function createTable($nameWithoutPrefix, $createDefinition)
    {
        $this->getSchema()->createTable($nameWithoutPrefix, $createDefinition);
    }

    /**
     * Create database
     *
     * @param null|string $dbName database name to create
     */
    public function createDatabase($dbName = null)
    {
        $this->getSchema()->createDatabase($dbName);
    }

    /**
     * Drop database
     */
    public function dropDatabase($dbName = null)
    {
        $this->getSchema()->dropDatabase($dbName);
    }

    /**
     * Create all tables
     */
    public function createTables(): void
    {
        $this->getSchema()->createTables();
    }

    /**
     * Creates an entry in the User table for the "anonymous" user.
     */
    public function createAnonymousUser(): void
    {
        $this->getSchema()->createAnonymousUser();
    }

    /**
     * Records the Matomo version a user used when installing this Matomo for the first time
     */
    public function recordInstallVersion(): void
    {
        $this->getSchema()->recordInstallVersion();
    }

    /**
     * Returns which Matomo version was used to install this Matomo for the first time.
     */
    public function getInstallVersion()
    {
        return $this->getSchema()->getInstallVersion();
    }

    /**
     * Truncate all tables
     */
    public function truncateAllTables(): void
    {
        $this->getSchema()->truncateAllTables();
    }

    /**
     * Names of all the prefixed tables in Matomo
     * Doesn't use the DB
     *
     * @return array Table names
     */
    public function getTablesNames()
    {
        return $this->getSchema()->getTablesNames();
    }

    /**
     * Get list of tables installed
     *
     * @param bool $forceReload Invalidate cache
     * @return array  installed tables
     */
    public function getTablesInstalled($forceReload = true)
    {
        return $this->getSchema()->getTablesInstalled($forceReload);
    }

    /**
     * Get list of installed columns in a table
     *
     * @param  string $tableName The name of a table.
     *
     * @return array  Installed columns indexed by the column name.
     */
    public function getTableColumns($tableName)
    {
        return $this->getSchema()->getTableColumns($tableName);
    }

    /**
     * Returns true if Matomo tables exist
     *
     * @return bool  True if tables exist; false otherwise
     */
    public function hasTables()
    {
        return $this->getSchema()->hasTables();
    }

    /**
     * Adds a MAX_EXECUTION_TIME hint into a SELECT query if $limit is bigger than 0
     *
     * @param string $sql  query to add hint to
     * @param float $limit  time limit in seconds
     * @return string
     */
    public function addMaxExecutionTimeHintToQuery(string $sql, float $limit): string
    {
        return $this->getSchema()->addMaxExecutionTimeHintToQuery($sql, $limit);
    }

    /**
     * Returns if the schema support complex column updates
     *
     * @return bool
     */
    public function supportsComplexColumnUpdates(): bool
    {
        return $this->getSchema()->supportsComplexColumnUpdates();
    }

    /**
     * Returns if the schema supports `OPTIMIZE TABLE` statements for innodb tables
     *
     * @return bool
     */
    public function isOptimizeInnoDBSupported(): bool
    {
        return $this->getSchema()->isOptimizeInnoDBSupported();
    }

    /**
     * Runs an `OPTIMIZE TABLE` query on the supplied table or tables.
     *
     * Tables will only be optimized if the `[General] enable_sql_optimize_queries` INI config option is
     * set to **1**.
     *
     * @param string|array $tables The name of the table to optimize or an array of tables to optimize.
     *                             Table names must be prefixed (see {@link Piwik\Common::prefixTable()}).
     * @param bool $force If true, the `OPTIMIZE TABLE` query will be run even if InnoDB tables are being used.
     * @return bool
     */
    public function optimizeTables(array $tables, bool $force = false): bool
    {
        return $this->getSchema()->optimizeTables($tables, $force);
    }
}
