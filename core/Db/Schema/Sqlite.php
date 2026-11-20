<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Db\Schema;

use Exception;
use Piwik\Common;
use Piwik\Config;
use Piwik\Db;
use Piwik\Db\Schema\Sqlite\DefinitionConverter;
use Piwik\Db\Sqlite\Connection;
use Piwik\Db\SqlListParser;
use Piwik\Piwik;
use Piwik\Plugin\Manager;

/**
 * SQLite schema (`schema = Sqlite`).
 *
 * The tables are defined once, in MySQL syntax, by {@link Mysql::getTablesCreateSql()}, and
 * converted to SQLite by {@link DefinitionConverter} when they are created. The same conversion
 * applies to plugin tables created with {@link createTable()}.
 */
class Sqlite extends Mysql
{
    protected $minimumSupportedVersion = Connection::MINIMUM_VERSION;

    /**
     * @var string[]|null
     */
    private $installedTables = null;

    /**
     * @var DefinitionConverter|null
     */
    private $converter = null;

    public function getDatabaseType(): string
    {
        return 'SQLite';
    }

    public function getVersion(): string
    {
        return (string) Db::fetchOne('SELECT sqlite_version()');
    }

    public function hasReachedEOL(): bool
    {
        // SQLite is a library: its version is the one PHP is built with
        return false;
    }

    public function getDefaultPort(): int
    {
        return 0;
    }

    public function getTableCreateOptions(): string
    {
        return '';
    }

    public function getDefaultCollationForCharset(string $charset): string
    {
        return '';
    }

    /**
     * Creates the database file, in WAL mode.
     *
     * @param string|null $dbName the path of the database file, by default the configured one
     */
    public function createDatabase($dbName = null)
    {
        Connection::createDatabaseFile($this->getConnectionConfig($dbName));
    }

    /**
     * Deletes the database file. The connection to it must be closed first.
     *
     * @param string|null $dbName the path of the database file, by default the configured one
     */
    public function dropDatabase($dbName = null)
    {
        Connection::deleteDatabaseFile($this->getConnectionConfig($dbName));
    }

    /**
     * Creates the table and its indexes unless the table exists, in which case nothing is changed,
     * as with MySQL's `CREATE TABLE IF NOT EXISTS`.
     */
    public function createTable($nameWithoutPrefix, $createDefinition)
    {
        $tableName = Common::prefixTable($nameWithoutPrefix);
        $statements = $this->getConverter()->convertTableDefinition($tableName, $createDefinition, true);
        if (in_array($tableName, $this->getAllTables(), true)) {
            return;
        }

        $this->executeStatements($statements);
    }

    public function createTables()
    {
        $installed = $this->getAllExistingTables();
        $prefix = Common::prefixTable('');

        $tablesToCreate = $this->getTablesCreateSql();
        unset($tablesToCreate['archive_blob']);
        unset($tablesToCreate['archive_numeric']);

        foreach ($tablesToCreate as $name => $sql) {
            if (!in_array($prefix . $name, $installed, true)) {
                $this->executeStatements($this->getConverter()->convertCreateTable($sql));
            }
        }
    }

    /**
     * Creates a table with the definition of one of Matomo's tables, e.g. a monthly archive table.
     *
     * @throws Exception if the table already exists, as on MySQL (error 1050)
     */
    public function createTableFromTemplate(string $templateNameWithoutPrefix, string $tableName): void
    {
        $sql = $this->getTableCreateSql($templateNameWithoutPrefix);
        $this->executeStatements($this->getConverter()->convertCreateTable($sql, $tableName));
    }

    /**
     * Converts MySQL `ALTER TABLE` specifications to SQLite statements, one or more per specification:
     * adding and dropping columns and indexes. Changing a column or the primary key needs a table
     * rebuild, which isn't supported here, and fails like any other unsupported specification.
     *
     * A column added as `NOT NULL` without a default gets the type's zero value as default, which is
     * the value MySQL gives the existing rows.
     */
    public function getAlterTableSql(string $tableName, array $specifications): array
    {
        $tableName = str_replace('`', '', $tableName);
        $converter = $this->getConverter();
        $collations = $this->getColumnCollations($tableName);

        // MySQL applies the specifications of one ALTER TABLE together; SQLite needs an index dropped
        // before its column, and a column added before an index on it
        $dropIndexes = [];
        $dropColumns = [];
        $addColumns = [];
        $addIndexes = [];

        foreach ($specifications as $specification) {
            $specification = trim($specification);
            if (preg_match('/^ADD\s+(UNIQUE(?:\s+(?:KEY|INDEX))?|KEY|INDEX)\s*`?(\w+)`?\s*(\(.*\))$/is', $specification, $matches)) {
                $addIndexes[] = [$matches[2], stripos($matches[1], 'UNIQUE') === 0, $converter->parseIndexColumns($tableName, $matches[3])];
            } elseif (preg_match('/^DROP\s+(?:KEY|INDEX)\s+`?(\w+)`?$/is', $specification, $matches)) {
                $dropIndexes[] = 'DROP INDEX `' . DefinitionConverter::getIndexName($tableName, $matches[1]) . '`';
            } elseif (
                preg_match('/^ADD\s+(?:COLUMN\s+)?`?(\w+)`?\s+(.*?)(?:\s+(?:AFTER\s+`?\w+`?|FIRST))?$/is', $specification, $matches)
                && !preg_match('/^(PRIMARY|CONSTRAINT|FOREIGN|FULLTEXT|SPATIAL)$/i', $matches[1])
            ) {
                $definition = $converter->convertColumnDefinition($tableName, $matches[2]);
                $addColumns[] = 'ALTER TABLE `' . $tableName . '` ADD COLUMN `' . $matches[1] . '` ' . $this->withImplicitDefault($definition);
                $collations[strtolower($matches[1])] = preg_match('/\bCOLLATE (\w+)$/', $definition, $collation) ? $collation[1] : null;
            } elseif (
                preg_match('/^DROP\s+(?:COLUMN\s+)?`?(\w+)`?$/is', $specification, $matches)
                && !preg_match('/^(PRIMARY|INDEX|KEY|FOREIGN)$/i', $matches[1])
            ) {
                $dropColumns[] = 'ALTER TABLE `' . $tableName . '` DROP COLUMN `' . $matches[1] . '`';
            } elseif (
                preg_match('/^(?:MODIFY\s+(?:COLUMN\s+)?`?(\w+)`?|CHANGE\s+(?:COLUMN\s+)?`?(\w+)`?\s+`?(\w+)`?)\s+(.*?)(?:\s+(?:AFTER\s+`?\w+`?|FIRST))?$/is', $specification, $matches)
                && ($matches[1] !== '' || strcasecmp($matches[2], $matches[3]) === 0)
                && $this->hasColumnDefinition($tableName, $matches[1] !== '' ? $matches[1] : $matches[2], $matches[4])
            ) {
                // the column already has this definition, e.g. a dimension's column declared again on update
                continue;
            } else {
                throw new \InvalidArgumentException(sprintf('SQLite: unsupported ALTER TABLE specification for table %s: %s', $tableName, $specification));
            }
        }

        // created last, with the collations of the columns added before
        $createIndexes = [];
        foreach ($addIndexes as [$name, $unique, $columns]) {
            $createIndexes[] = $converter->createIndexSql($tableName, $name, $unique, $columns, $collations);
        }

        return array_merge($dropIndexes, $dropColumns, $addColumns, $createIndexes);
    }

    public function getTablesLike(string $pattern): array
    {
        $regex = self::getLikePatternRegex($pattern);

        return array_values(array_filter($this->getAllTables(), function ($table) use ($regex) {
            return preg_match($regex, $table) === 1;
        }));
    }

    /**
     * Returns the regular expression matching what a `LIKE` pattern matches, case-sensitively as
     * `SHOW TABLES LIKE` does on MySQL with case-sensitive table names: `%` and `_` are wildcards
     * unless escaped with a backslash.
     *
     * @internal
     */
    public static function getLikePatternRegex(string $pattern): string
    {
        // a backslash and the character it escapes, or any other single character
        return '/^' . preg_replace_callback('/\\\\.|./s', function ($match) {
            if ($match[0] === '%') {
                return '.*';
            }
            if ($match[0] === '_') {
                return '.';
            }

            return preg_quote(strlen($match[0]) === 2 ? $match[0][1] : $match[0], '/');
        }, $pattern) . '$/sD';
    }

    public function tableHasIndex(string $tableName, string $indexName): bool
    {
        $tableName = str_replace('`', '', $tableName);
        if ($indexName === 'PRIMARY') {
            foreach (Db::fetchAll('PRAGMA table_info(`' . $tableName . '`)') as $column) {
                if ((int) $column['pk'] > 0) {
                    return true;
                }
            }
            return false;
        }

        // index names are case-insensitive, as on MySQL
        $qualifiedName = DefinitionConverter::getIndexName($tableName, $indexName);
        foreach (Db::fetchAll('PRAGMA index_list(`' . $tableName . '`)') as $index) {
            if (strcasecmp($index['name'], $qualifiedName) === 0) {
                return true;
            }
        }

        return false;
    }

    public function getTableColumns($tableName)
    {
        $columns = Db::fetchAll('PRAGMA table_info(`' . str_replace('`', '', $tableName) . '`)');
        $isAutoIncrement = $this->hasAutoIncrement($tableName);

        // the keys of MySQL's SHOW COLUMNS
        $fields = [];
        foreach ($columns as $column) {
            $isPrimaryKey = (int) $column['pk'] > 0;
            $fields[$column['name']] = [
                'Field'   => $column['name'],
                'Type'    => strtolower($column['type']),
                'Null'    => $column['notnull'] || $isPrimaryKey ? 'NO' : 'YES',
                'Key'     => $isPrimaryKey ? 'PRI' : '',
                'Default' => $column['dflt_value'],
                'Extra'   => $isPrimaryKey && $isAutoIncrement ? 'auto_increment' : '',
            ];
        }

        return $fields;
    }

    public function getTablesInstalled($forceReload = true)
    {
        if ($this->installedTables !== null && $forceReload !== true) {
            return $this->installedTables;
        }

        $allTables = $this->getAllExistingTables();
        $allMyTables = $this->getTablesNames();

        // see Mysql::getTablesInstalled()
        if (count($allTables) && empty($GLOBALS['DISABLE_GET_TABLES_INSTALLED_EVENTS_FOR_TEST'])) {
            Manager::getInstance()->loadPlugins(Manager::getAllPluginsNames());
            Piwik::postEvent('Db.getTablesInstalled', [&$allMyTables]);
            Manager::getInstance()->unloadPlugins();
            Manager::getInstance()->loadActivatedPlugins();
        }

        $prefix = Common::prefixTable('');
        $archiveTables = array_filter($allTables, function ($table) use ($prefix) {
            return str_starts_with($table, $prefix . 'archive_numeric') || str_starts_with($table, $prefix . 'archive_blob');
        });

        $this->installedTables = array_values(array_unique(array_merge(array_intersect($allMyTables, $allTables), $archiveTables)));

        return $this->installedTables;
    }

    public function truncateAllTables()
    {
        $tables = $this->getAllExistingTables();
        $hasSequences = (bool) Db::fetchOne("SELECT 1 FROM sqlite_schema WHERE type = 'table' AND name = 'sqlite_sequence'");

        foreach ($tables as $table) {
            Db::query('DELETE FROM `' . $table . '`');
            if ($hasSequences) {
                // like TRUNCATE, start the AUTO_INCREMENT ids again from 1
                Db::query('DELETE FROM sqlite_sequence WHERE name = ?', [$table]);
            }
        }
    }

    /**
     * SQLite has no statement time limit hint; the query runs without limit.
     */
    public function addMaxExecutionTimeHintToQuery(string $sql, float $limit): string
    {
        return $sql;
    }

    public function optimizeTables(array $tables, bool $force = false): bool
    {
        // a VACUUM rewrites the whole database file, it isn't run per table
        return false;
    }

    public function isOptimizeInnoDBSupported(): bool
    {
        return false;
    }

    public function supportsWindowFunctions(): bool
    {
        return true;
    }

    public function supportsGroupByWithRollup(): bool
    {
        return false;
    }

    public function getInsertIgnoreSql(): string
    {
        return 'INSERT OR IGNORE';
    }

    public function getOnDuplicateKeyUpdateSql(): string
    {
        // without a conflict target, the update applies to whichever unique key conflicts (SQLite 3.35+)
        return 'ON CONFLICT DO UPDATE SET';
    }

    public function getInsertedValueSql(string $column): string
    {
        return 'excluded.' . $column;
    }

    public function getGroupConcatSql(string $expression, string $orderBy = '', string $separator = ','): string
    {
        if (strpbrk($separator, "'\"\\") !== false) {
            throw new \InvalidArgumentException('The GROUP_CONCAT separator must not contain quotes or backslashes.');
        }

        $orderBy = $orderBy === '' ? '' : ' ORDER BY ' . $orderBy;

        return 'group_concat(' . $expression . ", '" . $separator . "'" . $orderBy . ')';
    }

    public function getLikeEscapeSql(): string
    {
        // SQLite has no default LIKE escape character
        return " ESCAPE '\\'";
    }

    /**
     * SQLite's planner gets no hint: its only one, `INDEXED BY`, fails the query when the planner cannot
     * use the index, where MySQL's hints only steer it.
     */
    public function getIndexHintSql(string $indexName, bool $force = true): string
    {
        return '';
    }

    public function getSupportedReadIsolationTransactionLevel(): string
    {
        return 'SERIALIZABLE';
    }

    /**
     * Runs the statements creating a table and its indexes atomically, so a failure leaves neither.
     * A savepoint also works inside a transaction the caller has opened. As on MySQL, creating a
     * table that exists fails with error 1050 unless the statement says `IF NOT EXISTS`.
     *
     * @param string[] $statements
     */
    private function executeStatements(array $statements): void
    {
        $db = Db::get();
        $db->exec('SAVEPOINT matomo_create_table');
        try {
            foreach ($statements as $statement) {
                $db->exec($statement);
            }
        } catch (Exception $e) {
            $db->exec('ROLLBACK TO matomo_create_table');
            $db->exec('RELEASE matomo_create_table');
            throw $e;
        }
        $db->exec('RELEASE matomo_create_table');
        $this->installedTables = null;
    }

    /**
     * Whether a column is defined as the MySQL column definition converts to, either when the table was
     * created or when the column was added (with the default SQLite then needs).
     */
    private function hasColumnDefinition(string $tableName, string $column, string $mysqlDefinition): bool
    {
        $converted = $this->getConverter()->convertColumnDefinition($tableName, $mysqlDefinition);
        $sql = (string) Db::fetchOne("SELECT sql FROM sqlite_schema WHERE type = 'table' AND name = ?", [$tableName]);
        $start = strpos($sql, '(');
        $end = strrpos($sql, ')');
        if ($start === false || $end === false) {
            return false;
        }

        foreach (SqlListParser::splitList(substr($sql, $start + 1, $end - $start - 1)) as $item) {
            if (preg_match('/^[`"]?(\w+)[`"]?\s+(.*)$/s', $item, $matches) && strcasecmp($matches[1], $column) === 0) {
                $actual = self::normalizeDefinition($matches[2]);

                return $actual === self::normalizeDefinition($converted)
                    || $actual === self::normalizeDefinition($this->withImplicitDefault($converted));
            }
        }

        return false;
    }

    /**
     * Upper-cases a definition and collapses its whitespace, except inside string literals.
     */
    private static function normalizeDefinition(string $definition): string
    {
        $parts = preg_split("/('(?:[^']|'')*')/", trim($definition), -1, PREG_SPLIT_DELIM_CAPTURE);
        foreach ($parts as $i => $part) {
            if ($i % 2 === 0) {
                $parts[$i] = strtoupper(preg_replace('/\s+/', ' ', $part));
            }
        }

        return implode('', $parts);
    }

    /**
     * SQLite can't add a NOT NULL column without a default; MySQL fills existing rows with the type's zero value.
     */
    private function withImplicitDefault(string $definition): string
    {
        if (!preg_match('/\bNOT NULL\b/', $definition) || preg_match('/\bDEFAULT\b/', $definition)) {
            return $definition;
        }

        $zero = str_starts_with($definition, 'TEXT') ? "''" : (str_starts_with($definition, 'BLOB') ? "X''" : '0');

        return str_replace('NOT NULL', 'NOT NULL DEFAULT ' . $zero, $definition);
    }

    /**
     * @return array<string, string|null> the collation of each column, by lower case column name
     */
    private function getColumnCollations(string $tableName): array
    {
        $collations = [];
        $sql = (string) Db::fetchOne("SELECT sql FROM sqlite_schema WHERE type = 'table' AND name = ?", [$tableName]);
        foreach (Db::fetchAll('PRAGMA table_info(`' . $tableName . '`)') as $column) {
            $collation = null;
            if (preg_match('/[(,]\s*[`"]?' . preg_quote($column['name'], '/') . '[`"]?\s+[^,]*?\bCOLLATE\s+(\w+)/i', $sql, $matches)) {
                $collation = strtoupper($matches[1]);
            }
            $collations[strtolower($column['name'])] = $collation;
        }

        return $collations;
    }

    private function hasAutoIncrement(string $tableName): bool
    {
        $sql = Db::fetchOne("SELECT sql FROM sqlite_schema WHERE type = 'table' AND name = ?", [$tableName]);

        return is_string($sql) && stripos($sql, 'AUTOINCREMENT') !== false;
    }

    /**
     * @return string[] the names of the tables with Matomo's table prefix
     */
    private function getAllExistingTables(): array
    {
        $prefix = Common::prefixTable('');

        return array_values(array_filter($this->getAllTables(), function ($table) use ($prefix) {
            return str_starts_with($table, $prefix);
        }));
    }

    /**
     * @return string[] the names of all tables but SQLite's own
     */
    private function getAllTables(): array
    {
        $rows = Db::fetchAll("SELECT name FROM sqlite_schema WHERE type = 'table' AND name NOT LIKE 'sqlite\\_%' ESCAPE '\\' ORDER BY name");

        return array_column($rows, 'name');
    }

    private function getConnectionConfig(?string $dbName): array
    {
        $config = Config::getInstance()->database;
        if ($dbName !== null && $dbName !== '') {
            $config['dbname'] = $dbName;
        }

        return $config;
    }

    private function getConverter(): DefinitionConverter
    {
        if ($this->converter === null) {
            $this->converter = new DefinitionConverter();
        }

        return $this->converter;
    }
}
