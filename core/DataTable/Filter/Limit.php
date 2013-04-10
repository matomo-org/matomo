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
 * Delete all rows from the table that are not in the offset,offset+limit range
 *
 * @package Piwik
 * @subpackage Piwik_DataTable
 */
class Piwik_DataTable_Filter_Limit extends Piwik_DataTable_Filter
{
    /**
     * Filter constructor.
     *
     * @param Piwik_DataTable $table
     * @param int $offset          Starting row (indexed from 0)
     * @param int $limit           Number of rows to keep (specify -1 to keep all rows)
     * @param bool $keepSummaryRow  Whether to keep the summary row or not.
     */
    public function __construct($table, $offset, $limit = null, $keepSummaryRow = false)
    {
        parent::__construct($table);
        $this->offset = $offset;

        if (is_null($limit)) {
            $limit = -1;
        }
        $this->limit = $limit;
        $this->keepSummaryRow = $keepSummaryRow;
    }

    /**
     * Limits the given data table
     *
     * @param Piwik_DataTable $table
     */
    public function filter($table)
    {
        $table->setRowsCountBeforeLimitFilter();

        if ($this->keepSummaryRow) {
            $summaryRow = $table->getRowFromId(Piwik_DataTable::ID_SUMMARY_ROW);
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
