<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */

/**
 * Sort the DataTable based on the value of column $columnToSort ordered by $order.
 * Possible to specify a natural sorting (see php.net/natsort for details)
 *
 * @package Piwik
 * @subpackage Piwik_DataTable
 */
class Piwik_DataTable_Filter_Sort extends Piwik_DataTable_Filter
{
    protected $columnToSort;
    protected $order;

    /**
     * @param Piwik_DataTable $table
     * @param string $columnToSort   name of the column to sort by
     * @param string $order          order (asc|desc)
     * @param bool $naturalSort    use natural sort?
     * @param bool $recursiveSort  sort recursively?
     */
    public function __construct($table, $columnToSort, $order = 'desc', $naturalSort = true, $recursiveSort = false)
    {
        parent::__construct($table);
        if ($recursiveSort) {
            $table->enableRecursiveSort();
        }
        $this->columnToSort = $columnToSort;
        $this->naturalSort = $naturalSort;
        $this->setOrder($order);
    }

    /**
     * Updates the order
     *
     * @param string $order  asc|desc
     */
    public function setOrder($order)
    {
        if ($order == 'asc') {
            $this->order = 'asc';
            $this->sign = 1;
        } else {
            $this->order = 'desc';
            $this->sign = -1;
        }
    }

    /**
     * Sorting method used for sorting numbers
     *
     * @param number $a
     * @param number $b
     * @return int
     */
    public function sort($a, $b)
    {
        return !isset($a->c[Piwik_DataTable_Row::COLUMNS][$this->columnToSort])
            && !isset($b->c[Piwik_DataTable_Row::COLUMNS][$this->columnToSort])

            ? 0
            : (
            !isset($a->c[Piwik_DataTable_Row::COLUMNS][$this->columnToSort])
                ? 1
                : (
            !isset($b->c[Piwik_DataTable_Row::COLUMNS][$this->columnToSort])
                ? -1
                : (($a->c[Piwik_DataTable_Row::COLUMNS][$this->columnToSort] != $b->c[Piwik_DataTable_Row::COLUMNS][$this->columnToSort]
                || !isset($a->c[Piwik_DataTable_Row::COLUMNS]['label']))
                ? ($this->sign * (
                $a->c[Piwik_DataTable_Row::COLUMNS][$this->columnToSort]
                    < $b->c[Piwik_DataTable_Row::COLUMNS][$this->columnToSort]
                    ? -1
                    : 1)
                )
                : -1 * $this->sign * strnatcasecmp(
                    $a->c[Piwik_DataTable_Row::COLUMNS]['label'],
                    $b->c[Piwik_DataTable_Row::COLUMNS]['label'])
            )
            )
            );
    }

    /**
     * Sorting method used for sorting values natural
     *
     * @param mixed $a
     * @param mixed $b
     * @return int
     */
    function naturalSort($a, $b)
    {
        return !isset($a->c[Piwik_DataTable_Row::COLUMNS][$this->columnToSort])
            && !isset($b->c[Piwik_DataTable_Row::COLUMNS][$this->columnToSort])
            ? 0
            : (!isset($a->c[Piwik_DataTable_Row::COLUMNS][$this->columnToSort])
                ? 1
                : (!isset($b->c[Piwik_DataTable_Row::COLUMNS][$this->columnToSort])
                    ? -1
                    : $this->sign * strnatcasecmp(
                        $a->c[Piwik_DataTable_Row::COLUMNS][$this->columnToSort],
                        $b->c[Piwik_DataTable_Row::COLUMNS][$this->columnToSort]
                    )
                )
            );
    }

    /**
     * Sorting method used for sorting values
     *
     * @param mixed $a
     * @param mixed $b
     * @return int
     */
    function sortString($a, $b)
    {
        return !isset($a->c[Piwik_DataTable_Row::COLUMNS][$this->columnToSort])
            && !isset($b->c[Piwik_DataTable_Row::COLUMNS][$this->columnToSort])
            ? 0
            : (!isset($a->c[Piwik_DataTable_Row::COLUMNS][$this->columnToSort])
                ? 1
                : (!isset($b->c[Piwik_DataTable_Row::COLUMNS][$this->columnToSort])
                    ? -1
                    : $this->sign *
                        strcasecmp($a->c[Piwik_DataTable_Row::COLUMNS][$this->columnToSort],
                            $b->c[Piwik_DataTable_Row::COLUMNS][$this->columnToSort]
                        )
                )
            );
    }

    /**
     * Sets the column to be used for sorting
     *
     * @param Piwik_DataTable_Row $row
     * @return int
     */
    protected function selectColumnToSort($row)
    {
        $value = $row->getColumn($this->columnToSort);
        if ($value !== false) {
            return $this->columnToSort;
        }

        // sorting by "nb_visits" but the index is Piwik_Archive::INDEX_NB_VISITS in the table
        if (isset(Piwik_Archive::$mappingFromNameToId[$this->columnToSort])) {
            $column = Piwik_Archive::$mappingFromNameToId[$this->columnToSort];
            $value = $row->getColumn($column);

            if ($value !== false) {
                return $column;
            }
        }

        // eg. was previously sorted by revenue_per_visit, but this table
        // doesn't have this column; defaults with nb_visits
        $column = Piwik_Archive::INDEX_NB_VISITS;
        $value = $row->getColumn($column);
        if ($value !== false) {
            return $column;
        }

        // even though this column is not set properly in the table,
        // we select it for the sort, so that the table's internal state is set properly
        return $this->columnToSort;
    }

    /**
     * Sorts the given data table by defined column and sorting method
     *
     * @param Piwik_DataTable $table
     * @return mixed
     */
    public function filter($table)
    {
        if ($table instanceof Piwik_DataTable_Simple) {
            return;
        }
        if (empty($this->columnToSort)) {
            return;
        }
        $rows = $table->getRows();
        if (count($rows) == 0) {
            return;
        }
        $row = current($rows);
        if ($row === false) {
            return;
        }
        $this->columnToSort = $this->selectColumnToSort($row);

        $value = $row->getColumn($this->columnToSort);
        if (is_numeric($value)) {
            $methodToUse = "sort";
        } else {
            if ($this->naturalSort) {
                $methodToUse = "naturalSort";
            } else {
                $methodToUse = "sortString";
            }
        }
        $table->sort(array($this, $methodToUse), $this->columnToSort);
    }
}
