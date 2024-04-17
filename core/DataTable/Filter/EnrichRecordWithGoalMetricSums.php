<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\DataTable\Filter;

use Piwik\DataTable\BaseFilter;
use Piwik\Metrics;
use Piwik\Tracker\GoalManager;

/**
 * This filter will check for goal metrics in every row in a DataTable (that is, the Metrics::INDEX_GOALS
 * column), and if found, adds the sum of all goal conversions and sum of revenue as two new fields.
 *
 * This filter is used by RecordBuilders during archiving.
 */
class EnrichRecordWithGoalMetricSums extends BaseFilter
{
    public function filter($table)
    {
        foreach ($table->getRows() as $row) {
            $columns = $row->getColumns();
            self::enrichWithConversions($columns);
            $row->setColumns($columns);

            $subtable = $row->getSubtable();
            if ($subtable) {
                $this->filter($subtable);
            }
        }
    }

    public static function enrichWithConversions(&$values): void
    {
        if (!isset($values[Metrics::INDEX_GOALS])) {
            return;
        }

        $revenue = $conversions = 0;
        foreach ($values[Metrics::INDEX_GOALS] as $idgoal => $goalValues) {
            // Do not sum Cart revenue since it is a lost revenue
            if ($idgoal >= GoalManager::IDGOAL_ORDER) {
                $revenue += $goalValues[Metrics::INDEX_GOAL_REVENUE];
                $conversions += $goalValues[Metrics::INDEX_GOAL_NB_CONVERSIONS];
            }
        }
        $values[Metrics::INDEX_NB_CONVERSIONS] = $conversions;

        // 25.00 recorded as 25
        if (round($revenue) == $revenue) {
            $revenue = round($revenue);
        }
        $values[Metrics::INDEX_REVENUE] = $revenue;

        // if there are no "visit" column, we force one to prevent future complications
        // eg. This helps the setDefaultColumnsToDisplay() call
        if (!isset($values[Metrics::INDEX_NB_VISITS])) {
            $values[Metrics::INDEX_NB_VISITS] = 0;
        }
    }
}
