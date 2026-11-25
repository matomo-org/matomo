<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Archive;

use Piwik\DataTable;

/**
 * Query interface for reading archived data. Instances are created with {@link \Piwik\Archive::build()}.
 * Use it as a type; it is not meant to be implemented by plugins.
 *
 * @api
 */
interface ArchiveQuery
{
    /**
     * Returns archived numeric records.
     *
     * @param string|string[] $names One or more record names.
     * @return float|array The value as a float (0 if there is no data) when one site, period and name are
     *                     queried; otherwise an array indexed by site ID and period (each only when several are queried),
     *                     then record name.
     */
    public function getNumeric($names);

    /**
     * Returns archived numeric records as a DataTable with at most one row, holding one column per record name.
     * When several sites or periods are queried, returns a DataTable\Map indexed by site ID, then period.
     *
     * @param string|string[] $names One or more record names.
     * @return DataTable|DataTable\Map A DataTable, or a DataTable\Map when several sites or periods are queried.
     */
    public function getDataTableFromNumeric($names);

    /**
     * Returns archived numeric records like {@link getDataTableFromNumeric()}, but merges the child tables
     * of the result into a single table.
     *
     * @param string|string[] $names One or more record names.
     * @return DataTable|DataTable\Map
     * @internal
     */
    public function getDataTableFromNumericAndMergeChildren($names);

    /**
     * Returns an archived blob record as a DataTable.
     *
     * @param string $name The record name.
     * @param int|string|null $idSubtable The ID of the subtable to return, or `null` for the root table.
     * @return DataTable|DataTable\Map A DataTable, or a DataTable\Map when several sites or periods are queried.
     */
    public function getDataTable($name, $idSubtable = null);

    /**
     * Returns an archived blob record as a DataTable with its subtables loaded and attached to their rows.
     *
     * @param string $name The record name.
     * @param int|string|null $idSubtable The ID of the subtable to start from, or `null` for the root table.
     * @param int|null $depth The maximum depth of subtables to load, or `null` for all.
     * @param bool $addMetadataSubtableId Whether to add the subtable ID to the row metadata.
     * @return DataTable|DataTable\Map A DataTable, or a DataTable\Map when several sites or periods are queried.
     */
    public function getDataTableExpanded($name, $idSubtable = null, $depth = null, $addMetadataSubtableId = true);
}
