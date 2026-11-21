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
use Piwik\Db\Schema\Sqlite\NamedLock;
use Piwik\Db\Sqlite\Connection;
use Piwik\Db\Sqlite\ErrorCodes;
use Piwik\Db\Sqlite\MysqlError;
use Piwik\Db\SqlListParser;
use Piwik\Piwik;
use Piwik\Plugin\Manager;
use Piwik\Updater\Migration\Db as Migration;

/**
 * SQLite schema (`schema = Sqlite`).
 *
 * The tables are defined once, in MySQL syntax, by {@link Mysql::getTablesCreateSql()}, and
 * converted to SQLite by {@link DefinitionConverter} when they are created. The same conversion
 * applies to plugin tables created with {@link createTable()}.
 */
class Sqlite extends Mysql
{
    /**
     * A column of a table's SQL as SQLite stores it: its name, quoted as SQLite allows or bare, and its definition.
     */
    private const SQLITE_COLUMN = '/^(`(?:[^`]|``)+`|"(?:[^"]|"")+"|\\[[^\\]]+\\]|\\w+)\\s+(.*)$/s';

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
        $statements = $this->getConverter()->convertTableDefinition($tableName, $createDefinition);
        if (in_array($tableName, $this->getAllTables(), true)) {
            return;
        }

        try {
            $this->executeStatements($statements);
        } catch (Exception $e) {
            // created by another process since, with its indexes: the statements were all rolled back
            if (!ErrorCodes::isErrNo($e, Migration::ERROR_CODE_TABLE_EXISTS)) {
                throw $e;
            }
        }
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
     * The table and its indexes, converted from the MySQL definition.
     */
    public function getCreateTableSql(string $tableName, string $createDefinition): array
    {
        return $this->getConverter()->convertTableDefinition(str_replace('`', '', $tableName), $createDefinition);
    }

    /**
     * Runs MySQL `ALTER TABLE` specifications on the current connection. Those `getAlterTableSql()`
     * converts run converted. Changing a column (`CHANGE`, `MODIFY`) or the primary key (`ADD`/`DROP
     * PRIMARY KEY`), which SQLite's ALTER TABLE lacks, rebuilds the table as the SQLite documentation
     * describes (https://www.sqlite.org/lang_altertable.html#otheralter): a table with the new
     * definition, the rows copied, the old table dropped and the new one renamed, its indexes and
     * triggers created again and its AUTOINCREMENT counter kept. All of it applies, or nothing does.
     *
     * The table is read when this runs, so an earlier migration of the same update is seen. An error a
     * migration may ignore is raised with MySQL's number. Unlike MySQL, no value is truncated or clamped
     * to a narrower type: SQLite applies the new type's affinity. An index on a column prefix keeps the
     * expression its column type gave it when it was created.
     */
    public function alterTable(string $tableName, array $specifications): void
    {
        $tableName = str_replace('`', '', $tableName);
        $converted = [];
        $indexes = [];
        $changes = [];
        foreach ($specifications as $specification) {
            $specification = trim($specification);
            if ($this->needsRebuild($specification)) {
                $changes[] = $specification;
            } elseif (preg_match('/^ADD\s+(?:(?:UNIQUE\s+)?(?:INDEX|KEY)\b|UNIQUE\b)/i', $specification)) {
                $indexes[] = $specification;
            } else {
                $converted[] = $specification;
            }
        }

        Db::inSqliteSavepoint(function () use ($tableName, $converted, $indexes, $changes) {
            // MySQL applies the specifications together: a column added or dropped before the renames, an index
            // added after them names the new column
            foreach ($this->getAlterTableSql($tableName, $converted) as $sql) {
                Db::exec($sql);
            }
            $changes = $this->renameColumns($tableName, $changes);
            foreach ($this->getAlterTableSql($tableName, $indexes) as $sql) {
                Db::exec($sql);
            }
            if ($changes !== []) {
                $this->rebuildTable($tableName, $changes);
            }
        });
    }

    /**
     * Whether `alterTable()` rebuilds the table for this specification: changing a column or the primary key.
     *
     * @internal
     */
    public function needsRebuild(string $specification): bool
    {
        $specification = trim($specification);

        return preg_match('/^(?:ADD|DROP)\s+PRIMARY\s+KEY\b/i', $specification) === 1 || self::parseColumnChange($specification) !== null;
    }

    /**
     * Renames the columns `CHANGE` renames, by SQLite's RENAME COLUMN, which updates the indexes and
     * triggers.
     *
     * @param string[] $changes
     * @return string[] the changes, each `CHANGE` now a `MODIFY` of the renamed column
     */
    private function renameColumns(string $tableName, array $changes): array
    {
        if ($changes === []) {
            return [];
        }

        $table = $this->readTable($tableName);
        foreach ($changes as $i => $specification) {
            $change = self::parseColumnChange($specification);
            if ($change === null) {
                continue;
            }
            [$column, $newName, $definition] = $change;
            if (!isset($table['columns'][strtolower($column)])) {
                throw new MysqlError(sprintf("Unknown column '%s' in '%s'", $column, $tableName), Migration::ERROR_CODE_UNKNOWN_COLUMN);
            }
            $current = $table['columns'][strtolower($column)]['name'];
            if ($current !== $newName) {
                if (strcasecmp($current, $newName) !== 0 && isset($table['columns'][strtolower($newName)])) {
                    throw new MysqlError(sprintf("Duplicate column name '%s'", $newName), Migration::ERROR_CODE_DUPLICATE_COLUMN);
                }
                // SQLite's names ignore case: a new case goes through another name
                $steps = strcasecmp($current, $newName) === 0 ? [$current . '__matomo_rename', $newName] : [$newName];
                foreach ($steps as $step) {
                    Db::exec(sprintf(
                        'ALTER TABLE main.%s RENAME COLUMN %s TO %s',
                        DefinitionConverter::quoteIdentifier($table['name']),
                        DefinitionConverter::quoteIdentifier($current),
                        DefinitionConverter::quoteIdentifier($step)
                    ));
                    $current = $step;
                }
                $table = $this->readTable($tableName);
            }
            $changes[$i] = 'MODIFY ' . DefinitionConverter::quoteIdentifier($newName) . ' ' . $definition;
        }

        return $changes;
    }

    /**
     * @param string[] $changes `CHANGE`, `MODIFY`, `ADD PRIMARY KEY` and `DROP PRIMARY KEY` specifications
     */
    private function rebuildTable(string $tableName, array $changes): void
    {
        $converter = $this->getConverter();
        if (Db::fetchOne("SELECT 1 FROM temp.sqlite_schema WHERE type = 'table' AND name = ? COLLATE NOCASE", [$tableName])) {
            throw new \InvalidArgumentException(sprintf('SQLite: a temporary table shadows %s, which cannot be rebuilt', $tableName));
        }
        $table = $this->readTable($tableName);

        $columns = $table['columns'];
        $primaryKey = $table['primaryKey'];
        $autoIncrement = $table['autoIncrement'];
        $requestedAutoIncrement = null;
        $changed = false;
        $primaryKeyChanged = false;
        $copied = [];
        foreach ($changes as $specification) {
            $change = self::parseColumnChange($specification);
            if ($change !== null) {
                $name = strtolower($change[1]);
                $inPrimaryKey = in_array($name, array_map('strtolower', array_column($primaryKey ?? [], 'column')), true);
                $converted = $converter->convertChangedColumnDefinition(
                    $tableName,
                    $change[2],
                    $columns[$name]['name'],
                    $inPrimaryKey,
                    $inPrimaryKey && count($primaryKey) === 1
                );
                if ($converted['autoIncrement']) {
                    if ($autoIncrement === $name) {
                        // INTEGER PRIMARY KEY AUTOINCREMENT whatever the width
                        continue;
                    }
                    if ($requestedAutoIncrement !== null && $requestedAutoIncrement !== $name) {
                        throw new MysqlError('Incorrect table definition; there can be only one auto column and it must be defined as a key', 1075);
                    }
                    // the key is checked once every specification applies, as MySQL does
                    $autoIncrement = $requestedAutoIncrement = $name;
                } elseif ($autoIncrement === $name) {
                    // no longer AUTO_INCREMENT, still the primary key, declared with the table
                    $autoIncrement = null;
                }
                if (self::matchesOutsideLiterals('/\bUNIQUE\b/i', $columns[$name]['sql']) && !self::matchesOutsideLiterals('/\bUNIQUE\b/i', $converted['sql'])) {
                    // a native column constraint, kept as MySQL keeps a unique index
                    $converted['sql'] .= ' UNIQUE';
                }
                if (self::normalizeDefinition($columns[$name]['sql']) !== self::normalizeDefinition($converted['sql'])) {
                    $columns[$name]['sql'] = $converted['sql'];
                    // MySQL's ALTER (Matomo's sql_mode is not strict) stores the implicit default for a NULL
                    $copied[$name] = $converted['notNull'] && !$converted['autoIncrement']
                        ? sprintf('IFNULL(%s, %s)', DefinitionConverter::quoteIdentifier($columns[$name]['name']), $converted['implicitDefault'])
                        : null;
                    $changed = true;
                }
            } elseif (preg_match('/^ADD\s+PRIMARY\s+KEY\s*(\(.*\))$/is', $specification, $matches)) {
                if ($primaryKey !== null) {
                    throw new MysqlError('Multiple primary key defined', Migration::ERROR_CODE_DUPLICATE_PRIMARY_KEY);
                }
                $primaryKey = $converter->parseIndexColumns($tableName, $matches[1]);
                foreach ($primaryKey as $i => $part) {
                    $name = strtolower($part['column']);
                    if (!isset($columns[$name])) {
                        throw new MysqlError(sprintf("Key column '%s' doesn't exist in table", $part['column']), Migration::ERROR_CODE_KEY_COLUMN_NOT_EXISTS);
                    }
                    $primaryKey[$i]['column'] = $columns[$name]['name'];
                    [$columns[$name]['sql'], $implicitDefault] = self::getPrimaryKeyColumnSql($columns[$name]['name'], $columns[$name]['sql'], count($primaryKey) === 1);
                    if ($implicitDefault !== null) {
                        // as MySQL's ALTER stores it for a NULL
                        $copied[$name] = sprintf('IFNULL(%s, %s)', DefinitionConverter::quoteIdentifier($columns[$name]['name']), $implicitDefault);
                    }
                }
                $changed = $primaryKeyChanged = true;
            } elseif (preg_match('/^DROP\s+PRIMARY\s+KEY$/is', $specification)) {
                if ($primaryKey === null) {
                    throw new MysqlError("Can't DROP 'PRIMARY'; check that column/key exists", Migration::ERROR_CODE_COLUMN_NOT_EXISTS);
                }
                // the columns stay NOT NULL, as on MySQL; a sole integer key loses the check keeping it from the rowid
                if (count($primaryKey) === 1) {
                    $name = strtolower($primaryKey[0]['column']);
                    $columns[$name]['sql'] = self::removeRowidGuard($columns[$name]['sql']);
                }
                $primaryKey = null;
                $changed = $primaryKeyChanged = true;
            } else {
                throw new \InvalidArgumentException(sprintf('SQLite: unsupported ALTER TABLE specification for table %s: %s', $tableName, $specification));
            }
        }

        if (!$changed) {
            return;
        }

        if ((int) Db::fetchOne('PRAGMA foreign_keys')) {
            // dropping the old table would delete or change the rows that reference it
            throw new \InvalidArgumentException(sprintf('SQLite: %s cannot be rebuilt while foreign_keys is on', $tableName));
        }

        if (
            $autoIncrement !== null
            && ($primaryKey === null || count($primaryKey) !== 1 || strtolower($primaryKey[0]['column']) !== $autoIncrement || $primaryKey[0]['length'] !== null)
        ) {
            throw new MysqlError('Incorrect table definition; there can be only one auto column and it must be defined as a key', 1075);
        }

        if ($autoIncrement !== null && $autoIncrement !== $table['autoIncrement']) {
            $columns[$autoIncrement]['sql'] = 'INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL';
        }
        $lines = [];
        foreach ($columns as $column) {
            $lines[] = DefinitionConverter::quoteIdentifier($column['name']) . ' ' . $column['sql'];
        }
        if ($primaryKey !== null && $autoIncrement === null) {
            $lines[] = 'PRIMARY KEY (' . implode(', ', array_map(function (array $part) {
                return DefinitionConverter::quoteIdentifier($part['column']);
            }, $primaryKey)) . ')';
        }
        $lines = array_merge($lines, $table['constraints']);

        $name = $table['name'];
        $rebuilt = $name . '__matomo_rebuild';
        $primaryIndex = DefinitionConverter::getIndexName($name, 'PRIMARY');
        $indexes = Db::fetchAll(
            "SELECT name, sql FROM main.sqlite_schema WHERE type = 'index' AND tbl_name = ? AND sql IS NOT NULL ORDER BY rowid",
            [$name]
        );
        // the temporary triggers on the table go with it as well
        $triggers = Db::fetchAll(
            "SELECT sql, temp FROM (SELECT sql, 0 AS temp, rowid FROM main.sqlite_schema WHERE type = 'trigger' AND tbl_name = ?
                UNION ALL SELECT sql, 1, rowid FROM temp.sqlite_schema WHERE type = 'trigger' AND tbl_name = ?) ORDER BY temp, rowid",
            [$name, $name]
        );
        // generated columns are computed, not copied, unless a specification made them ordinary ones
        $generated = array_map('strtolower', array_column(Db::fetchAll("SELECT name FROM pragma_table_xinfo(?, 'main') WHERE hidden IN (2, 3)", [$name]), 'name'));
        $copiedColumns = [];
        $values = [];
        foreach ($columns as $key => $column) {
            if (!in_array($key, $generated, true) || array_key_exists($key, $copied)) {
                $copiedColumns[] = DefinitionConverter::quoteIdentifier($column['name']);
                $values[] = $copied[$key] ?? DefinitionConverter::quoteIdentifier($column['name']);
            }
        }
        $quotedName = DefinitionConverter::quoteIdentifier($name);
        $quotedRebuilt = DefinitionConverter::quoteIdentifier($rebuilt);

        // in main, whatever temporary table has the same name
        Db::exec(sprintf("CREATE TABLE main.%s (\n  %s\n)%s", $quotedRebuilt, implode(",\n  ", $lines), $table['options']));
        // OR ABORT: a constraint's own conflict clause can't end the caller's transaction
        Db::exec(sprintf('INSERT OR ABORT INTO main.%s (%s) SELECT %s FROM main.%s', $quotedRebuilt, implode(', ', $copiedColumns), implode(', ', $values), $quotedName));
        if ($autoIncrement !== null && $table['autoIncrement'] !== null) {
            // the counter goes on from the last value issued, which can be above the highest kept
            Db::query('DELETE FROM main.sqlite_sequence WHERE name = ?', [$rebuilt]);
            Db::query('INSERT INTO main.sqlite_sequence (name, seq) SELECT ?, seq FROM main.sqlite_sequence WHERE name = ?', [$rebuilt, $name]);
        }
        Db::exec(sprintf('DROP TABLE main.%s', $quotedName));
        // views and other tables' triggers still name the table: the legacy rename leaves them as they are,
        // where the current one fails on them while the table is missing
        $legacyAlterTable = (int) Db::fetchOne('PRAGMA legacy_alter_table');
        Db::exec('PRAGMA legacy_alter_table = ON');
        try {
            Db::exec(sprintf('ALTER TABLE main.%s RENAME TO %s', $quotedRebuilt, $quotedName));
        } finally {
            Db::exec('PRAGMA legacy_alter_table = ' . ($legacyAlterTable ? 'ON' : 'OFF'));
        }
        foreach ($indexes as $index) {
            if (!$primaryKeyChanged || strcasecmp($index['name'], $primaryIndex) !== 0) {
                Db::exec($index['sql']);
            }
        }
        $hasPrefix = false;
        foreach ($primaryKey ?? [] as $part) {
            $hasPrefix = $hasPrefix || $part['length'] !== null;
        }
        if ($primaryKeyChanged && $hasPrefix) {
            // as convertTableDefinition(): the primary key on the whole columns, the prefixes unique
            Db::exec($converter->createIndexSql($name, 'PRIMARY', true, $primaryKey, $this->getColumnTypes($name)));
        }
        foreach ($triggers as $trigger) {
            // SQLite stores a temporary trigger without its TEMP
            Db::exec($trigger['temp'] ? preg_replace('/^\s*CREATE\s+TRIGGER\b/i', 'CREATE TEMP TRIGGER', $trigger['sql']) : $trigger['sql']);
        }
    }

    /**
     * @return array{name: string, columns: array<string, array{name: string, sql: string}>, primaryKey: array<array{column: string, length: int|null, order: string}>|null, autoIncrement: string|null, constraints: string[], options: string}
     *         the table's columns by lower case name, its primary key, its AUTOINCREMENT column by lower case name, its other table constraints and options
     */
    private function readTable(string $tableName): array
    {
        $row = Db::fetchRow("SELECT name, sql FROM main.sqlite_schema WHERE type = 'table' AND name = ? COLLATE NOCASE", [$tableName]);
        if (empty($row)) {
            throw new MysqlError(sprintf("Table '%s' doesn't exist", $tableName), Migration::ERROR_CODE_TABLE_NOT_EXISTS);
        }

        $sql = (string) $row['sql'];
        $start = strpos($sql, '(');
        $end = strrpos($sql, ')');
        // e.g. STRICT, WITHOUT ROWID
        $table = ['name' => $row['name'], 'columns' => [], 'primaryKey' => null, 'autoIncrement' => null, 'constraints' => [], 'options' => substr($sql, $end + 1)];
        foreach (SqlListParser::splitList(substr($sql, $start + 1, $end - $start - 1)) as $item) {
            if (preg_match('/^PRIMARY\s+KEY\s*\((.*)\)$/is', $item, $matches)) {
                // SQLite writes the names its RENAME COLUMN changes in double quotes
                $parts = array_map(function (string $part): string {
                    return preg_match(self::SQLITE_COLUMN, trim($part) . ' ', $name)
                        ? DefinitionConverter::quoteIdentifier(self::unquoteSqliteIdentifier($name[1])) . ' ' . $name[2]
                        : $part;
                }, SqlListParser::splitList($matches[1]));
                $table['primaryKey'] = $this->getConverter()->parseIndexColumns($tableName, '(' . implode(', ', $parts) . ')');
            } elseif (
                preg_match(self::SQLITE_COLUMN, $item, $matches)
                && !preg_match('/^(PRIMARY|UNIQUE|CONSTRAINT|FOREIGN|CHECK)$/i', $matches[1])
            ) {
                $name = self::unquoteSqliteIdentifier($matches[1]);
                $definition = $matches[2];
                $withoutLiterals = preg_replace("/'(?:[^']|'')*'/", "''", $definition);
                if (preg_match('/\bPRIMARY\s+KEY\b/i', $withoutLiterals)) {
                    $table['primaryKey'] = [['column' => $name, 'length' => null, 'order' => '']];
                    if (preg_match('/\bAUTOINCREMENT\b/i', $withoutLiterals)) {
                        $table['autoIncrement'] = strtolower($name);
                    } else {
                        // declared again with the table
                        $definition = self::removeOutsideLiterals('/\s*\bPRIMARY\s+KEY(?:\s+(?:ASC|DESC))?\b/i', $definition);
                    }
                }
                $table['columns'][strtolower($name)] = ['name' => $name, 'sql' => $definition];
            } else {
                $table['constraints'][] = $item;
            }
        }

        return $table;
    }

    /**
     * A column joining the primary key is NOT NULL with its type's implicit default, as on MySQL and as
     * convertTableDefinition() declares it (from the SQLite type only: a date gets an empty string, a
     * VARBINARY(n) the zero bytes of a BINARY(n)); alone and an integer, it is declared INT with a type
     * check, so it doesn't become the rowid.
     *
     * @return array{0: string, 1: string|null} the declaration, and the implicit default MySQL gives a NULL
     *                                          (null when the column was already NOT NULL)
     */
    private static function getPrimaryKeyColumnSql(string $name, string $definition, bool $solePrimaryKey): array
    {
        $implicitDefault = null;
        // a key can't be NULL, MySQL drops that default
        $definition = self::removeOutsideLiterals('/\s*\bDEFAULT\s+NULL\b/i', $definition);
        $withoutLiterals = preg_replace("/'(?:[^']|'')*'/", "''", $definition);
        if (!preg_match('/\bNOT\s+NULL\b/i', $withoutLiterals) && preg_match('/^(\w+)(?:\((\d+)\))?/', $definition, $type)) {
            switch (strtoupper($type[1])) {
                case 'TEXT':
                    $implicitDefault = "('')";
                    break;
                case 'BLOB':
                    $implicitDefault = isset($type[2]) ? "(CAST(X'" . str_repeat('00', (int) $type[2]) . "' AS TEXT))" : "('')";
                    break;
                default:
                    $implicitDefault = '(0)';
            }
            $constraints = ' NOT NULL' . (preg_match('/\bDEFAULT\b/i', $withoutLiterals) ? '' : ' DEFAULT ' . $implicitDefault);
            $definition = $type[0] . $constraints . substr($definition, strlen($type[0]));
        }
        if ($solePrimaryKey && preg_match('/^INTEGER\b/i', $definition)) {
            $definition = preg_replace('/^INTEGER\b/i', 'INT', $definition) . ' CHECK (typeof(' . DefinitionConverter::quoteIdentifier($name) . ") = 'integer')";
        }

        return [$definition, $implicitDefault];
    }

    private static function unquoteSqliteIdentifier(string $identifier): string
    {
        $quote = $identifier[0];
        if ($quote === '[') {
            return substr($identifier, 1, -1);
        }
        if ($quote === '`' || $quote === '"') {
            return str_replace($quote . $quote, $quote, substr($identifier, 1, -1));
        }

        return $identifier;
    }

    private static function matchesOutsideLiterals(string $pattern, string $definition): bool
    {
        return preg_match($pattern, (string) preg_replace("/'(?:[^']|'')*'/", "''", $definition)) === 1;
    }

    /**
     * The declaration of a column that was a sole integer primary key, as convertTableDefinition() gives it
     * once it no longer is: INTEGER, without the type check that kept it from becoming the rowid.
     */
    private static function removeRowidGuard(string $definition): string
    {
        // quoted as SQLite writes a renamed column too
        $check = '/\s+CHECK \(typeof\((?:`(?:[^`]|``)+`|"(?:[^"]|"")+"|\w+)\) = \'integer\'\)$/i';
        if (!preg_match('/^INT\b/i', $definition) || !preg_match($check, $definition)) {
            return $definition;
        }

        return preg_replace(['/^INT\b/i', $check], ['INTEGER', ''], $definition);
    }

    private static function removeOutsideLiterals(string $pattern, string $definition): string
    {
        $parts = preg_split("/('(?:[^']|'')*')/", $definition, -1, PREG_SPLIT_DELIM_CAPTURE);
        foreach ($parts as $i => $part) {
            if ($i % 2 === 0) {
                $parts[$i] = preg_replace($pattern, '', $part);
            }
        }

        return implode('', $parts);
    }

    /**
     * @return array{0: string, 1: string, 2: string}|null for `CHANGE` or `MODIFY`: the column, its new
     *                                                    name (the same for `MODIFY`) and its new definition
     */
    private static function parseColumnChange(string $specification): ?array
    {
        $identifier = DefinitionConverter::IDENTIFIER;
        if (!preg_match("/^(?:MODIFY\\s+(?:COLUMN\\s+)?($identifier)|CHANGE\\s+(?:COLUMN\\s+)?($identifier)\\s*($identifier))\\s+(.*?)(?:\\s+(?:AFTER\\s+$identifier|FIRST))?$/is", trim($specification), $matches)) {
            return null;
        }

        $names = array_map([DefinitionConverter::class, 'unquoteIdentifier'], [$matches[1], $matches[2], $matches[3]]);

        return $matches[1] !== '' ? [$names[0], $names[0], $matches[4]] : [$names[1], $names[2], $matches[4]];
    }

    /**
     * Converts MySQL `ALTER TABLE` specifications to SQLite statements, one or more per specification:
     * adding and dropping columns and indexes. Changing a column or the primary key needs a table
     * rebuild, which `alterTable()` runs; here it fails like any other unsupported specification.
     *
     * A column added as `NOT NULL` without a default gets the type's implicit default from the
     * converter, which is the value MySQL gives the existing rows.
     */
    public function getAlterTableSql(string $tableName, array $specifications): array
    {
        $tableName = str_replace('`', '', $tableName);
        $converter = $this->getConverter();
        $columnTypes = $this->getColumnTypes($tableName);

        // MySQL applies the specifications of one ALTER TABLE together; SQLite needs an index dropped
        // before its column, and a column added before an index on it
        $dropIndexes = [];
        $dropColumns = [];
        $addColumns = [];
        $addIndexes = [];

        foreach ($specifications as $specification) {
            $specification = trim($specification);
            if (preg_match('/^ADD\s+(UNIQUE(?:\s+(?:KEY|INDEX))?|KEY|INDEX)\s*(' . DefinitionConverter::IDENTIFIER . ')\s*(\(.*\))$/is', $specification, $matches)) {
                $addIndexes[] = [DefinitionConverter::unquoteIdentifier($matches[2]), stripos($matches[1], 'UNIQUE') === 0, $converter->parseIndexColumns($tableName, $matches[3])];
            } elseif (preg_match('/^DROP\s+(?:KEY|INDEX)\s+(' . DefinitionConverter::IDENTIFIER . ')$/is', $specification, $matches)) {
                $dropIndexes[] = 'DROP INDEX ' . DefinitionConverter::quoteIdentifier(DefinitionConverter::getIndexName($tableName, DefinitionConverter::unquoteIdentifier($matches[1])));
            } elseif (
                // a bare keyword starts another specification, a quoted one is a column name
                preg_match('/^ADD\s+(?:COLUMN\s+)?(' . DefinitionConverter::IDENTIFIER . ')\s+(.*?)(?:\s+(?:AFTER\s+' . DefinitionConverter::IDENTIFIER . '|FIRST))?$/is', $specification, $matches)
                && !preg_match('/^(PRIMARY|CONSTRAINT|FOREIGN|FULLTEXT|SPATIAL|UNIQUE|KEY|INDEX)$/i', $matches[1])
            ) {
                $column = DefinitionConverter::unquoteIdentifier($matches[1]);
                $definition = $converter->convertColumnDefinition($tableName, $matches[2], $column);
                $addColumns[] = 'ALTER TABLE `' . $tableName . '` ADD COLUMN ' . DefinitionConverter::quoteIdentifier($column) . ' ' . $definition;
                $columnTypes[strtolower($column)] = self::getColumnType($definition);
            } elseif (
                preg_match('/^DROP\s+(?:COLUMN\s+)?(' . DefinitionConverter::IDENTIFIER . ')$/is', $specification, $matches)
                && !preg_match('/^(PRIMARY|INDEX|KEY|FOREIGN)$/i', $matches[1])
            ) {
                $dropColumns[] = 'ALTER TABLE `' . $tableName . '` DROP COLUMN ' . DefinitionConverter::quoteIdentifier(DefinitionConverter::unquoteIdentifier($matches[1]));
            } elseif (
                ($change = self::parseColumnChange($specification)) !== null
                && strcasecmp($change[0], $change[1]) === 0
                && !preg_match('/\bAUTO_INCREMENT\b/i', $change[2])
                && $this->hasColumnDefinition($tableName, $change[0], $change[2])
            ) {
                // the column already has this definition, e.g. a dimension's column declared again on update
                continue;
            } else {
                // changing a column or the primary key needs a table rebuild: alterTable() does it
                throw new \InvalidArgumentException(sprintf('SQLite: unsupported ALTER TABLE specification for table %s: %s', $tableName, $specification));
            }
        }

        // created last, with the types of the columns added before
        $createIndexes = [];
        foreach ($addIndexes as [$name, $unique, $columns]) {
            $createIndexes[] = $converter->createIndexSql($tableName, $name, $unique, $columns, $columnTypes);
        }

        return array_merge($dropIndexes, $dropColumns, $addColumns, $createIndexes);
    }

    public function getTablesLike(string $pattern): array
    {
        self::checkTablesLikePattern($pattern);
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
            // else a primary key on column prefixes, which is a unique index named PRIMARY
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
        if (empty($columns)) {
            // a missing table fails, as MySQL's SHOW COLUMNS does ("no such table")
            Db::query('SELECT * FROM `' . str_replace('`', '', $tableName) . '` LIMIT 0');
        }
        $isAutoIncrement = $this->hasAutoIncrement($tableName);
        $definitions = $this->getColumnDefinitions($tableName);

        // the keys of MySQL's SHOW COLUMNS
        $fields = [];
        foreach ($columns as $column) {
            $isPrimaryKey = (int) $column['pk'] > 0;
            $fields[$column['name']] = [
                'Field'   => $column['name'],
                'Type'    => strtolower($column['type']),
                'Null'    => $column['notnull'] || $isPrimaryKey ? 'NO' : 'YES',
                'Key'     => $isPrimaryKey ? 'PRI' : '',
                'Default' => self::getColumnDefaultValue($column['dflt_value'], $definitions[strtolower($column['name'])] ?? ''),
                'Extra'   => $isPrimaryKey && $isAutoIncrement ? 'auto_increment' : '',
            ];
        }

        return $fields;
    }

    /**
     * A column default as MySQL's SHOW COLUMNS gives it: the value itself, or null when the column has
     * no default of its own (a parenthesized default, which PRAGMA table_info reads without its
     * parentheses, is the implicit one the converter adds).
     */
    private static function getColumnDefaultValue(?string $default, string $definition): ?string
    {
        if ($default === null || strcasecmp($default, 'NULL') === 0 || preg_match('/\bDEFAULT\s*\(/i', preg_replace("/'(?:[^']|'')*'/", "''", $definition))) {
            return null;
        }
        if (preg_match("/^'(.*)'$/s", $default, $matches)) {
            return str_replace("''", "'", $matches[1]);
        }

        return $default;
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

    public function getDateTimeIntervalSql(string $datetime, string $operator, string $amount, string $unit): string
    {
        self::checkInterval($operator, $unit);
        $sign = $operator === '-' ? '-' : '';

        // e.g. datetime(x, -(5) || ' seconds'); the result has MySQL's DATETIME text format
        return 'datetime(' . $datetime . ', ' . $sign . '(' . $amount . ") || ' " . strtolower($unit) . "s')";
    }

    public function getCreateTemporaryTableSql(string $tableName, string $definition, string $tableOptions = ''): string
    {
        $statements = $this->getConverter()->convertTableDefinition($tableName, $definition);
        if (count($statements) !== 1) {
            throw new \InvalidArgumentException('SQLite: a temporary table can have a primary key, but no other index.');
        }

        return preg_replace('/^CREATE TABLE /', 'CREATE TEMP TABLE ', $statements[0]);
    }

    public function getDropTemporaryTableSql(string $tableName): string
    {
        return 'DROP TABLE IF EXISTS temp.`' . str_replace('`', '', $tableName) . '`';
    }

    public function getHourSql(string $datetime): string
    {
        return "CAST(strftime('%H', " . $datetime . ') AS INTEGER)';
    }

    public function getDayOfWeekSql(string $datetime): string
    {
        // cast as a whole, so that the expression has integer affinity when compared with a bound string
        return "CAST(strftime('%w', " . $datetime . ') + 1 AS INTEGER)';
    }

    public function getUnixTimestampSql(string $datetime = ''): string
    {
        return 'unixepoch(' . $datetime . ')';
    }

    public function getCurrentDateTimeSql(): string
    {
        return "datetime('now')";
    }

    public function getCharLengthSql(string $expression): string
    {
        return 'length(' . $expression . ')';
    }

    public function getSecondsBetweenSql(string $from, string $to): string
    {
        // $from first, so that placeholders bind in the same order as on MySQL
        return '(-unixepoch(' . $from . ') + unixepoch(' . $to . '))';
    }

    public function getGreatestSql(string ...$expressions): string
    {
        if (count($expressions) < 2) {
            throw new \InvalidArgumentException('At least two expressions are needed.');
        }

        return 'max(' . implode(', ', $expressions) . ')';
    }

    public function getLeastSql(string ...$expressions): string
    {
        if (count($expressions) < 2) {
            throw new \InvalidArgumentException('At least two expressions are needed.');
        }

        return 'min(' . implode(', ', $expressions) . ')';
    }

    public function getRowLockSql(bool $exclusive = true): string
    {
        // a SQLite write transaction locks the whole database
        return '';
    }

    public function getTruncateTableSql(string $tableName): string
    {
        return 'DELETE FROM `' . $tableName . '`';
    }

    public function getNullSafeEqualSql(string $left, string $right): string
    {
        // SQLite reads `x IS TRUE` as a truth test; in MySQL's `x <=> TRUE` the literal is the value 1.
        // Other operands are left as they are, so column affinity applies as for any comparison.
        $literal = function (string $operand): string {
            // a parenthesized literal, such as `(TRUE)`, is the same literal
            if (
                preg_match('/^[\s(]*(TRUE|FALSE)[\s)]*$/i', $operand, $matches)
                && substr_count($operand, '(') === substr_count($operand, ')')
            ) {
                return strtoupper($matches[1]) === 'TRUE' ? '1' : '0';
            }
            return $operand;
        };

        return '(' . $literal($left) . ') IS (' . $literal($right) . ')';
    }

    public function getNoTableSql(): string
    {
        return '';
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

    public function getNamedLock(string $name, int $timeoutSeconds): bool
    {
        $db = $this->getSqliteConnection();
        if ($db === null) {
            return parent::getNamedLock($name, $timeoutSeconds);
        }

        return NamedLock::acquire($db, $db->getDatabaseFile(), $name, $timeoutSeconds);
    }

    public function releaseNamedLock(string $name): bool
    {
        $db = $this->getSqliteConnection();
        if ($db === null) {
            return parent::releaseNamedLock($name);
        }

        return NamedLock::release($db, $name);
    }

    public function isNamedLockFree(string $name): bool
    {
        $db = $this->getSqliteConnection();
        if ($db === null) {
            return parent::isNamedLockFree($name);
        }

        return NamedLock::isFree($db, $db->getDatabaseFile(), $name);
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
     * Whether a column is defined as the MySQL column definition converts to.
     */
    private function hasColumnDefinition(string $tableName, string $column, string $mysqlDefinition): bool
    {
        $converted = $this->getConverter()->convertColumnDefinition($tableName, $mysqlDefinition, $column);
        $actual = $this->getColumnDefinitions($tableName)[strtolower($column)] ?? null;
        if ($actual === null) {
            return false;
        }

        $actual = self::normalizeDefinition($actual);

        return $actual === self::normalizeDefinition($converted);
    }

    /**
     * @return array<string, string> the definition of each column in the table's SQL, by lower case column name
     */
    private function getColumnDefinitions(string $tableName): array
    {
        // the table PRAGMA table_info reads: a temporary one shadows the permanent one
        $sql = (string) Db::fetchOne(
            "SELECT sql FROM (SELECT sql, 0 AS shadow FROM temp.sqlite_schema WHERE type = 'table' AND name = ? COLLATE NOCASE"
            . " UNION ALL SELECT sql, 1 FROM main.sqlite_schema WHERE type = 'table' AND name = ? COLLATE NOCASE) ORDER BY shadow LIMIT 1",
            [$tableName, $tableName]
        );
        $start = strpos($sql, '(');
        $end = strrpos($sql, ')');
        if ($start === false || $end === false) {
            return [];
        }

        $definitions = [];
        foreach (SqlListParser::splitList(substr($sql, $start + 1, $end - $start - 1)) as $item) {
            if (
                preg_match(self::SQLITE_COLUMN, $item, $matches)
                && !preg_match('/^(PRIMARY|UNIQUE|CONSTRAINT|FOREIGN|CHECK)$/i', $matches[1])
            ) {
                $definitions[strtolower(self::unquoteSqliteIdentifier($matches[1]))] = $matches[2];
            }
        }

        return $definitions;
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

        // a sole integer primary key is declared INT, the type is the same, and its generated type
        // check isn't part of the MySQL definition
        $normalized = preg_replace('/^INT\b/', 'INTEGER', implode('', $parts));

        return preg_replace("/ CHECK \\(TYPEOF\\((?:`(?:[^`]|``)+`|\"(?:[^\"]|\"\")+\"|\\w+)\\) = 'integer'\\)/", '', $normalized);
    }

    /**
     * @return array<string, array{type: string, collation: string|null}> the type and collation of each
     *                                                                     column, by lower case column name
     */
    private function getColumnTypes(string $tableName): array
    {
        return array_map([self::class, 'getColumnType'], $this->getColumnDefinitions($tableName));
    }

    /**
     * @return array{type: string, collation: string|null}
     */
    private static function getColumnType(string $definition): array
    {
        $parts = preg_split("/'(?:[^']|'')*'/", $definition);
        $withoutLiterals = implode("''", $parts === false ? [$definition] : $parts);

        return [
            'type' => strtoupper((string) strtok(trim($definition), " \t\n(")),
            'collation' => preg_match('/\bCOLLATE\s+[`"]?(\w+)/i', $withoutLiterals, $matches) ? strtoupper($matches[1]) : null,
        ];
    }

    private function hasAutoIncrement(string $tableName): bool
    {
        $sql = Db::fetchOne("SELECT sql FROM sqlite_schema WHERE type = 'table' AND name = ? COLLATE NOCASE", [$tableName]);

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

    /**
     * The connection in use if it is a SQLite one: the connection decides, whatever the configuration
     * file says.
     *
     * @return \Piwik\Db\Adapter\Pdo\Sqlite|\Piwik\Tracker\Db\Pdo\Sqlite|null
     */
    private function getSqliteConnection()
    {
        $db = Db::get();
        if ($db instanceof \Piwik\Db\Adapter\Pdo\Sqlite || $db instanceof \Piwik\Tracker\Db\Pdo\Sqlite) {
            return $db;
        }

        return null;
    }

    private function getConverter(): DefinitionConverter
    {
        if ($this->converter === null) {
            $this->converter = new DefinitionConverter();
        }

        return $this->converter;
    }
}
