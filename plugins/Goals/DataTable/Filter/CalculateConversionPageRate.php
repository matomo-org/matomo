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
use Piwik\Context;
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

        // Find all goal ids used in the table and store in an array
        $goals = [];
        foreach ($table->getRowsWithoutSummaryRow() as $row) {
            if (isset($row[Metrics::INDEX_GOALS])) {
                foreach ($row[Metrics::INDEX_GOALS] as $goalIdString => $metrics) {
                    $goals[$goalIdString] = $goalIdString;
                }
            }
        }

        // Get the total top-level conversions for the goals in the table
        $goalTotals = $this->getGoalTotalConversions($table, $goals);
        $fallbackGoalTotals = $this->getGoalTotalConversionsFromTable($table, $goals);
        foreach ($goals as $idGoal) {
            if (
                (!isset($goalTotals[$idGoal]) || (float) $goalTotals[$idGoal] <= 0.0)
                && isset($fallbackGoalTotals[$idGoal])
                && (float) $fallbackGoalTotals[$idGoal] > 0.0
            ) {
                $goalTotals[$idGoal] = (float) $fallbackGoalTotals[$idGoal];
            }
        }

        if (count($goalTotals) === 0) {
            return;
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
        $names = [];
        foreach ($goalIds as $idGoal => $g) {
            $names[$idGoal] = GoalsArchiver::getRecordName('nb_conversions', $idGoal);
        }

        $sum = Context::executeWithQueryParameters(['requestedReport' => ''], function () use ($idSite, $periodName, $date, $segment, $names) {
            $archive = Archive::build($idSite, $periodName, $date, $segment);
            return $archive->getNumeric($names);
        });
        foreach ($names as $idGoal => $name) {
            if (is_array($sum) && array_key_exists($name, $sum) && is_numeric($sum[$name])) {
                $goalTotals[$idGoal] = $sum[$name];
            } elseif (is_numeric($sum)) {
                $goalTotals[$idGoal] = $sum;
            }
        }

        return $goalTotals;
    }

    private function getGoalTotalConversionsFromTable(DataTable $table, array $goalIds): array
    {
        $goalTotals = [];
        if (empty($goalIds)) {
            return $goalTotals;
        }

        foreach ($table->getRows() as $row) {
            $goalsColumn = $row[Metrics::INDEX_GOALS] ?? null;
            if (!is_array($goalsColumn)) {
                continue;
            }

            foreach ($goalIds as $idGoal) {
                if (!isset($goalsColumn[$idGoal]) || !is_array($goalsColumn[$idGoal])) {
                    continue;
                }

                $entryConversions = $goalsColumn[$idGoal][Metrics::INDEX_GOAL_NB_CONVERSIONS_ENTRY] ?? null;
                if (!is_numeric($entryConversions)) {
                    continue;
                }

                $goalTotals[$idGoal] = ($goalTotals[$idGoal] ?? 0) + (float) $entryConversions;
            }
        }

        return $goalTotals;
    }
}
