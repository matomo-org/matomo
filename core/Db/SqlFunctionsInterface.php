<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Db;

/**
 * SQL functions and statements that engines spell differently, for a schema implementing
 * {@link SchemaInterface}. Schemas that don't implement it are sent MySQL's spelling by {@link Schema}.
 */
interface SqlFunctionsInterface
{
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

    /**
     * Returns the statements to run once a temporary table is filled, so the query planner knows its size
     * before it is joined; none for an engine that needs none.
     *
     * @return string[]
     */
    public function getTemporaryTableFilledSql(string $tableName): array;

    /**
     * Returns the hour (0–23) of a date and time, or of a time.
     */
    public function getHourSql(string $datetime): string;

    /**
     * Returns the day of the week of a date, from 1 (Sunday) to 7 (Saturday), as MySQL's `DAYOFWEEK()`.
     */
    public function getDayOfWeekSql(string $datetime): string;

    /**
     * Returns the Unix time of a date and time stored in UTC, or the current Unix time without argument.
     */
    public function getUnixTimestampSql(string $datetime = ''): string;

    /**
     * Returns the current date and time, as MySQL's `NOW()` gives it to a `DATETIME` column.
     */
    public function getCurrentDateTimeSql(): string;

    /**
     * Returns the length of a text in characters.
     */
    public function getCharLengthSql(string $expression): string;

    /**
     * Returns the whole seconds from one date and time to another (negative if `$to` is earlier).
     */
    public function getSecondsBetweenSql(string $from, string $to): string;

    /**
     * Returns the greatest of several values, or NULL if one is NULL.
     */
    public function getGreatestSql(string ...$expressions): string;

    /**
     * Returns the least of several values, or NULL if one is NULL.
     */
    public function getLeastSql(string ...$expressions): string;

    /**
     * Returns the clause appended to a `SELECT` in a transaction to lock the rows it reads, starting with a space, or an empty string where the transaction already holds the database's write lock.
     */
    public function getRowLockSql(bool $exclusive = true): string;

    /**
     * Returns the statement removing every row of a table.
     */
    public function getTruncateTableSql(string $tableName): string;

    /**
     * Returns a comparison that is true when both values are equal or both are NULL, as MySQL's `<=>`.
     */
    public function getNullSafeEqualSql(string $left, string $right): string;

    /**
     * Returns what follows the column list of a `SELECT` that reads no table but has a `WHERE`: empty, or
     * starting with a space, e.g. ` FROM DUAL`.
     */
    public function getNoTableSql(): string;

    /**
     * Returns the join keyword that makes the engine read the left table first, as MySQL's `STRAIGHT_JOIN`.
     * It joins like an inner join and takes an `ON` clause.
     */
    public function getStraightJoinSql(): string;
}
