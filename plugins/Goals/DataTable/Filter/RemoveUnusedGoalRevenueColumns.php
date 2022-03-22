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
                $columnsToCheck['goal_'.$goalId.'_'.$columnName] = true;
            }
        }

        // Check if there are any values in each column
        foreach ($table->getRowsWithoutSummaryRow() as $row) {

            $didChecks = false;
            foreach ($columnsToCheck as $colName => $shouldRemove) {
                if ($shouldRemove) {
                    $didChecks = true;
                }
                if (isset($row[$colName]) && $row[$colName] > 0) {
                    $columnsToCheck[$colName] = false;
                }
            }
            if (!$didChecks) {
                break 1;
            }

        }

        $columnsToRemove = [];
        foreach ($columnsToCheck as $c => $shouldRemove) {
            if ($shouldRemove) {
                $columnsToRemove[] = $c;
            }
        }

        // We can't remove the columns from here, it needs to be done in the visualisation, so set a metadata value
        // on the datatable to indicate the columns to be removed, the visualisation can then check for this and
        // adjust the view config
        $table->setMetadata('excluded_goal_columns', json_encode($columnsToRemove, true));

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
        $result = array();
        foreach ($table->getRows() as $row) {
            $goals = $row->getMetadata('goals');
            if (!$goals) {
                continue;
            }

            foreach ($goals as $goalId) {
                $result[] = $goalId;
            }
        }
        return array_unique($result);
    }

}
