<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
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
        // all that code is in here and not in separate methods for best performance. It does make a difference once
        // php has to copy many (eg 50k) rows otherwise.

        $table->setTableSortedBy($this->config->primaryColumnToSort);

        $rows = $table->getRowsWithoutSummaryRow();

        // we need to sort rows that have a value separately from rows that do not have a value since we always want
        // to append rows that do not have a value at the end.
        $rowsWithValues    = array();
        $rowsWithoutValues = array();

        $valuesToSort = array();
        foreach ($rows as $key => $row) {
            $value = $this->getColumnValue($row);
            if (isset($value)) {
                $valuesToSort[] = $value;
                $rowsWithValues[] = $row;
            } else {
                $rowsWithoutValues[] = $row;
            }
        }

        unset($rows);

        if ($this->config->isSecondaryColumnSortEnabled && $this->config->secondaryColumnToSort) {
            $secondaryValues = array();
            foreach ($rowsWithValues as $key => $row) {
                $secondaryValues[$key] = $row->getColumn($this->config->secondaryColumnToSort);
            }

            array_multisort($valuesToSort, $this->config->primarySortOrder, $this->config->primarySortFlags, $secondaryValues, $this->config->secondarySortOrder, $this->config->secondarySortFlags, $rowsWithValues);

        } else {
            array_multisort($valuesToSort, $this->config->primarySortOrder, $this->config->primarySortFlags, $rowsWithValues);
        }

        if (!empty($rowsWithoutValues) && $this->config->secondaryColumnToSort) {
            $secondaryValues = array();
            foreach ($rowsWithoutValues as $key => $row) {
                $secondaryValues[$key] = $row->getColumn($this->config->secondaryColumnToSort);
            }

            array_multisort($secondaryValues, $this->config->secondarySortOrder, $this->config->secondarySortFlags, $rowsWithoutValues);
        }

        unset($secondaryValues);

        foreach ($rowsWithoutValues as $row) {
            $rowsWithValues[] = $row;
        }

        $table->setRows(array_values($rowsWithValues));
    }

    private function getColumnValue(Row $row)
    {
        $value = $row->getColumn($this->config->primaryColumnToSort);

        if ($value === false || is_array($value)) {
            return null;
        }

        return $value;
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
     * @param DataTable $table
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
     * @param Row $row
     * @param int|string $primaryColumnToSort
     * @return int
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
    }

    /**
     * @param DataTable $table
     * @param string|int $columnToSort  A column name or column id. Make sure that column actually exists in the row.
     *                                  You might want to get a valid column via {@link getPrimaryColumnToSort()} or
     *                                  {@link getSecondaryColumnToSort()}
     * @return int
     */
    public function getBestSortFlags(DataTable $table, $columnToSort)
    {
        // when column is label we always to sort by string or natural
        if (isset($columnToSort) && $columnToSort !== 'label') {
            foreach ($table->getRowsWithoutSummaryRow() as $row) {
                $value = $row->getColumn($columnToSort);

                if ($value !== false && $value !== null && !is_array($value)) {

                    if (is_numeric($value)) {
                        $sortFlags = SORT_NUMERIC;
                    } else {
                        $sortFlags = $this->getStringSortFlags();
                    }

                    return $sortFlags;
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