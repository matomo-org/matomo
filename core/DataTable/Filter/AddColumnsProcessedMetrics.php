<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\DataTable\Filter;

use Piwik\DataTable\BaseFilter;
use Piwik\DataTable;
use Piwik\Plugin\Metric;
use Piwik\Plugins\CoreHome\Columns\Metrics\ActionsPerVisit;
use Piwik\Plugins\CoreHome\Columns\Metrics\AverageTimeOnSite;
use Piwik\Plugins\CoreHome\Columns\Metrics\BounceRate;
use Piwik\Plugins\CoreHome\Columns\Metrics\ConversionRate;

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

        $extraProcessedMetrics = $table->getMetadata(DataTable::EXTRA_PROCESSED_METRICS_METADATA_NAME) ?: [];

        $extraProcessedMetrics[] = new ConversionRate();
        $extraProcessedMetrics[] = new ActionsPerVisit();
        $extraProcessedMetrics[] = new AverageTimeOnSite();
        $extraProcessedMetrics[] = new BounceRate();

        $table->setMetadata(DataTable::EXTRA_PROCESSED_METRICS_METADATA_NAME, $extraProcessedMetrics);
    }

    private function deleteRowsWithNoVisit(DataTable $table)
    {
        foreach ($table->getRows() as $key => $row) {
            $nbVisits  = (int)Metric::getMetric($row, 'nb_visits');
            $nbActions = (int)Metric::getMetric($row, 'nb_actions');

            if ($nbVisits === 0
                && $nbActions === 0
            ) {
                // case of keyword/website/campaign with a conversion for this day, but no visit, we don't show it
                $table->deleteRow($key);
            }
        }
    }
}
