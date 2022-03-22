<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Goals\DataTable\Filter;

use Piwik\DataTable\BaseFilter;
use Piwik\DataTable;
use Piwik\Metrics;
use Piwik\Piwik;

class CalculateConversionPageRate extends BaseFilter
{
    /**
     * Constructor.
     *
     * @param DataTable $table The table to eventually filter.
     */
    public function __construct($table)
    {
        parent::__construct($table);
    }

    /**
     * @param DataTable $table
     */
    public function filter($table)
    {
        $goalTotals = [];

        // Find all conversions for each goal in the table and store in an array
        foreach ($table->getRowsWithoutSummaryRow() as $row) {
            if (isset($row[Metrics::INDEX_GOALS])) {
                foreach ($row[Metrics::INDEX_GOALS] as $goalIdString => $metrics) {
                    if (isset($row[Metrics::INDEX_GOALS][$goalIdString][Metrics::INDEX_GOAL_NB_CONVERSIONS_ATTRIB])) {
                        if (!isset($goalTotals[$goalIdString])) {
                            $goalTotals[$goalIdString] = 0;
                        }
                        $goalTotals[$goalIdString] += $row[Metrics::INDEX_GOALS][$goalIdString][Metrics::INDEX_GOAL_NB_CONVERSIONS_ATTRIB];
                    }
                }
            }
        }

        // Walk the rows and populate the nb_conversions_page_rate with nb_conversions_page_uniq / $goalTotals[goal id]
        foreach ($table->getRowsWithoutSummaryRow() as &$row) {
            if (isset($row[Metrics::INDEX_GOALS])) {
                foreach ($row[Metrics::INDEX_GOALS] as $goalIdString => $metrics) {
                    if (isset($row[Metrics::INDEX_GOALS][$goalIdString][Metrics::INDEX_GOAL_NB_CONVERSIONS_PAGE_UNIQ])) {

                        $rate = Piwik::getQuotientSafe(
                                $row[Metrics::INDEX_GOALS][$goalIdString][Metrics::INDEX_GOAL_NB_CONVERSIONS_PAGE_UNIQ],
                                $goalTotals[$goalIdString],
                                3
                            );
                        $row[Metrics::INDEX_GOALS][$goalIdString][Metrics::INDEX_GOAL_NB_CONVERSIONS_PAGE_RATE] = $rate;
                    }
                }
            }
        }
    }
}
