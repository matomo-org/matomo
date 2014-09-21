<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\DataTable\Filter;

use Piwik\DataTable\BaseFilter;
use Piwik\DataTable\Row;
use Piwik\DataTable\Simple;
use Piwik\DataTable;
use Piwik\Metrics;

/**
 * Sorts a {@link DataTable} based on the value of a specific column.
 *
 * It is possible to specify a natural sorting (see [php.net/natsort](http://php.net/natsort) for details).
 *
 * @api
 */
class Sort extends BaseFilter
{
    protected $columnToSort;
    protected $order;

    /**
     * Constructor.
     *
     * @param DataTable $table The table to eventually filter.
     * @param string $columnToSort The name of the column to sort by.
     * @param string $order order `'asc'` or `'desc'`.
     * @param bool $naturalSort Whether to use a natural sort or not (see {@link http://php.net/natsort}).
     * @param bool $recursiveSort Whether to sort all subtables or not.
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
     * @param string $order asc|desc
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
    public function numberSort($a, $b)
    {
        $valA = $a->getColumn($this->columnToSort);
        $valB = $b->getColumn($this->columnToSort);

        if ($valA === false) {
            $valA = null;
        }

        if ($valB === false) {
            $valB = null;
        }

        return !isset($valA)
        && !isset($valB)
            ? 0
            : (
            !isset($valA)
                ? 1
                : (
            !isset($valB)
                ? -1
                : (($valA != $valB
                || !isset($a->c[Row::COLUMNS]['label']))
                ? ($this->sign * (
                    $valA
                    < $valB
                        ? -1
                        : 1)
                )
                : -1 * $this->sign * strnatcasecmp(
                    $a->c[Row::COLUMNS]['label'],
                    $b->c[Row::COLUMNS]['label'])
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
        $valA = $a->getColumn($this->columnToSort);
        $valB = $b->getColumn($this->columnToSort);

        if ($valA === false) {
            $valA = null;
        }

        if ($valB === false) {
            $valB = null;
        }

        return !isset($valA)
        && !isset($valB)
            ? 0
            : (!isset($valA)
                ? 1
                : (!isset($valB)
                    ? -1
                    : $this->sign * strnatcasecmp(
                        $valA,
                        $valB
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
        $valA = $a->getColumn($this->columnToSort);
        $valB = $b->getColumn($this->columnToSort);

        if ($valA === false) {
            $valA = null;
        }

        if ($valB === false) {
            $valB = null;
        }

        return !isset($valA)
        && !isset($valB)
            ? 0
            : (!isset($valA)
                ? 1
                : (!isset($valB)
                    ? -1
                    : $this->sign *
                    strcasecmp($valA,
                        $valB
                    )
                )
            );
    }

    /**
     * Sets the column to be used for sorting
     *
     * @param Row $row
     * @return int
     */
    protected function selectColumnToSort($row)
    {
        $value = $row->getColumn($this->columnToSort);
        if ($value !== false) {
            return $this->columnToSort;
        }

        $columnIdToName = Metrics::getMappingFromIdToName();
        // sorting by "nb_visits" but the index is Metrics::INDEX_NB_VISITS in the table
        if (isset($columnIdToName[$this->columnToSort])) {
            $column = $columnIdToName[$this->columnToSort];
            $value = $row->getColumn($column);

            if ($value !== false) {
                return $column;
            }
        }

        // eg. was previously sorted by revenue_per_visit, but this table
        // doesn't have this column; defaults with nb_visits
        $column = Metrics::INDEX_NB_VISITS;
        $value = $row->getColumn($column);
        if ($value !== false) {
            return $column;
        }

        // even though this column is not set properly in the table,
        // we select it for the sort, so that the table's internal state is set properly
        return $this->columnToSort;
    }

    /**
     * See {@link Sort}.
     *
     * @param DataTable $table
     * @return mixed
     */
    public function filter($table)
    {
        if ($table instanceof Simple) {
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
            $methodToUse = "numberSort";
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
