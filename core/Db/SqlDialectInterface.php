<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Db;

/**
 * SQL fragments whose syntax differs between database engines.
 *
 * A schema implementing {@link SchemaInterface} may also implement this interface to provide its own
 * fragments. Schemas that don't are served the MySQL fragments by {@link Schema}.
 *
 * The MySQL implementation must keep returning exactly the SQL Matomo used before these methods existed,
 * so MySQL and MariaDB behaviour stays unchanged. Engine differences belong in the engine's schema class,
 * not at the call sites. Guarded by `Piwik\Tests\Unit\Db\Schema\MysqlDialectTest`.
 */
interface SqlDialectInterface
{
    /**
     * Returns the statement prefix of an insert that skips rows conflicting with a unique key,
     * e.g. `INSERT IGNORE`. It is followed by `INTO <table>`.
     */
    public function getInsertIgnoreSql(): string;

    /**
     * Returns the clause that turns an insert into an update of the conflicting row,
     * e.g. `ON DUPLICATE KEY UPDATE`. It is followed by a list of `column = expression` assignments,
     * in which an unqualified column refers to the existing row.
     */
    public function getOnDuplicateKeyUpdateSql(): string;

    /**
     * Returns an expression usable in the assignments after {@link getOnDuplicateKeyUpdateSql()} that refers
     * to the value the insert tried to write into the given column, e.g. `VALUES(column)`.
     *
     * @param string $column An unquoted or quoted column name.
     */
    public function getInsertedValueSql(string $column): string;

    /**
     * Returns an aggregate expression concatenating `$expression` over the rows of a group.
     *
     * @param string $expression A single SQL expression. Concatenate several values with `CONCAT()`.
     * @param string $orderBy    Optional `ORDER BY` list (without the keywords) for the concatenated values.
     * @param string $separator  Literal separator. It must not contain quotes or backslashes.
     */
    public function getGroupConcatSql(string $expression, string $orderBy = '', string $separator = ','): string;

    /**
     * Returns the clause to append directly after the pattern operand of a `LIKE` whose pattern escapes
     * `%` and `_` with a backslash. The operand is a placeholder (`LIKE ?`) or a whole expression such
     * as `LIKE CONCAT('%', ?)`. The clause is either empty, where backslash is already the default escape
     * character as on MySQL, or starts with a space, e.g. ` ESCAPE '\'`. On engines that have no
     * default escape character, an escaped pattern without this clause can match different rows.
     */
    public function getLikeEscapeSql(): string;

    /**
     * Returns an expression adding an interval to, or subtracting it from, a date and time.
     *
     * @param string $datetime An SQL expression: a column, a placeholder or a function call.
     * @param string $operator `+` or `-`.
     * @param string $amount   An SQL expression giving a whole number of units.
     * @param string $unit     `SECOND`, `MINUTE`, `HOUR` or `DAY`. Longer units are excluded because
     *                         engines differ on days missing from a month.
     */
    public function getDateTimeIntervalSql(string $datetime, string $operator, string $amount, string $unit): string;

    /**
     * Returns the statement creating a temporary table, visible to the current connection only and
     * dropped when it disconnects.
     *
     * @param string $tableName    The prefixed table name.
     * @param string $definition   Column definitions and an optional `PRIMARY KEY`, in MySQL syntax.
     * @param string $tableOptions MySQL table options such as `ENGINE=MEMORY`; other engines ignore them.
     */
    public function getCreateTemporaryTableSql(string $tableName, string $definition, string $tableOptions = ''): string;

    /**
     * Returns the statement dropping a temporary table if it exists.
     */
    public function getDropTemporaryTableSql(string $tableName): string;
}
