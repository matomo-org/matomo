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
 * This class creates a row from a given DataTable.
 * The row contains
 * - for each numeric column, the returned "summary" column is the sum of all the subRows
 * - for every other column, it is ignored and will not be in the "summary row"
 *
 * @see Piwik_DataTable_Row::sumRow() for more information on the algorithm
 *
 * @package Piwik
 * @subpackage Piwik_DataTable
 */
class Piwik_DataTable_Row_DataTableSummary extends Piwik_DataTable_Row
{
    /**
     * @param Piwik_DataTable $subTable
     */
    function __construct($subTable = null)
    {
        parent::__construct();

        if ($subTable !== null) {
            $this->sumTable($subTable);
        }
    }

    /**
     * Reset this row to an empty one and sum the associated subtable again.
     */
    public function recalculate()
    {
        $id = $this->getIdSubDataTable();
        if ($id !== null) {
            $subtable = Piwik_DataTable_Manager::getInstance()->getTable($id);
            $this->sumTable($subtable);
        }
    }

    /**
     * Sums a tables row with this one.
     *
     * @param Piwik_DataTable $table
     */
    private function sumTable($table)
    {
        foreach ($table->getRows() as $row) {
            $this->sumRow($row, $enableCopyMetadata = false, $table->getColumnAggregationOperations());
        }
    }
}
