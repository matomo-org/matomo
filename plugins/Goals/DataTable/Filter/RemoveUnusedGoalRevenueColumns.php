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

class RemoveUnusedGoalRevenueColumns extends BaseFilter
{
    /**
     * @param DataTable $table
     */
    public function filter($table)
    {
        $goals = $this->getGoalsInTable($table);

        if (count($goals) === 0) {
            return;
        }

        $columnNames = [
                'revenue',
                'revenue_entry',
                'revenue_per_entry',
                'revenue_per_visit',
                'revenue_attrib',
            ];

        // Build array of columns to check
        $columnsToCheck = [];
        foreach ($goals as $goalId) {
            foreach ($columnNames as $columnName) {
                $columnsToCheck['goal_' . $goalId . '_' . $columnName] = true;
            }
        }

        // Check if there are any values in each column
        foreach ($table->getRowsWithoutSummaryRow() as $row) {
            foreach ($columnsToCheck as $colName => $shouldRemove) {
                if (isset($row[$colName]) && $row[$colName] > 0) {
                    $columnsToCheck[$colName] = false;
                }
            }
        }

        $columnsToCheck = array_filter($columnsToCheck);

        if (empty($columnsToCheck)) {
            return;
        }

        $table->deleteColumns(array_keys($columnsToCheck));
    }

    /**
     * Get the ids of all goals used in the table
     *
     * @param DataTable $table
     *
     * @return array
     */
    private function getGoalsInTable(DataTable &$table)
    {
        $result = [];
        foreach ($table->getRows() as $row) {
            $goals = $row->getColumn('goals');
            if (!$goals) {
                continue;
            }

            foreach ($goals as $goalIdString => $metrics) {
                $result[] = substr($goalIdString, 7);
            }
        }
        return array_unique($result);
    }
}
