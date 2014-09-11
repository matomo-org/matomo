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
use Piwik\DataTable;
use Piwik\Metrics;

/**
 * Adds processed metrics columns to a {@link DataTable} using metrics that already exist.
 *
 * Columns added are:
 *
 * - **conversion_rate**: percent value of `nb_visits_converted / nb_visits
 * - **nb_actions_per_visit**: `nb_actions / nb_visits`
 * - **avg_time_on_site**: in number of seconds, `round(visit_length / nb_visits)`. Not
 *                         pretty formatted.
 * - **bounce_rate**: percent value of `bounce_count / nb_visits`
 *
 * Adding the **filter_add_columns_when_show_all_columns** query parameter to
 * an API request will trigger the execution of this Filter.
 *
 * _Note: This filter must be called before {@link ReplaceColumnNames} is called._
 *
 * **Basic usage example**
 *
 *     $dataTable->filter('AddColumnsProcessedMetrics');
 *
 * @api
 */
class AddColumnsProcessedMetrics extends BaseFilter
{
    protected $invalidDivision = 0;
    protected $roundPrecision = 2;
    protected $deleteRowsWithNoVisit = true;

    /**
     * Constructor.
     *
     * @param DataTable $table The table to eventually filter.
     * @param bool $deleteRowsWithNoVisit Whether to delete rows with no visits or not.
     */
    public function __construct($table, $deleteRowsWithNoVisit = true)
    {
        $this->deleteRowsWithNoVisit = $deleteRowsWithNoVisit;
        parent::__construct($table);
    }

    /**
     * Adds the processed metrics. See {@link AddColumnsProcessedMetrics} for
     * more information.
     *
     * @param DataTable $table
     */
    public function filter($table)
    {
        if ($this->deleteRowsWithNoVisit) {
            $this->deleteRowsWithNoVisit($table);
        }

        $metrics = new Metrics\Processed();

        foreach ($table->getRows() as $row) {
            $this->tryToAddColumn($row, 'conversion_rate', array($metrics, 'getConversionRate'));
            $this->tryToAddColumn($row, 'nb_actions_per_visit', array($metrics, 'getActionsPerVisit'));
            $this->tryToAddColumn($row, 'avg_time_on_site', array($metrics, 'getAvgTimeOnSite'));
            $this->tryToAddColumn($row, 'bounce_rate', array($metrics, 'getBounceRate'));

            $this->filterSubTable($row);
        }
    }

    private function tryToAddColumn(Row $row, $column, $callable)
    {
        try {
            $row->addColumn($column, $callable);
        } catch (\Exception $e) {

        }
    }

    private function deleteRowsWithNoVisit(DataTable $table)
    {
        $metrics = new Metrics\Processed();

        foreach ($table->getRows() as $key => $row) {
            $nbVisits  = $metrics->getColumn($row, Metrics::INDEX_NB_VISITS);
            $nbActions = $metrics->getColumn($row, Metrics::INDEX_NB_ACTIONS);

            if ($nbVisits == 0
                && $nbActions == 0
            ) {
                // case of keyword/website/campaign with a conversion for this day, but no visit, we don't show it
                $table->deleteRow($key);
            }
        }
    }
}
