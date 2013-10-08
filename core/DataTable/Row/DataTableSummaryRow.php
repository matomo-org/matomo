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
namespace Piwik\DataTable\Row;

use Piwik\DataTable\Manager;
use Piwik\DataTable;
use Piwik\DataTable\Row;

/**
 * This class creates a row from a given DataTable.
 * The row contains
 * - for each numeric column, the returned "summary" column is the sum of all the subRows
 * - for every other column, it is ignored and will not be in the "summary row"
 *
 * @see \DataTable\Row::sumRow() for more information on the algorithm
 *
 * @package Piwik
 * @subpackage DataTable
 */
class DataTableSummaryRow extends Row
{
    /**
     * @param DataTable $subTable
     */
    function __construct($subTable = null)
    {
        parent::__construct();

        if ($subTable !== null) {
            $this->sumTable($subTable);
        }
    }

    /**
     * Reset this row to an empty one and sum the associated subTable again.
     */
    public function recalculate()
    {
        $id = $this->getIdSubDataTable();
        if ($id !== null) {
            $subTable = Manager::getInstance()->getTable($id);
            $this->sumTable($subTable);
        }
    }

    /**
     * Sums a tables row with this one.
     *
     * @param DataTable $table
     */
    private function sumTable($table)
    {
        foreach ($table->getRows() as $row) {
            $this->sumRow($row, $enableCopyMetadata = false, $table->getColumnAggregationOperations());
        }
    }
}
