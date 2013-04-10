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
 * Add a new row to the table containing a summary
 * of the rows from StartRowToSummarize to EndRowToSummarize.
 * It then deletes the rows from StartRowToSummarize to EndRowToSummarize.
 * The new row created has a label = 'other'
 *
 * This filter is useful to build a more compact view of a table,
 * keeping the first records unchanged.
 *
 * For example we use this for the pie chart, to build the last pie part
 * which is the sum of all the remaining data after the top 5 data.
 * This row is assigned a label of 'Others'.
 *
 * @package Piwik
 * @subpackage Piwik_DataTable
 */
class Piwik_DataTable_Filter_AddSummaryRow extends Piwik_DataTable_Filter
{
    /**
     * Creates a new filter and set all required parameters
     *
     * @param Piwik_DataTable $table
     * @param int $startRowToSummarize
     * @param int $labelSummaryRow
     * @param null $columnToSortByBeforeTruncating
     * @param bool $deleteRows
     */
    public function __construct($table,
                                $startRowToSummarize,
                                $labelSummaryRow = Piwik_DataTable::LABEL_SUMMARY_ROW,
                                $columnToSortByBeforeTruncating = null,
                                $deleteRows = true)
    {
        parent::__construct($table);
        $this->startRowToSummarize = $startRowToSummarize;
        $this->labelSummaryRow = $labelSummaryRow;
        $this->columnToSortByBeforeTruncating = $columnToSortByBeforeTruncating;
        $this->deleteRows = $deleteRows;
    }

    /**
     * Adds a summary row to the given data table
     *
     * @param Piwik_DataTable $table
     */
    public function filter($table)
    {
        if ($table->getRowsCount() <= $this->startRowToSummarize + 1) {
            return;
        }
        $table->filter('Sort',
            array($this->columnToSortByBeforeTruncating, 'desc'));

        $rows = $table->getRows();
        $count = $table->getRowsCount();
        $newRow = new Piwik_DataTable_Row();
        for ($i = $this->startRowToSummarize; $i < $count; $i++) {
            if (!isset($rows[$i])) {
                // case when the last row is a summary row, it is not indexed by $cout but by Piwik_DataTable::ID_SUMMARY_ROW
                $summaryRow = $table->getRowFromId(Piwik_DataTable::ID_SUMMARY_ROW);

                //FIXME: I'm not sure why it could return false, but it was reported in: http://forum.piwik.org/read.php?2,89324,page=1#msg-89442
                if ($summaryRow) {
                    $newRow->sumRow($summaryRow, $enableCopyMetadata = false, $table->getColumnAggregationOperations());
                }
            } else {
                $newRow->sumRow($rows[$i], $enableCopyMetadata = false, $table->getColumnAggregationOperations());
            }
        }

        $newRow->setColumns(array('label' => $this->labelSummaryRow) + $newRow->getColumns());
        if ($this->deleteRows) {
            $table->filter('Limit', array(0, $this->startRowToSummarize));
        }
        $table->addSummaryRow($newRow);
        unset($rows);
    }
}
