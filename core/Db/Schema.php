<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Db;

use Piwik\Config;
use Piwik\Singleton;

/**
 * Schema abstraction
 *
 * Note: no relation to the ZF proposals for Zend_Db_Schema_Manager
 *
 * @method static \Piwik\Db\Schema getInstance()
 */
class Schema extends Singleton
{
    const DEFAULT_SCHEMA = 'Mysql';

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
     *
     * @return string
     */
    private static function getSchemaClassName(string $schemaName): string
    {
        // Upgrade from pre 2.0.4
        if (strtolower($schemaName) == 'myisam' || empty($schemaName)) {
            $schemaName = self::DEFAULT_SCHEMA;
        }

        $class = str_replace(' ', '\\', ucwords(str_replace('_', ' ', strtolower($schemaName))));
        return '\Piwik\Db\Schema\\' . $class;
    }

    /**
     * Load schema
     *
     * @return void
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
     * Get the SQL to create a specific Matomo table
     *
     * @param string $tableName Name of the table to create
     *
     * @return string SQL
     */
    public function getTableCreateSql(string $tableName): string
    {
        return $this->getSchema()->getTableCreateSql($tableName);
    }

    /**
     * Get the SQL to create Matomo tables
     *
     * @return array Array of strings containing SQL
     */
    public function getTablesCreateSql(): array
    {
        return $this->getSchema()->getTablesCreateSql();
    }

    /**
     * Creates a new table in the database.
     *
     * @param string $nameWithoutPrefix   The name of the table without any piwik prefix.
     * @param string $createDefinition    The table create definition
     *
     * @return void
     */
    public function createTable(string $nameWithoutPrefix, string $createDefinition): void
    {
        $this->getSchema()->createTable($nameWithoutPrefix, $createDefinition);
    }

    /**
     * Create database
     *
     * @param null|string $dbName database name to create
     *
     * @return void
     */
    public function createDatabase(? string $dbName = null): void
    {
        $this->getSchema()->createDatabase($dbName);
    }

    /**
     * Drop database
     *
     * @param string|null $dbName
     *
     * @return void
     */
    public function dropDatabase(?string $dbName = null): void
    {
        $this->getSchema()->dropDatabase($dbName);
    }

    /**
     * Create all tables
     *
     * @return void
     */
    public function createTables(): void
    {
        $this->getSchema()->createTables();
    }

    /**
     * Creates an entry in the User table for the "anonymous" user.
     *
     * @return void
     */
    public function createAnonymousUser(): void
    {
        $this->getSchema()->createAnonymousUser();
    }

    /**
     * Records the Matomo version a user used when installing this Matomo for the first time
     *
     * @return void
     */
    public function recordInstallVersion(): void
    {
        $this->getSchema()->recordInstallVersion();
    }

    /**
     * Returns which Matomo version was used to install this Matomo for the first time.
     *
     * @return string|null Installed version
     */
    public function getInstallVersion(): ?string
    {
        return $this->getSchema()->getInstallVersion();
    }

    /**
     * Truncate all tables
     *
     * @return void
     */
    public function truncateAllTables(): void
    {
        $this->getSchema()->truncateAllTables();
    }

    /**
     * Names of all the prefixed tables in piwik
     * Doesn't use the DB
     *
     * @return array Table names
     */
    public function getTablesNames(): array
    {
        return $this->getSchema()->getTablesNames();
    }

    /**
     * Get list of tables installed
     *
     * @param bool $forceReload Invalidate cache
     *
     * @return array  installed tables
     */
    public function getTablesInstalled(bool $forceReload = true): array
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
    public function getTableColumns(string $tableName): array
    {
        return $this->getSchema()->getTableColumns($tableName);
    }

    /**
     * Returns true if Matomo tables exist
     *
     * @return bool  True if tables exist; false otherwise
     */
    public function hasTables(): bool
    {
        return $this->getSchema()->hasTables();
    }
}
