<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Goals\DataTable\Filter;

use Piwik\Plugins\Goals\Archiver as GoalsArchiver;
use Piwik\Archive;
use Piwik\DataTable\BaseFilter;
use Piwik\DataTable;
use Piwik\Metrics;
use Piwik\Piwik;
use Piwik\Site;

class CalculateConversionPageRate extends BaseFilter
{
    /**
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
        // Collect goal ids from the whole table tree so we can look up totals once,
        // even when this filter is invoked on a parent table whose goal data lives
        // in subtable rows (e.g. Actions.getPageUrls page subpages, or flat=1).
        $goals = [];
        $this->collectGoalIds($table, $goals);

        $goalTotals = $this->getGoalTotalConversions($table, $goals);
        if (count($goalTotals) === 0) {
            return;
        }

        $this->populateRates($table, $goalTotals);
    }

    private function collectGoalIds(DataTable $table, array &$goals): void
    {
        foreach ($table->getRowsWithoutSummaryRow() as $row) {
            $goalsCol = $row->getColumn(Metrics::INDEX_GOALS);
            if (is_array($goalsCol)) {
                foreach ($goalsCol as $goalIdString => $metrics) {
                    $goals[$goalIdString] = $goalIdString;
                }
            }

            $subTable = $row->getSubtable();
            if ($subTable) {
                $this->collectGoalIds($subTable, $goals);
            }
        }
    }

    private function populateRates(DataTable $table, array $goalTotals): void
    {
        foreach ($table->getRowsWithoutSummaryRow() as $row) {
            $goalsCol = $row->getColumn(Metrics::INDEX_GOALS);
            if (is_array($goalsCol)) {
                foreach ($goalsCol as $goalIdString => $metrics) {
                    if (
                        isset($goalsCol[$goalIdString][Metrics::INDEX_GOAL_NB_CONVERSIONS_PAGE_UNIQ])
                        && isset($goalTotals[$goalIdString])
                    ) {
                        $rate = Piwik::getQuotientSafe(
                            $goalsCol[$goalIdString][Metrics::INDEX_GOAL_NB_CONVERSIONS_PAGE_UNIQ],
                            $goalTotals[$goalIdString],
                            3
                        );
                        // Prevent page rates over 100% which can happen when subpage
                        // unique counts add up to more than the parent's total.
                        if ($rate > 1) {
                            $rate = 1;
                        }

                        $goalsCol[$goalIdString][Metrics::INDEX_GOAL_NB_CONVERSIONS_PAGE_RATE] = $rate;
                    }
                }
                $row->setColumn((string) Metrics::INDEX_GOALS, $goalsCol);
            }

            $subTable = $row->getSubtable();
            if ($subTable) {
                $this->populateRates($subTable, $goalTotals);
            }
        }
    }

    /**
     * Get the conversions total for each goal in the top level datatable
     *
     * @param array $goalIds
     * @return array
     */
    private function getGoalTotalConversions(DataTable $table, array $goalIds): array
    {
        $goalTotals = [];

        if (empty($goalIds)) {
            return $goalTotals;
        }

        /** @var Site $site */
        $site = $table->getMetadata('site');
        if (empty($site)) {
            return $goalTotals;
        }
        $idSite = $site->getId();

        $period = $table->getMetadata('period');
        $periodName = $period->getLabel();
        $date = $period->getDateStart()->toString();
        $date = ($periodName === 'range' ? $date . ',' . $period->getDateEnd()->toString() : $date);
        $segment = $table->getMetadata('segment');
        $archive = Archive::build($idSite, $periodName, $date, $segment);

        $names = [];
        foreach ($goalIds as $idGoal => $g) {
            $names[$idGoal] = GoalsArchiver::getRecordName('nb_conversions', $idGoal);
        }

        $sum = $archive->getNumeric($names);
        foreach ($names as $idGoal => $name) {
            if (is_array($sum) && array_key_exists($name, $sum) && is_numeric($sum[$name])) {
                $goalTotals[$idGoal] = $sum[$name];
            } elseif (is_numeric($sum)) {
                $goalTotals[$idGoal] = $sum;
            }
        }

        return $goalTotals;
    }
}
