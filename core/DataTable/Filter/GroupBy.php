<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\DataTable\Filter;

use Piwik\DataTable;
use Piwik\DataTable\BaseFilter;
use Piwik\DataTable\Row;

/**
 * DataTable filter that will group {@link DataTable} rows together based on the results
 * of a reduce function. Rows with the same reduce result will be summed and merged.
 *
 * _NOTE: This filter should never be queued, it must be applied directly on a {@link DataTable}._
 *
 * **Basic usage example**
 *
 *     // group URLs by host
 *     $dataTable->filter('GroupBy', array('label', function ($labelUrl) {
 *         return parse_url($labelUrl, PHP_URL_HOST);
 *     }));
 *
 * @api
 */
class GroupBy extends BaseFilter
{
    /**
     * The name of the columns to reduce.
     * @var string
     */
    private $groupByColumn;

    /**
     * A callback that modifies the $groupByColumn of each row in some way. Rows with
     * the same reduction result will be added together.
     */
    private $reduceFunction;

    /**
     * Extra parameters to pass to the reduce function.
     */
    private $parameters;

    /**
     * Constructor.
     *
     * @param DataTable $table The DataTable to filter.
     * @param string $groupByColumn The column name to reduce.
     * @param callable $reduceFunction The reduce function. This must alter the `$groupByColumn`
     *                                 columng in some way. If not set then the filter will group by the raw column value.
     * @param array $parameters deprecated - use an [anonymous function](http://php.net/manual/en/functions.anonymous.php)
     *                          instead.
     */
    public function __construct($table, $groupByColumn, $reduceFunction = null, $parameters = array())
    {
        parent::__construct($table);

        $this->groupByColumn  = $groupByColumn;
        $this->reduceFunction = $reduceFunction;
        $this->parameters     = $parameters;
    }

    /**
     * See {@link GroupBy}.
     *
     * @param DataTable $table
     */
    public function filter($table)
    {
        /** @var Row[] $groupByRows */
        $groupByRows = array();
        $nonGroupByRowIds = array();

        foreach ($table->getRowsWithoutSummaryRow() as $rowId => $row) {
            $groupByColumnValue = $row->getColumn($this->groupByColumn);
            $groupByValue = $groupByColumnValue;

            // reduce the group by column of this row
            if ($this->reduceFunction) {
                $parameters   = array_merge(array($groupByColumnValue), $this->parameters);
                $groupByValue = call_user_func_array($this->reduceFunction, $parameters);
            }

            if (!isset($groupByRows[$groupByValue])) {
                // if we haven't encountered this group by value before, we mark this row as a
                // row to keep, and change the group by column to the reduced value.
                $groupByRows[$groupByValue] = $row;
                $row->setColumn($this->groupByColumn, $groupByValue);
            } else {
                // if we have already encountered this group by value, we add this row to the
                // row that will be kept, and mark this one for deletion
                $groupByRows[$groupByValue]->sumRow($row, $copyMeta = true, $table->getMetadata(DataTable::COLUMN_AGGREGATION_OPS_METADATA_NAME));
                $nonGroupByRowIds[] = $rowId;
            }
        }

        if ($this->groupByColumn === 'label') {
            $table->setLabelsHaveChanged();
        }

        // delete the unneeded rows.
        $table->deleteRows($nonGroupByRowIds);
    }
}
