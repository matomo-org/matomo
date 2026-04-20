<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Metrics;

use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Metrics;
use Piwik\Plugin\Metric;

class Sorter
{
    /**
     * @var Sorter\Config
     */
    private $config;

    public function __construct(Sorter\Config $config)
    {
        $this->config = $config;
    }

    /**
     * Sorts the DataTable rows using the supplied callback function.
     *
     * @param DataTable $table The table to sort.
     */
    public function sort(DataTable $table)
    {
        // Operates on row IDs (plain ints) rather than ViewRow objects to avoid allocating
        // N proxy objects during sort — for large tables (e.g. 13k rows) this saves ~5 MB peak.
        $table->setTableSortedBy($this->config->primaryColumnToSort);

        $rowIds = $table->getRowIdsWithoutSummaryRow();

        $rowIdsWithValues    = [];
        $rowIdsWithoutValues = [];
        $valuesToSort        = [];

        $primaryCol = $this->config->primaryColumnToSort;
        foreach ($rowIds as $rowId) {
            $value = $table->getPackedValue($rowId, (string) $primaryCol);
            if ($value !== null && $value !== false && !is_array($value)) {
                $valuesToSort[]     = $value;
                $rowIdsWithValues[] = $rowId;
            } else {
                $rowIdsWithoutValues[] = $rowId;
            }
        }

        if ($this->config->isSecondaryColumnSortEnabled && $this->config->secondaryColumnToSort) {
            $secondaryValues = [];
            foreach ($rowIdsWithValues as $rowId) {
                $v = $table->getPackedValue($rowId, (string) $this->config->secondaryColumnToSort);
                $secondaryValues[] = $v ?? '';
            }
            array_multisort(
                $valuesToSort,    $this->config->primarySortOrder,   $this->config->primarySortFlags,
                $secondaryValues, $this->config->secondarySortOrder, $this->config->secondarySortFlags,
                $rowIdsWithValues
            );
        } else {
            array_multisort($valuesToSort, $this->config->primarySortOrder, $this->config->primarySortFlags, $rowIdsWithValues);
        }

        if (!empty($rowIdsWithoutValues) && $this->config->secondaryColumnToSort) {
            $secondaryValues = [];
            foreach ($rowIdsWithoutValues as $rowId) {
                $v = $table->getPackedValue($rowId, (string) $this->config->secondaryColumnToSort);
                $secondaryValues[] = $v ?? '';
            }
            array_multisort($secondaryValues, $this->config->secondarySortOrder, $this->config->secondarySortFlags, $rowIdsWithoutValues);
        }

        foreach ($rowIdsWithoutValues as $rowId) {
            $rowIdsWithValues[] = $rowId;
        }

        $table->reorderRows($rowIdsWithValues);
    }

    /**
     * @param string $order   'asc' or 'desc'
     * @return int
     */
    public function getPrimarySortOrder($order)
    {
        if ($order === 'asc') {
            return SORT_ASC;
        }

        return SORT_DESC;
    }

    /**
     * @param string $order   'asc' or 'desc'
     * @param string|int $secondarySortColumn  column name or column id
     * @return int
     */
    public function getSecondarySortOrder($order, $secondarySortColumn)
    {
        if ($secondarySortColumn === 'label') {
            $secondaryOrder = SORT_ASC;
            if ($order === 'asc') {
                $secondaryOrder = SORT_DESC;
            }

            return $secondaryOrder;
        }

        return $this->getPrimarySortOrder($order);
    }

    /**
     * Detect the column to be used for sorting
     *
     * @param string|int $columnToSort  column name or column id
     * @return int
     */
    public function getPrimaryColumnToSort(DataTable $table, $columnToSort)
    {
        // we fallback to nb_visits in case columnToSort does not exist
        $columnsToCheck = array($columnToSort, 'nb_visits');

        $row = $table->getFirstRow();

        foreach ($columnsToCheck as $column) {
            $column = Metric::getActualMetricColumn($table, $column);

            if ($row->hasColumn($column)) {
                // since getActualMetricColumn() returns a default value, we need to make sure it actually has that column
                return $column;
            }
        }

        return $columnToSort;
    }

    /**
     * Detect the secondary sort column to be used for sorting
     *
     * @param int|string $primaryColumnToSort
     * @return ?string
     */
    public function getSecondaryColumnToSort(Row $row, $primaryColumnToSort)
    {
        $defaultSecondaryColumn = array(Metrics::INDEX_NB_VISITS, 'nb_visits');

        if (in_array($primaryColumnToSort, $defaultSecondaryColumn)) {
            // if sorted by visits, then sort by label as a secondary column
            $column = 'label';
            $value  = $row->hasColumn($column);
            if ($value !== false) {
                return $column;
            }

            return null;
        }

        if ($primaryColumnToSort !== 'label') {
            // we do not add this by default to make sure we do not sort by label as a first and secondary column
            $defaultSecondaryColumn[] = 'label';
        }

        foreach ($defaultSecondaryColumn as $column) {
            $value = $row->hasColumn($column);
            if ($value !== false) {
                return $column;
            }
        }

        return null;
    }

    /**
     * @param string|int|null $columnToSort  A column name or column id. Make sure that column actually exists in the row.
     *                                  You might want to get a valid column via {@link getPrimaryColumnToSort()} or
     *                                  {@link getSecondaryColumnToSort()}
     * @return int
     */
    public function getBestSortFlags(DataTable $table, $columnToSort)
    {
        // when column is label we always to sort by string or natural
        if (isset($columnToSort) && $columnToSort !== 'label') {
            // Use row IDs + getPackedValue() to avoid allocating N ViewRow objects upfront.
            foreach ($table->getRowIdsWithoutSummaryRow() as $rowId) {
                $value = $table->getPackedValue($rowId, (string) $columnToSort);

                if ($value !== null && $value !== false && !is_array($value)) {
                    return is_numeric($value) ? SORT_NUMERIC : $this->getStringSortFlags();
                }
            }
        }

        return $this->getStringSortFlags();
    }

    private function getStringSortFlags()
    {
        if ($this->config->naturalSort) {
            $sortFlags = SORT_NATURAL | SORT_FLAG_CASE;
        } else {
            $sortFlags = SORT_STRING | SORT_FLAG_CASE;
        }

        return $sortFlags;
    }
}
