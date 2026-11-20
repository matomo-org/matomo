<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Db\Schema\Sqlite;

use InvalidArgumentException;
use Piwik\Db\SqlListParser;

/**
 * Converts MySQL table and column definitions, as Matomo and its plugins write them, to SQLite.
 *
 * - Integer types become `INTEGER`; an `AUTO_INCREMENT` primary key becomes
 *   `INTEGER PRIMARY KEY AUTOINCREMENT`, so ids are never reused, as with InnoDB. Any other sole
 *   integer primary key is declared `INT`, so it doesn't become an alias of the rowid, which
 *   SQLite would assign when the value is missing or NULL; a CHECK keeps it an integer.
 * - Floating point and decimal types become `REAL`.
 * - Text types become `TEXT COLLATE NOCASE`, case-insensitive like MySQL's default collations
 *   (ASCII only), or `TEXT` with a binary collation (`*_bin`) on the column; a binary table
 *   collation is rejected.
 * - Binary types become `BLOB`; date and time types become `TEXT`; an `ENUM` becomes text with the
 *   text collation, a CHECK on its values and, NOT NULL without a default, its first value as default.
 * - Indexes become `CREATE INDEX` statements named `<table>.<index>`, as index names are global
 *   in SQLite (and a dot can't be in either name). A prefix (`col(n)`) is dropped from a non-unique
 *   index; in a unique index it becomes `substr(col, 1, n)` with the column's collation, or the first
 *   n bytes of a binary column, so uniqueness stays the same.
 * - Table options (`ENGINE`, `CHARSET`, ...) are dropped.
 *
 * Anything else fails with an exception naming the table and the fragment, instead of creating a
 * table that doesn't behave as on MySQL.
 *
 * @internal
 */
final class DefinitionConverter
{
    private const INTEGER_TYPES = ['tinyint', 'smallint', 'mediumint', 'int', 'integer', 'bigint', 'bool', 'boolean'];
    private const REAL_TYPES = ['double', 'float', 'real', 'decimal', 'numeric'];
    private const TEXT_TYPES = ['char', 'varchar', 'tinytext', 'text', 'mediumtext', 'longtext', 'enum'];
    private const BLOB_TYPES = ['binary', 'varbinary', 'tinyblob', 'blob', 'mediumblob', 'longblob'];
    private const DATE_TYPES = ['date', 'datetime', 'timestamp', 'time'];

    /**
     * Converts a MySQL `CREATE TABLE` statement.
     *
     * @return string[] the statements creating the table and its indexes, to run in order
     */
    public function convertCreateTable(string $createTableSql, ?string $tableName = null): array
    {
        if (!preg_match('/^\s*CREATE\s+TABLE\s+(IF\s+NOT\s+EXISTS\s+)?`?(\w+)`?\s*\(/i', $createTableSql, $matches)) {
            throw new InvalidArgumentException('Not a CREATE TABLE statement: ' . $this->shorten($createTableSql));
        }

        $start = strlen($matches[0]);
        $end = $this->findClosingParenthesis($createTableSql, $start - 1);
        $definition = substr($createTableSql, $start, $end - $start);

        // only table options without an effect on SQLite may follow, e.g. not AUTO_INCREMENT=n or AS SELECT
        $options = substr($createTableSql, $end + 1);
        $option = '(?:ENGINE|(?:DEFAULT\s+)?(?:CHARSET|CHARACTER\s+SET|COLLATE)|ROW_FORMAT)\s*=?\s*\w+|COMMENT\s*=?\s*\'(?:[^\']|\'\')*\'';
        if (!preg_match('/^\s*(?:(?:' . $option . ')\s*,?\s*)*;?\s*$/i', $options)) {
            throw $this->unsupported($matches[2], trim($options));
        }

        // a binary table collation would also apply to the columns added later, which SQLite can't
        // know: it is rejected (no table of Matomo or its plugins has one)
        $optionsWithoutComment = preg_replace('/COMMENT\s*=?\s*\'(?:[^\']|\'\')*\'/i', '', $options);
        if (preg_match('/\b(?:COLLATE\s*=?\s*(?:\w+_bin|binary)|(?:CHARSET|CHARACTER\s+SET)\s*=?\s*binary)\b/i', $optionsWithoutComment)) {
            throw $this->unsupported($matches[2], trim($options));
        }

        return $this->convertTableDefinition($tableName ?? $matches[2], $definition, $matches[1] !== '');
    }

    /**
     * Converts the definition list of a table (what is inside the parentheses of `CREATE TABLE`).
     *
     * @return string[] the statements creating the table and its indexes, to run in order
     */
    public function convertTableDefinition(string $tableName, string $definition, bool $ifNotExists = false): array
    {
        $columns = [];
        $columnTypes = [];
        $primaryKey = null;
        $indexes = [];

        foreach (SqlListParser::splitList($definition) as $item) {
            if (preg_match('/^PRIMARY\s+KEY\s*(\(.*\))$/is', $item, $matches)) {
                $primaryKey = $this->parseIndexColumns($tableName, $matches[1]);
            } elseif (preg_match('/^(UNIQUE(?:\s+(?:KEY|INDEX))?|KEY|INDEX)\s*(?:`?(\w+)`?)?\s*(\(.*\))$/is', $item, $matches)) {
                $indexes[] = [
                    'unique' => stripos($matches[1], 'UNIQUE') === 0,
                    'name' => $matches[2] !== '' ? $matches[2] : null,
                    'columns' => $this->parseIndexColumns($tableName, $matches[3]),
                ];
            } elseif (preg_match('/^(FULLTEXT|SPATIAL|CONSTRAINT|FOREIGN|CHECK)\b/i', $item)) {
                throw $this->unsupported($tableName, $item);
            } else {
                [$name, $column] = $this->parseColumn($tableName, $item);
                $columns[$name] = $column;
                if ($column['unique']) {
                    // MySQL creates a unique index named after the column
                    $indexes[] = ['unique' => true, 'name' => null, 'columns' => [['column' => $name, 'length' => null, 'order' => '']]];
                }
            }
        }

        // as on MySQL, the columns of a primary key can't be NULL (SQLite allows it but for INTEGER PRIMARY KEY)
        foreach ($primaryKey ?? [] as $part) {
            foreach ($columns as $name => $column) {
                if (strcasecmp($name, $part['column']) === 0 && !$column['autoIncrement']) {
                    $columns[$name]['notNull'] = true;
                }
            }
        }

        // a sole INTEGER primary key would be the rowid, which SQLite assigns instead of failing on NULL
        $primaryKeyColumns = $primaryKey !== null ? array_column($primaryKey, 'column') : [];
        foreach ($columns as $name => $column) {
            if ($column['primaryKey']) {
                $primaryKeyColumns[] = $name;
            }
        }
        foreach ($columns as $name => $column) {
            if (
                count($primaryKeyColumns) === 1 && strcasecmp($primaryKeyColumns[0], $name) === 0
                && !$column['autoIncrement'] && $column['type'] === 'INTEGER'
            ) {
                $columns[$name]['type'] = 'INT';
                $columns[$name]['integerCheck'] = true;
            }
            $columns[$name]['sql'] = $this->getColumnSql($columns[$name]);
            $columnTypes[strtolower($name)] = ['type' => $columns[$name]['type'], 'collation' => $column['collation']];
        }

        if ($columns === []) {
            throw new InvalidArgumentException(sprintf('SQLite: table %s has no columns.', $tableName));
        }

        $lines = [];
        $autoIncrement = null;
        foreach ($columns as $name => $column) {
            if ($column['autoIncrement']) {
                $autoIncrement = $name;
            }
        }

        if ($autoIncrement !== null) {
            $isInlinePrimaryKey = $columns[$autoIncrement]['primaryKey'];
            $isSolePrimaryKey = $primaryKey !== null && count($primaryKey) === 1
                && strcasecmp($primaryKey[0]['column'], $autoIncrement) === 0 && $primaryKey[0]['length'] === null;
            if (!$isInlinePrimaryKey && !$isSolePrimaryKey) {
                throw new InvalidArgumentException(sprintf(
                    'SQLite: in table %s, the AUTO_INCREMENT column %s must be the whole primary key.',
                    $tableName,
                    $autoIncrement
                ));
            }
            $primaryKey = null;
        }

        foreach ($columns as $name => $column) {
            $lines[] = $this->quote($name) . ' ' . $column['sql'];
        }

        $hasPrefix = false;
        foreach ($primaryKey ?? [] as $part) {
            $hasPrefix = $hasPrefix || $part['length'] !== null;
        }

        if ($hasPrefix) {
            // SQLite has no prefix keys: the primary key is on the whole columns, so the metadata shows
            // it, and a unique index named PRIMARY on the prefixes keeps MySQL's uniqueness
            $indexes[] = ['unique' => true, 'name' => 'PRIMARY', 'columns' => $primaryKey];
        }
        if ($primaryKey !== null) {
            $lines[] = 'PRIMARY KEY (' . implode(', ', array_map(function (array $part) {
                return $this->quote($part['column']);
            }, $primaryKey)) . ')';
        }

        $statements = [sprintf(
            "CREATE TABLE %s%s (\n  %s\n)",
            $ifNotExists ? 'IF NOT EXISTS ' : '',
            $this->quote($tableName),
            implode(",\n  ", $lines)
        )];

        $usedNames = [];
        foreach ($indexes as $index) {
            if ($index['name'] !== null) {
                $usedNames[strtolower($index['name'])] = true;
            }
        }
        foreach ($indexes as $index) {
            $name = $index['name'] ?? $this->getDefaultIndexName($index['columns'], $usedNames);
            $usedNames[strtolower($name)] = true;
            $statements[] = $this->createIndexSql($tableName, $name, $index['unique'], $index['columns'], $columnTypes, $ifNotExists);
        }

        return $statements;
    }

    /**
     * Converts one MySQL column definition (type and attributes, without the name), e.g. for
     * `ALTER TABLE ... ADD COLUMN` or a dimension's column type.
     */
    public function convertColumnDefinition(string $tableName, string $columnDefinition, ?string $columnName = null): string
    {
        $column = $this->parseColumnAttributes($tableName, $columnDefinition, $columnName);
        // SQLite can't add a column with a constraint needing an index
        if ($column['autoIncrement'] || $column['primaryKey'] || $column['unique']) {
            throw $this->unsupported($tableName, $columnDefinition);
        }

        return $column['sql'];
    }

    /**
     * Returns the statement creating an index, with its name qualified by the table name.
     *
     * @param array<array{column: string, length: int|null, order: string}> $columns
     * @param array<string, array{type: string, collation: string|null}> $columnTypes the type and collation
     *                                                                               of the columns, by lower case name
     */
    public function createIndexSql(string $tableName, string $indexName, bool $unique, array $columns, array $columnTypes, bool $ifNotExists = false): string
    {
        $parts = [];
        foreach ($columns as $part) {
            $column = $this->quote($part['column']);
            if ($part['length'] !== null && $unique) {
                // the prefix decides uniqueness, so it is kept as an expression: bytes of a binary
                // column (a text value would stop at a NUL byte), characters with the column's collation
                $type = $columnTypes[strtolower($part['column'])] ?? ['type' => 'TEXT', 'collation' => null];
                if ($type['type'] === 'BLOB') {
                    $column = sprintf('substr(CAST(%s AS BLOB), 1, %d)', $column, $part['length']);
                } else {
                    $collation = $type['collation'];
                    $column = sprintf('substr(%s, 1, %d)%s', $column, $part['length'], $collation === null ? '' : ' COLLATE ' . $collation);
                }
            }
            $parts[] = $column . ($part['order'] !== '' ? ' ' . $part['order'] : '');
        }

        return sprintf(
            'CREATE %sINDEX %s%s ON %s (%s)',
            $unique ? 'UNIQUE ' : '',
            $ifNotExists ? 'IF NOT EXISTS ' : '',
            $this->quote(self::getIndexName($tableName, $indexName)),
            $this->quote($tableName),
            implode(', ', $parts)
        );
    }

    /**
     * SQLite index names are global to the database, MySQL ones are per table. Neither name can
     * contain a dot, so the qualified name is unique.
     */
    public static function getIndexName(string $tableName, string $indexName): string
    {
        return $tableName . '.' . $indexName;
    }

    /**
     * @return array<array{column: string, length: int|null, order: string}>
     */
    public function parseIndexColumns(string $tableName, string $list): array
    {
        $list = trim($list);
        if (!str_starts_with($list, '(') || !str_ends_with($list, ')')) {
            throw $this->unsupported($tableName, $list);
        }

        $columns = [];
        foreach (SqlListParser::splitList(substr($list, 1, -1)) as $part) {
            if (!preg_match('/^`?(\w+)`?\s*(?:\(\s*(\d+)\s*\))?(?:\s+(ASC|DESC))?$/i', $part, $matches)) {
                throw $this->unsupported($tableName, $part);
            }
            $columns[] = [
                'column' => $matches[1],
                'length' => isset($matches[2]) && $matches[2] !== '' ? (int) $matches[2] : null,
                'order' => strtoupper($matches[3] ?? ''),
            ];
        }

        return $columns;
    }

    /**
     * @return array{0: string, 1: array}
     */
    private function parseColumn(string $tableName, string $item): array
    {
        if (!preg_match('/^(`\w+`|\w+)\s+(.*)$/s', $item, $matches)) {
            throw $this->unsupported($tableName, $item);
        }

        $name = trim($matches[1], '`');

        return [$name, $this->parseColumnAttributes($tableName, $matches[2], $name)];
    }

    /**
     * @param string|null $name the column name, needed for the CHECK constraints of an ENUM and an
     *                          integer primary key
     * @return array{name: string|null, type: string, notNull: bool, default: string|null, collation: string|null, autoIncrement: bool, primaryKey: bool, unique: bool, enumValues: string[]|null, integerCheck: bool, sql: string}
     */
    private function parseColumnAttributes(string $tableName, string $definition, ?string $name = null): array
    {
        $rest = trim($definition);
        $enumValues = null;
        if (preg_match('/^ENUM\s*\(\s*\'/i', $rest)) {
            // an ENUM is text checked against its values
            $end = $this->findClosingParenthesis($rest, strpos($rest, '('));
            $matches = [substr($rest, 0, $end + 1), 'enum'];
            $enumValues = SqlListParser::splitList(substr($rest, strpos($rest, '(') + 1, $end - strpos($rest, '(') - 1));
            foreach ($enumValues as $value) {
                if (!preg_match("/^'(?:[^']|'')*'$/", $value)) {
                    throw $this->unsupported($tableName, $definition);
                }
            }
        } elseif (!preg_match('/^(\w+)(?:\s*\(\s*(\d+)(?:\s*,\s*(\d+))?\s*\))?/i', $rest, $matches)) {
            throw $this->unsupported($tableName, $definition);
        }
        $type = strtolower($matches[1]);
        $rest = substr($rest, strlen($matches[0]));

        if ($type === 'double' && preg_match('/^\s+PRECISION\b/i', $rest, $precision)) {
            $rest = substr($rest, strlen($precision[0]));
        }

        $binaryCollation = false;
        $notNull = null;
        $default = null;
        $autoIncrement = false;
        $primaryKey = false;
        $unique = false;

        while (($rest = ltrim($rest)) !== '') {
            if (preg_match('/^(UNSIGNED|SIGNED|ZEROFILL)\b/i', $rest, $m)) {
                // SQLite integers are signed 64-bit, which holds every unsigned value up to BIGINT's signed range
            } elseif (preg_match('/^(?:CHARACTER\s+SET|CHARSET)\s+(\w+)/i', $rest, $m)) {
                // text is stored as UTF-8; the column gets the character set's default collation
                $binaryCollation = strcasecmp($m[1], 'binary') === 0;
            } elseif (preg_match('/^COLLATE\s+(\w+)/i', $rest, $m)) {
                $binaryCollation = (bool) preg_match('/_bin$|^binary$/i', $m[1]);
            } elseif (preg_match('/^NOT\s+NULL\b/i', $rest, $m)) {
                $notNull = true;
            } elseif (preg_match('/^NULL\b/i', $rest, $m)) {
                $notNull = false;
            } elseif (preg_match('/^DEFAULT\s+(\'(?:[^\']|\'\')*\'|-?\d+(?:\.\d+)?|NULL\b|CURRENT_TIMESTAMP(?:\(\))?)/i', $rest, $m)) {
                $default = preg_match('/^CURRENT_TIMESTAMP/i', $m[1]) ? 'CURRENT_TIMESTAMP' : $m[1];
            } elseif (preg_match('/^AUTO_INCREMENT\b/i', $rest, $m)) {
                $autoIncrement = true;
            } elseif (preg_match('/^PRIMARY\s+KEY\b/i', $rest, $m)) {
                $primaryKey = true;
                $notNull = true;
            } elseif (preg_match('/^UNIQUE(?:\s+KEY)?\b/i', $rest, $m)) {
                $unique = true;
            } elseif (preg_match('/^COMMENT\s+\'(?:[^\']|\'\')*\'/i', $rest, $m)) {
                // comments are not kept
            } else {
                throw $this->unsupported($tableName, $definition);
            }
            $rest = substr($rest, strlen($m[0]));
        }

        $collation = null;
        if (in_array($type, self::INTEGER_TYPES, true)) {
            $sqlType = 'INTEGER';
        } elseif (in_array($type, self::REAL_TYPES, true)) {
            $sqlType = 'REAL';
        } elseif (in_array($type, self::TEXT_TYPES, true)) {
            $sqlType = 'TEXT';
            $collation = $binaryCollation ? null : 'NOCASE';
        } elseif (in_array($type, self::BLOB_TYPES, true)) {
            $sqlType = 'BLOB';
        } elseif (in_array($type, self::DATE_TYPES, true)) {
            $sqlType = 'TEXT';
        } else {
            throw $this->unsupported($tableName, $definition);
        }

        // MySQL makes a primary key column NOT NULL, even declared NULL
        if ($primaryKey) {
            $notNull = true;
        }

        // a NOT NULL ENUM without a default defaults to its first value
        if ($enumValues !== null && $notNull === true && $default === null) {
            $default = $enumValues[0];
        }
        if ($enumValues !== null && $name === null) {
            throw $this->unsupported($tableName, $definition . ' (ENUM without a column name)');
        }

        if ($autoIncrement && $sqlType !== 'INTEGER') {
            throw $this->unsupported($tableName, $definition);
        }

        $column = [
            'name' => $name,
            'enumValues' => $enumValues,
            'integerCheck' => false,
            'type' => $sqlType,
            'notNull' => $notNull === true,
            'default' => $default,
            'collation' => $collation,
            'autoIncrement' => $autoIncrement,
            'primaryKey' => $primaryKey,
            'unique' => $unique,
        ];
        $column['sql'] = $this->getColumnSql($column);

        return $column;
    }

    private function getColumnSql(array $column): string
    {
        if ($column['autoIncrement']) {
            // the primary key is declared on the column, whether MySQL declares it there or in the table
            return 'INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL';
        }

        $sql = $column['type'];
        if ($column['primaryKey']) {
            $sql .= ' PRIMARY KEY';
        }
        if ($column['notNull']) {
            $sql .= ' NOT NULL';
        }
        if ($column['default'] !== null) {
            $sql .= ' DEFAULT ' . $column['default'];
        }
        if ($column['enumValues'] !== null) {
            $sql .= ' CHECK (' . $this->quote($column['name']) . ' IN (' . implode(', ', $column['enumValues']) . '))';
        }
        if ($column['integerCheck']) {
            // an integer column stores text it can't convert as text; MySQL rejects it
            $sql .= ' CHECK (typeof(' . $this->quote($column['name']) . ") = 'integer')";
        }
        if ($column['collation'] !== null) {
            $sql .= ' COLLATE ' . $column['collation'];
        }

        return $sql;
    }

    /**
     * MySQL names an unnamed index after its first column, adding `_2`, `_3`... if that name is taken.
     */
    private function getDefaultIndexName(array $columns, array $usedNames): string
    {
        $name = $columns[0]['column'];
        for ($suffix = 2; isset($usedNames[strtolower($name)]); $suffix++) {
            $name = $columns[0]['column'] . '_' . $suffix;
        }

        return $name;
    }

    private function findClosingParenthesis(string $sql, int $openingOffset): int
    {
        $depth = 0;
        $quote = null;
        $length = strlen($sql);
        for ($i = $openingOffset; $i < $length; $i++) {
            $char = $sql[$i];
            if ($quote !== null) {
                if ($char === $quote) {
                    $quote = null;
                }
            } elseif ($char === "'" || $char === '`' || $char === '"') {
                $quote = $char;
            } elseif ($char === '(') {
                $depth++;
            } elseif ($char === ')' && --$depth === 0) {
                return $i;
            }
        }

        throw new InvalidArgumentException('Unbalanced parentheses in: ' . $this->shorten($sql));
    }

    private function quote(string $name): string
    {
        return '`' . $name . '`';
    }

    private function unsupported(string $tableName, string $fragment): InvalidArgumentException
    {
        return new InvalidArgumentException(sprintf(
            'SQLite: unsupported definition in table %s: %s',
            $tableName,
            $this->shorten($fragment)
        ));
    }

    private function shorten(string $sql): string
    {
        $sql = trim(preg_replace('/\s+/', ' ', $sql));

        return strlen($sql) > 200 ? substr($sql, 0, 200) . '...' : $sql;
    }
}
