<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\DataTable;

/**
 * The common interface of {@link \Piwik\DataTable} and {@link \Piwik\DataTable\Map}. Use it as a type; it is
 * not meant to be implemented by plugins.
 *
 * @api
 */
interface DataTableInterface
{
    /**
     * Returns the number of rows (for a DataTable) or contained tables (for a Map).
     *
     * @return int The number of rows or tables.
     */
    public function getRowsCount();

    /**
     * Queues a filter to be run when {@link applyQueuedFilters()} is called.
     *
     * @param string|callable $className The class name of the filter, eg. `'Limit'`, or a closure.
     * @param array $parameters The parameters to give to the filter.
     */
    public function queueFilter($className, $parameters = array());

    /**
     * Applies all filters that were previously queued.
     */
    public function applyQueuedFilters();

    /**
     * Applies a filter to the table.
     *
     * @param string|callable $className The class name of the filter, eg. `'Sort'`, or a closure.
     * @param array $parameters The parameters to give to the filter.
     */
    public function filter($className, $parameters = array());

    /**
     * Invokes `$filter` with this table and every table in `$otherTables`.
     *
     * @param array $otherTables The other tables to pass to the filter.
     * @param callable $filter A function like `function (DataTable $thisTable, $otherTable1, $otherTable2) {}`.
     * @return mixed The result of `$filter`, or for a Map an array of the results indexed by table label.
     */
    public function multiFilter($otherTables, $filter);

    /**
     * Returns the first row of a DataTable, or the first table of a Map.
     *
     * @return \Piwik\DataTable\Row|\Piwik\DataTable|Map|false `false` if there is none.
     */
    public function getFirstRow();

    /**
     * Returns a string representation of the table for convenient viewing.
     *
     * @return string The string representation.
     */
    public function __toString();

    /**
     * Enables recursive sorting, so sorting also sorts all subtables.
     */
    public function enableRecursiveSort();

    /**
     * Renames a column in every row.
     *
     * @param string $oldName The old column name.
     * @param string $newName The new column name.
     */
    public function renameColumn($oldName, $newName);

    /**
     * Deletes several columns by name in every row.
     *
     * @param array<string|int> $columns The names of the columns to delete.
     * @param bool $deleteRecursiveInSubtables Has no effect; subtables are always processed.
     */
    public function deleteColumns($columns, $deleteRecursiveInSubtables = false);

    /**
     * Deletes a row by ID (for a DataTable) or a contained table by label (for a Map).
     *
     * @param int|string $id The row ID or table label.
     */
    public function deleteRow($id);

    /**
     * Deletes a column by name in every row.
     *
     * @param string|int $name The column name.
     */
    public function deleteColumn($name);

    /**
     * Returns all values of a column.
     *
     * @param string|int $name The column name.
     * @return array The column values.
     */
    public function getColumn($name);

    /**
     * Returns the names of the columns the table contains.
     *
     * @return array The column names.
     */
    public function getColumns();

    /**
     * Deletes row metadata by name in every row.
     *
     * @param string $name The metadata name.
     * @param bool $deleteRecursiveInSubtables Has no effect; subtables are always processed.
     */
    public function deleteRowsMetadata($name, $deleteRecursiveInSubtables = false);

    /**
     * Sets whether the table was built without reading archives.
     *
     * @internal
     */
    public function setAsBuiltWithoutArchives(bool $flag): void;

    /**
     * Returns whether the table was built without reading archives.
     *
     * @internal
     */
    public function wasBuiltWithoutArchives(): bool;
}
