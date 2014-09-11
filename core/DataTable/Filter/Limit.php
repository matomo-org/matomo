<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\DataTable\Filter;

use Piwik\DataTable;
use Piwik\DataTable\BaseFilter;

/**
 * Delete all rows from the table that are not in the given [offset, offset+limit) range.
 *
 * **Basic example usage**
 *
 *     // delete all rows from 5 -> 15
 *     $dataTable->filter('Limit', array(5, 10));
 *
 * @api
 */
class Limit extends BaseFilter
{
    /**
     * Constructor.
     *
     * @param DataTable $table The DataTable that will be filtered eventually.
     * @param int $offset The starting row index to keep.
     * @param int $limit Number of rows to keep (specify -1 to keep all rows).
     * @param bool $keepSummaryRow Whether to keep the summary row or not.
     */
    public function __construct($table, $offset, $limit = -1, $keepSummaryRow = false)
    {
        parent::__construct($table);
        $this->offset = $offset;

        $this->limit = $limit;
        $this->keepSummaryRow = $keepSummaryRow;
    }

    /**
     * See {@link Limit}.
     *
     * @param DataTable $table
     */
    public function filter($table)
    {
        $table->setMetadata(DataTable::TOTAL_ROWS_BEFORE_LIMIT_METADATA_NAME, $table->getRowsCount());

        if ($this->keepSummaryRow) {
            $summaryRow = $table->getRowFromId(DataTable::ID_SUMMARY_ROW);
        }

        // we delete from 0 to offset
        if ($this->offset > 0) {
            $table->deleteRowsOffset(0, $this->offset);
        }
        // at this point the array has offset less elements. We delete from limit to the end
        if ($this->limit >= 0) {
            $table->deleteRowsOffset($this->limit);
        }

        if ($this->keepSummaryRow && !empty($summaryRow)) {
            $table->addSummaryRow($summaryRow);
        }
    }
}
