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

    private $goalConversionTotals;

    /**
     * Constructor.
     *
     * @param DataTable $table                 The table to eventually filter.
     * @param array     $goalConversionTotals  Associative array of goalIds and total conversions for the table period
     */
    public function __construct($table, $goalConversionTotals)
    {
        parent::__construct($table);
        $this->goalConversionTotals = $goalConversionTotals;
    }

    /**
     * @param DataTable $table
     */
    public function filter($table)
    {

        if (count($this->goalConversionTotals) === 0) {
            return;
        }

        // Walk the rows and populate the nb_conversions_page_rate with nb_conversions_page_uniq / goalConversionTotals[goal id]
        foreach ($table->getRowsWithoutSummaryRow() as &$row) {
            if (isset($row[Metrics::INDEX_GOALS])) {
                foreach ($row[Metrics::INDEX_GOALS] as $goalIdString => $metrics) {
                    if (isset($row[Metrics::INDEX_GOALS][$goalIdString][Metrics::INDEX_GOAL_NB_CONVERSIONS_PAGE_UNIQ])) {

                        $rate = Piwik::getQuotientSafe(
                                $row[Metrics::INDEX_GOALS][$goalIdString][Metrics::INDEX_GOAL_NB_CONVERSIONS_PAGE_UNIQ],
                                $this->goalConversionTotals[$goalIdString],
                                3
                            );
                        // Prevent page rates over 100% which can happen when there are subpages
                        if ($rate > 1) {
                            $rate = 1;
                        }

                        $row[Metrics::INDEX_GOALS][$goalIdString][Metrics::INDEX_GOAL_NB_CONVERSIONS_PAGE_RATE] = $rate;
                    }
                }
            }
        }
    }

}
