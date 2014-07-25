<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\DataTable\Row;

use Piwik\DataTable\Manager;
use Piwik\DataTable;
use Piwik\DataTable\Row;

/**
 * A special row whose column values are the aggregate of the row's subtable.
 *
 * This class creates sets its own columns to the sum of each row in the row's subtable.
 *
 * Non-numeric columns are bypassed during summation and do not appear in this
 * rows columns.
 *
 * See {@link Piwik\DataTable\Row::sumRow()} for more information on the algorithm.
 *
 */
class DataTableSummaryRow extends Row
{
    /**
     * Constructor.
     *
     * @param DataTable|null $subTable The subtable of this row. This parameter is mostly for
     *                                 convenience. If set, its rows will be summed to this one,
     *                                 but it will not be set as this row's subtable (so
     *                                 getSubtable() will return false).
     */
    public function __construct($subTable = null)
    {
        parent::__construct();

        if ($subTable !== null) {
            $this->sumTable($subTable);
        }
    }

    /**
     * Reset this row to an empty one and sums the associated subtable again.
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
            $this->sumRow($row, $enableCopyMetadata = false, $table->getMetadata(DataTable::COLUMN_AGGREGATION_OPS_METADATA_NAME));
        }
    }
}
