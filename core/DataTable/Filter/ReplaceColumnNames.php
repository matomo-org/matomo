<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\DataTable\Filter;

use Piwik\DataTable\BaseFilter;
use Piwik\DataTable\Simple;
use Piwik\DataTable;
use Piwik\Metrics;
use Piwik\Piwik;
use Piwik\Tracker\GoalManager;

/**
 * Replaces column names in each row of a table using an array that maps old column
 * names new ones.
 *
 * If no mapping is provided, this column will use one that maps index metric names
 * (which are integers) with their string column names. In the database, reports are
 * stored with integer metric names because it results in blobs that take up less space.
 * When loading the reports, the column names must be replaced, which is handled by this
 * class. (See {@link Piwik\Metrics} for more information about integer metric names.)
 *
 * **Basic example**
 *
 *     // filter use in a plugin's API method
 *     public function getMyReport($idSite, $period, $date, $segment = false, $expanded = false)
 *     {
 *         $dataTable = Archive::createDataTableFromArchive('MyPlugin_MyReport', $idSite, $period, $date, $segment, $expanded);
 *         $dataTable->queueFilter('ReplaceColumnNames');
 *         return $dataTable;
 *     }
 *
 * @api
 */
class ReplaceColumnNames extends BaseFilter
{
    protected $mappingToApply;

    /**
     * Constructor.
     *
     * @param DataTable $table The table that will be eventually filtered.
     * @param array|null $mappingToApply The name mapping to apply. Must map old column names
     *                                   with new ones, eg,
     *
     *                                       array('OLD_COLUMN_NAME' => 'NEW_COLUMN NAME',
     *                                             'OLD_COLUMN_NAME2' => 'NEW_COLUMN NAME2')
     *
     *                                   If null, {@link Piwik\Metrics::$mappingFromIdToName} is used.
     */
    public function __construct($table, $mappingToApply = null)
    {
        parent::__construct($table);
        $this->mappingToApply = Metrics::getMappingFromIdToName();
        if (!is_null($mappingToApply)) {
            $this->mappingToApply = $mappingToApply;
        }
    }

    /**
     * See {@link ReplaceColumnNames}.
     *
     * @param DataTable $table
     */
    public function filter($table)
    {
        if ($table instanceof Simple) {
            $this->filterSimple($table);
        } else {
            $this->filterTable($table);
        }
    }

    /**
     * @param DataTable $table
     */
    protected function filterTable($table)
    {
        $rows = $table->getRows();

        $totalRow = $table->getTotalsRow();
        if ($totalRow) {
            $rows[] = $totalRow;
        }

        foreach ($rows as $row) {
            $newColumns = $this->getRenamedColumns($row->getColumns());
            $row->setColumns($newColumns);
            $this->filterSubTable($row);
        }
    }

    /**
     * @param Simple $table
     */
    protected function filterSimple(Simple $table)
    {
        foreach ($table->getRows() as $row) {
            $columns = array_keys($row->getColumns());
            foreach ($columns as $column) {
                $newName = $this->getRenamedColumn($column);
                if ($newName) {
                    $row->renameColumn($column, $newName);
                }
            }
        }
    }

    protected function getRenamedColumn($column)
    {
        $newName = false;
        if (
            isset($this->mappingToApply[$column])
            && $this->mappingToApply[$column] != $column
        ) {
            $newName = $this->mappingToApply[$column];
        }
        return $newName;
    }

    /**
     * Checks the given columns and renames them if required
     *
     * @param array $columns
     * @return array
     */
    protected function getRenamedColumns($columns)
    {
        $newColumns = array();
        foreach ($columns as $columnName => $columnValue) {
            $renamedColumn = $this->getRenamedColumn($columnName);
            if ($renamedColumn) {
                if ($renamedColumn == 'goals') {
                    $columnValue = $this->flattenGoalColumns($columnValue);
                }
                // If we happen to rename a column to a name that already exists,
                // sum both values in the column. This should really not happen, but
                // we introduced in 1.1 a new dataTable indexing scheme for Actions table, and
                // could end up with both strings and their int indexes counterpart in a monthly/yearly dataTable
                // built from DataTable with both formats
                if (isset($newColumns[$renamedColumn])) {
                    $columnValue += $newColumns[$renamedColumn];
                }

                $columnName = $renamedColumn;
            }
            $newColumns[$columnName] = $columnValue;
        }
        return $newColumns;
    }

    /**
     * @param $columnValue
     * @return array
     */
    protected function flattenGoalColumns($columnValue)
    {
        $newSubColumns = array();
        // sort by key (idgoal) to ensure a static result
        ksort($columnValue);
        foreach ($columnValue as $idGoal => $goalValues) {
            $mapping = Metrics::$mappingFromIdToNameGoal;
            if ($idGoal == GoalManager::IDGOAL_CART) {
                $idGoal = Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_CART;
            } elseif ($idGoal == GoalManager::IDGOAL_ORDER) {
                $idGoal = Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER;
            }
            foreach ($goalValues as $id => $goalValue) {
                $subColumnName = $mapping[$id];
                $newSubColumns['idgoal=' . $idGoal][$subColumnName] = $goalValue;
            }
        }
        return $newSubColumns;
    }
}
