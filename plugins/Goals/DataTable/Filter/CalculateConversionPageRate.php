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
use Piwik\Plugin\Metric;
use Piwik\DataTable;
use Piwik\Metrics;
use Piwik\Metrics\Formatter;
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

        $formatter = new Formatter();

        $goalTotals = [];

        // Get all goal ids for the table
        $goals = [];
        foreach ($this->getGoalsInTable($table) as $g) {
            $goals[] = $g;
            $goalTotals[$g] = 0;
        }

        // Find all conversions for the table and store in an array
        foreach ($table->getRowsWithoutSummaryRow() as $row) {
            foreach ($goals as $goalId) {
                if (isset($row[Metrics::INDEX_GOALS][$goalId][Metrics::INDEX_GOAL_NB_CONVERSIONS_FLOAT])) {
                    $goalTotals[$goalId] += $row[Metrics::INDEX_GOALS][$goalId][Metrics::INDEX_GOAL_NB_CONVERSIONS_FLOAT];
                }
            }
        }

        // Walk the rows and populate the goal_[x]_nb_conversions_page_rate with goal_[x]_nb_conversions / $goalTotals[goal id]
        foreach ($table->getRowsWithoutSummaryRow() as $row) {
            foreach ($goals as $goalId) {
                if (isset($row[Metrics::INDEX_GOALS][$goalId][Metrics::INDEX_GOAL_NB_CONVERSIONS])) {

                    $v = $formatter->getPrettyPercentFromQuotient(
                            Piwik::getQuotientSafe($row[Metrics::INDEX_GOALS][$goalId][Metrics::INDEX_GOAL_NB_CONVERSIONS_PAGE_UNIQ],
                            $goalTotals[$goalId], 3));
                    $row[Metrics::INDEX_GOALS][$goalId][Metrics::INDEX_GOAL_NB_CONVERSIONS_PAGE_RATE] = $v;
                    // This filter runs after the goal values have been copied from numeric named columns in the subtable
                    // to labelled columns on the row, so we need to add the goal_x_abc column too
                    $row['goal_'.$goalId.'_nb_conversion_page_rate'] = $v;
                }
            }
        }

    }

    private function getGoalsInTable(DataTable $table)
    {
        $result = array();
        foreach ($table->getRows() as $row) {
            $goals = Metric::getMetric($row, 'goals');
            if (!$goals) {
                continue;
            }

            foreach ($goals as $goalId => $goalMetrics) {
                $goalId = str_replace("idgoal=", "", $goalId);
                $result[] = $goalId;
            }
        }
        return array_unique($result);
    }
}
