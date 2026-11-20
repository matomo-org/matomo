<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Db;

use Piwik\Common;
use Piwik\Config;
use Piwik\Db;
use Piwik\Db\Schema\Mysql;
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
     * @var SchemaInterface|null
     */
    private $schema = null;

    /**
     * The MySQL schema, whose SQL dialect and table metadata operations serve schemas that have none.
     *
     * @var Mysql|null
     */
    private $mysqlDialect = null;

    /**
     * Get schema class name
     *
     * @param string $schemaName
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
     */
    public static function getDefaultPortForSchema(string $schemaName): int
    {
        $schemaClassName = self::getSchemaClassName($schemaName);
        /** @var SchemaInterface $schemaClass */
        $schemaClass = new $schemaClassName();
        return $schemaClass->getDefaultPort();
    }

    /**
     * Extracts a comparable semantic version (e.g. "10.6.19") from a raw database
     * server version string.
     *
     * MariaDB reports its version with a leading "5.5.5-" compatibility prefix for
     * old MySQL clients (e.g. "5.5.5-10.6.19-MariaDB"); that prefix is stripped so
     * the actual server version is returned.
     */
    public static function getComparableVersion(string $databaseVersion): string
    {
        if (
            stripos($databaseVersion, 'mariadb') !== false
            && preg_match('/5\.5\.5-(\d+(?:\.\d+){0,2})-MariaDB/i', $databaseVersion, $matches)
        ) {
            return $matches[1];
        }

        if (preg_match('/\d+(?:\.\d+){0,2}/', $databaseVersion, $matches)) {
            return $matches[0];
        }

        return $databaseVersion;
    }

    /**
     * Detects the database server type ("MySQL" or "MariaDB") from a raw server
     * version string, matching the values returned by getDatabaseType().
     */
    public static function getServerTypeFromVersion(string $serverVersion): string
    {
        if (stripos($serverVersion, 'mariadb') !== false) {
            return 'MariaDB';
        }

        return 'MySQL';
    }

    /**
     * Returns the minimum supported server version to compare the given raw server
     * version string against.
     *
     * The minimum is derived from the actual server type reported in the version
     * string rather than from the configured schema, so a server is always held to
     * the requirement matching what it really is: a MariaDB server is never checked
     * against the MySQL minimum and vice versa, regardless of the schema configured
     * in config.ini.php (running MariaDB with the MySQL schema is a supported setup).
     */
    public static function getMinimumSupportedVersionForServer(string $serverVersion): string
    {
        $schemaClassName = self::getSchemaClassName(self::getServerTypeFromVersion($serverVersion));
        /** @var SchemaInterface $schemaClass */
        $schemaClass = new $schemaClassName();
        return $schemaClass->getMinimumSupportedVersion();
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
     */
    private function getSchema(): SchemaInterface
    {
        if ($this->schema === null) {
            $this->loadSchema();
        }

        return $this->schema;
    }

    /**
     * Unset schema instance
     */
    public function unsetSchema(): void
    {
        $this->schema = null;
    }

    /**
     * Returns the default collation for a charset.
     */
    public function getDefaultCollationForCharset(string $charset): string
    {
        return $this->getSchema()->getDefaultCollationForCharset($charset);
    }

    /**
     * Get the table options to use for a CREATE TABLE statement.
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
     * Creates a table with the definition of one of Matomo's tables, e.g. a monthly archive table.
     *
     * @param string $templateNameWithoutPrefix e.g. `archive_numeric`
     * @param string $tableName                 the prefixed name of the table to create
     * @throws \Exception if the table already exists (error 1050)
     */
    public function createTableFromTemplate(string $templateNameWithoutPrefix, string $tableName): void
    {
        $schema = $this->getSchema();
        if ($schema instanceof Mysql) {
            $schema->createTableFromTemplate($templateNameWithoutPrefix, $tableName);
            return;
        }

        $sql = $schema->getTableCreateSql($templateNameWithoutPrefix);
        Db::query(str_replace(Common::prefixTable($templateNameWithoutPrefix), $tableName, $sql));
    }

    /**
     * Returns the statements applying `ALTER TABLE` specifications (in MySQL syntax, e.g.
     * `ADD COLUMN name VARCHAR(10) NULL`) to a table.
     *
     * @param string   $tableName      the prefixed table name
     * @param string[] $specifications
     * @return string[]
     */
    public function getAlterTableSql(string $tableName, array $specifications): array
    {
        $schema = $this->getSchema();
        if ($schema instanceof Mysql) {
            return $schema->getAlterTableSql($tableName, $specifications);
        }

        if ($schema->supportsComplexColumnUpdates()) {
            return ['ALTER TABLE `' . $tableName . '` ' . implode(', ', $specifications)];
        }

        return array_map(function ($specification) use ($tableName) {
            return 'ALTER TABLE `' . $tableName . '` ' . $specification;
        }, $specifications);
    }

    /**
     * Returns the names of the tables matching a `LIKE` pattern, in which `\\` escapes `%` and `_`.
     *
     * @return string[]
     */
    public function getTablesLike(string $pattern): array
    {
        return $this->getMysqlCompatibleSchema()->getTablesLike($pattern);
    }

    /**
     * Takes a named lock for the current connection, waiting up to `$timeoutSeconds`. The connection
     * may take a lock it holds again, and releases it as many times. Its locks are released when it
     * disconnects.
     */
    public function getNamedLock(string $name, int $timeoutSeconds): bool
    {
        return $this->getMysqlCompatibleSchema()->getNamedLock($name, $timeoutSeconds);
    }

    /**
     * Releases a named lock the current connection holds. Returns false if it doesn't hold it.
     */
    public function releaseNamedLock(string $name): bool
    {
        return $this->getMysqlCompatibleSchema()->releaseNamedLock($name);
    }

    /**
     * Whether no connection holds a named lock.
     */
    public function isNamedLockFree(string $name): bool
    {
        return $this->getMysqlCompatibleSchema()->isNamedLockFree($name);
    }

    /**
     * Whether a table has an index with the given name (`PRIMARY` for the primary key).
     */
    public function tableHasIndex(string $tableName, string $indexName): bool
    {
        return $this->getMysqlCompatibleSchema()->tableHasIndex($tableName, $indexName);
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
     */
    public function addMaxExecutionTimeHintToQuery(string $sql, float $limit): string
    {
        return $this->getSchema()->addMaxExecutionTimeHintToQuery($sql, $limit);
    }

    /**
     * Returns if the schema support complex column updates
     */
    public function supportsComplexColumnUpdates(): bool
    {
        return $this->getSchema()->supportsComplexColumnUpdates();
    }

    /**
     * Returns if the schema supports `OPTIMIZE TABLE` statements for innodb tables
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
     * @param array $tables The name of the table to optimize or an array of tables to optimize.
     *                             Table names must be prefixed (see {@link Piwik\Common::prefixTable()}).
     * @param bool $force If true, the `OPTIMIZE TABLE` query will be run even if InnoDB tables are being used.
     */
    public function optimizeTables(array $tables, bool $force = false): bool
    {
        return $this->getSchema()->optimizeTables($tables, $force);
    }

    /**
     * Returns if the database engine can provide a rollup ranking query result
     * without needing additional sorting.
     */
    public function supportsRankingRollupWithoutExtraSorting(): bool
    {
        return $this->getSchema()->supportsRankingRollupWithoutExtraSorting();
    }

    /**
     * Returns if the database engine is able to use sorted subqueries
     */
    public function supportsSortingInSubquery(): bool
    {
        return $this->getSchema()->supportsSortingInSubquery();
    }

    /**
     * Returns whether ranking queries count rows with window functions.
     *
     * @see QueryCapabilitiesInterface::supportsWindowFunctions()
     */
    public function supportsWindowFunctions(): bool
    {
        $schema = $this->getSchema();

        return $schema instanceof QueryCapabilitiesInterface && $schema->supportsWindowFunctions();
    }

    /**
     * Returns whether `GROUP BY ... WITH ROLLUP` is available.
     *
     * @see QueryCapabilitiesInterface::supportsGroupByWithRollup()
     */
    public function supportsGroupByWithRollup(): bool
    {
        $schema = $this->getSchema();

        return !($schema instanceof QueryCapabilitiesInterface) || $schema->supportsGroupByWithRollup();
    }

    /**
     * Returns the configured schema if it derives from the MySQL schema, which defines the table
     * metadata operations. Other schemas get the MySQL ones, which is the SQL Matomo always ran for them.
     */
    private function getMysqlCompatibleSchema(): Mysql
    {
        // only for operations that use no other method of the schema
        $schema = $this->getSchema();

        if ($schema instanceof Mysql) {
            return $schema;
        }

        if ($this->mysqlDialect === null) {
            $this->mysqlDialect = new Mysql();
        }

        return $this->mysqlDialect;
    }

    /**
     * Returns the SQL dialect of the configured schema. Schemas that don't implement
     * {@link SqlDialectInterface} get MySQL's, which is the SQL Matomo always emitted for them.
     */
    private function getSqlDialect(): SqlDialectInterface
    {
        $schema = $this->getSchema();

        if ($schema instanceof SqlDialectInterface) {
            return $schema;
        }

        if ($this->mysqlDialect === null) {
            $this->mysqlDialect = new Mysql();
        }

        return $this->mysqlDialect;
    }

    /**
     * Returns the statement prefix of an insert that skips rows conflicting with a unique key.
     *
     * @see SqlDialectInterface::getInsertIgnoreSql()
     */
    public function getInsertIgnoreSql(): string
    {
        return $this->getSqlDialect()->getInsertIgnoreSql();
    }

    /**
     * Returns the clause that turns an insert into an update of the conflicting row.
     *
     * @see SqlDialectInterface::getOnDuplicateKeyUpdateSql()
     */
    public function getOnDuplicateKeyUpdateSql(): string
    {
        return $this->getSqlDialect()->getOnDuplicateKeyUpdateSql();
    }

    /**
     * Returns an expression referring to the value an upsert tried to write into a column.
     *
     * @see SqlDialectInterface::getInsertedValueSql()
     */
    public function getInsertedValueSql(string $column): string
    {
        return $this->getSqlDialect()->getInsertedValueSql($column);
    }

    /**
     * Returns an aggregate expression concatenating an expression over the rows of a group.
     *
     * @see SqlDialectInterface::getGroupConcatSql()
     */
    public function getGroupConcatSql(string $expression, string $orderBy = '', string $separator = ','): string
    {
        return $this->getSqlDialect()->getGroupConcatSql($expression, $orderBy, $separator);
    }

    /**
     * Returns the clause to append after a `LIKE ?` placeholder bound to a backslash-escaped pattern.
     *
     * @see SqlDialectInterface::getLikeEscapeSql()
     */
    public function getLikeEscapeSql(): string
    {
        return $this->getSqlDialect()->getLikeEscapeSql();
    }

    /**
     * Returns the hint to append after a table name or alias asking the engine to use an index.
     *
     * @see IndexHintInterface::getIndexHintSql()
     */
    public function getIndexHintSql(string $indexName, bool $force = true): string
    {
        $schema = $this->getSchema();
        if ($schema instanceof IndexHintInterface) {
            return $schema->getIndexHintSql($indexName, $force);
        }

        // what Matomo always sent to the schemas that don't say otherwise
        return ($force ? ' FORCE INDEX (' : ' USE INDEX (') . $indexName . ')';
    }

    /**
     * Returns the supported read isolation transaction level
     *
     * For example:
     *      READ COMMITTED
     *      or
     *      READ UNCOMMITTED
     */
    public function getSupportedReadIsolationTransactionLevel(): string
    {
        return $this->getSchema()->getSupportedReadIsolationTransactionLevel();
    }

    /**
     * Returns the type of the current database (e.g. MySQL, MariaDb, ...)
     */
    public function getDatabaseType(): string
    {
        return $this->getSchema()->getDatabaseType();
    }

    /**
     * Returns the version of the currently used database server
     */
    public function getVersion(): string
    {
        return $this->getSchema()->getVersion();
    }

    /**
     * Returns the minimum supported version of the currently used database server
     */
    public function getMinimumSupportedVersion(): string
    {
        return $this->getSchema()->getMinimumSupportedVersion();
    }

    /**
     * Returns if the currently used database version has reach its EOL
     */
    public function hasReachedEOL(): string
    {
        return $this->getSchema()->hasReachedEOL();
    }
}
