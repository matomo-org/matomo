<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */

/**
 * This filter replaces column names using a mapping table that maps from the old name to the new name.
 *
 * Why this filter?
 * For saving bytes in the database, you can change all the columns labels by an integer value.
 * Exemple instead of saving 10000 rows with the column name 'nb_uniq_visitors' which would cost a lot of memory,
 * we map it to the integer 1 before saving in the DB.
 * After selecting the DataTable from the DB though, you need to restore back the real names so that
 * it shows nicely in the report (XML for example).
 *
 * You can specify the mapping array to apply in the constructor.
 *
 * @package Piwik
 * @subpackage Piwik_DataTable
 */
class Piwik_DataTable_Filter_ReplaceColumnNames extends Piwik_DataTable_Filter
{
    protected $mappingToApply;

    /**
     * @param Piwik_DataTable $table  Table
     * @param array $mappingToApply   Mapping to apply. Must have the format
     *                                           array( OLD_COLUMN_NAME => NEW_COLUMN NAME,
     *                                                  OLD_COLUMN_NAME2 => NEW_COLUMN NAME2,
     *                                                 )
     */
    public function __construct($table, $mappingToApply = null)
    {
        parent::__construct($table);
        $this->mappingToApply = Piwik_Metrics::$mappingFromIdToName;
        if (!is_null($mappingToApply)) {
            $this->mappingToApply = $mappingToApply;
        }
    }

    /**
     * Executes the filter and renames the defined columns
     *
     * @param Piwik_DataTable $table
     */
    public function filter($table)
    {
        if($table instanceof Piwik_DataTable_Simple) {
            $this->filterSimple($table);
        } else {
            $this->filterTable($table);
        }
    }

    protected function filterTable($table)
    {
        foreach ($table->getRows() as $key => $row) {
            $oldColumns = $row->getColumns();
            $newColumns = $this->getRenamedColumns($oldColumns);
            $row->setColumns($newColumns);
            $this->filterSubTable($row);
        }
    }

    protected function filterSimple(Piwik_DataTable_Simple $table)
    {
        foreach ($table->getRows() as $row) {
            $columns = array_keys( $row->getColumns() );
            foreach($columns as $column) {
                $newName = $this->getRenamedColumn($column);
                if($newName) {
                    $row->renameColumn($column, $newName);
                }
            }
        }
    }

    protected function getRenamedColumn($column)
    {
        $newName = false;
        if (isset($this->mappingToApply[$column])
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
        foreach ($columnValue as $idGoal => $goalValues) {
            $mapping = Piwik_Metrics::$mappingFromIdToNameGoal;
            if ($idGoal == Piwik_Tracker_GoalManager::IDGOAL_CART) {
                $idGoal = Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_CART;
            } elseif ($idGoal == Piwik_Tracker_GoalManager::IDGOAL_ORDER) {
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
