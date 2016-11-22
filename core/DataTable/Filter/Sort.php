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
use Piwik\Metrics\Sorter;

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
    protected $naturalSort;
    protected $isSecondaryColumnSortEnabled;

    const ORDER_DESC = 'desc';
    const ORDER_ASC  = 'asc';

    /**
     * Constructor.
     *
     * @param DataTable $table The table to eventually filter.
     * @param string $columnToSort The name of the column to sort by.
     * @param string $order order `'asc'` or `'desc'`.
     * @param bool $naturalSort Whether to use a natural sort or not (see {@link http://php.net/natsort}).
     * @param bool $recursiveSort Whether to sort all subtables or not.
     * @param bool $doSortBySecondaryColumn If true will sort by a secondary column. The column is automatically
     *                                    detected and will be either nb_visits or label, if possible.
     */
    public function __construct($table, $columnToSort, $order = 'desc', $naturalSort = true, $recursiveSort = true, $doSortBySecondaryColumn = false)
    {
        parent::__construct($table);

        if ($recursiveSort) {
            $table->enableRecursiveSort();
        }

        $this->columnToSort = $columnToSort;
        $this->naturalSort = $naturalSort;
        $this->order = strtolower($order);
        $this->isSecondaryColumnSortEnabled = $doSortBySecondaryColumn;
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

        if (!$table->getRowsCountWithoutSummaryRow()) {
            return;
        }

        $row = $table->getFirstRow();

        if ($row === false) {
            return;
        }

        $config = new Sorter\Config();
        $sorter = new Sorter($config);

        $config->naturalSort = $this->naturalSort;
        $config->primaryColumnToSort   = $sorter->getPrimaryColumnToSort($table, $this->columnToSort);
        $config->primarySortOrder      = $sorter->getPrimarySortOrder($this->order);
        $config->primarySortFlags      = $sorter->getBestSortFlags($table, $config->primaryColumnToSort);
        $config->secondaryColumnToSort = $sorter->getSecondaryColumnToSort($row, $config->primaryColumnToSort);
        $config->secondarySortOrder    = $sorter->getSecondarySortOrder($this->order, $config->secondaryColumnToSort);
        $config->secondarySortFlags    = $sorter->getBestSortFlags($table, $config->secondaryColumnToSort);

        // secondary sort should not be needed for all other sort flags (eg string/natural sort) as label is unique and would make it slower
        $isSecondaryColumnSortNeeded = $config->primarySortFlags === SORT_NUMERIC;
        $config->isSecondaryColumnSortEnabled = $this->isSecondaryColumnSortEnabled && $isSecondaryColumnSortNeeded;

        $this->sort($sorter, $table);
    }

    private function sort(Sorter $sorter, DataTable $table)
    {
        $sorter->sort($table);

        if ($table->isSortRecursiveEnabled()) {
            foreach ($table->getRowsWithoutSummaryRow() as $row) {
                $subTable = $row->getSubtable();

                if ($subTable) {
                    $subTable->enableRecursiveSort();
                    $this->sort($sorter, $subTable);
                }
            }
        }
    }

}