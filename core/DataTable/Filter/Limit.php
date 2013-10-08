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
namespace Piwik\DataTable\Filter;

use Piwik\DataTable;
use Piwik\DataTable\Filter;

/**
 * Delete all rows from the table that are not in the offset,offset+limit range
 *
 * @package Piwik
 * @subpackage DataTable
 */
class Limit extends Filter
{
    /**
     * Filter constructor.
     *
     * @param DataTable $table
     * @param int $offset Starting row (indexed from 0)
     * @param int $limit Number of rows to keep (specify -1 to keep all rows)
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
     * Limits the given data table
     *
     * @param DataTable $table
     */
    public function filter($table)
    {
        $table->setRowsCountBeforeLimitFilter();

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
