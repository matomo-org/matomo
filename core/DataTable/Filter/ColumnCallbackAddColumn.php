<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\DataTable\Filter;

use Piwik\DataTable;
use Piwik\DataTable\BaseFilter;
use Piwik\Plugins\CoreHome\Columns\Metrics\CallableProcessedMetric;

/**
 * Adds a new column to every row of a {@link DataTable} based on the result of callback.
 *
 * **Basic usage example**
 *
 *     $callback = function ($visits, $timeSpent) {
 *         return round($timeSpent / $visits, 2);
 *     };
 *
 *     $dataTable->filter('ColumnCallbackAddColumn', array(array('nb_visits', 'sum_time_spent'), 'avg_time_on_site', $callback));
 *
 * @api
 */
class ColumnCallbackAddColumn extends BaseFilter
{
    /**
     * The names of the columns to pass to the callback.
     */
    private $columns;

    /**
     * The name of the column to add.
     */
    private $columnToAdd;

    /**
     * The callback to apply to each row of the DataTable. The result is added as
     * the value of a new column.
     */
    private $functionToApply;

    /**
     * Extra parameters to pass to the callback.
     */
    private $functionParameters;

    /**
     * Constructor.
     *
     * @param DataTable $table The DataTable that will be filtered.
     * @param array|string $columns The names of the columns to pass to the callback.
     * @param string $columnToAdd The name of the column to add.
     * @param callable $functionToApply The callback to apply to each row of a DataTable. The columns
     *                                  specified in `$columns` are passed to this callback.
     * @param array $functionParameters deprecated - use an [anonymous function](http://php.net/manual/en/functions.anonymous.php)
     *                                  instead.
     */
    public function __construct($table, $columns, $columnToAdd, $functionToApply, $functionParameters = array())
    {
        parent::__construct($table);

        if (!is_array($columns)) {
            $columns = array($columns);
        }

        $this->columns = $columns;
        $this->columnToAdd = $columnToAdd;
        $this->functionToApply = $functionToApply;
        $this->functionParameters = $functionParameters;
    }

    /**
     * See {@link ColumnCallbackAddColumn}.
     *
     * @param DataTable $table The table to filter.
     */
    public function filter($table)
    {
        $columns = $this->columns;
        $functionParams  = $this->functionParameters;
        $functionToApply = $this->functionToApply;

        $extraProcessedMetrics = $table->getMetadata(DataTable::EXTRA_PROCESSED_METRICS_METADATA_NAME);

        if (empty($extraProcessedMetrics)) {
            $extraProcessedMetrics = array();
        }

        $metric = new CallableProcessedMetric($this->columnToAdd, function (DataTable\Row $row) use ($columns, $functionParams, $functionToApply) {

            $columnValues = array();
            foreach ($columns as $column) {
                $columnValues[] = $row->getColumn($column);
            }

            $parameters = array_merge($columnValues, $functionParams);

            return call_user_func_array($functionToApply, $parameters);
        }, $columns);
        $extraProcessedMetrics[] = $metric;

        $table->setMetadata(DataTable::EXTRA_PROCESSED_METRICS_METADATA_NAME, $extraProcessedMetrics);

        foreach ($table->getRows() as $row) {
            $row->setColumn($this->columnToAdd, $metric->compute($row));
            $this->filterSubTable($row);
        }
    }
}
