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

use Piwik\DataTable\Filter;
use Piwik\DataTable;
use Piwik\DataTable\Manager;

/**
 * @package Piwik
 * @subpackage DataTable
 */
class Truncate extends Filter
{
    /**
     * @param DataTable $table
     * @param int $truncateAfter
     */
    public function __construct($table,
                                $truncateAfter,
                                $labelSummaryRow = DataTable::LABEL_SUMMARY_ROW,
                                $columnToSortByBeforeTruncating = null,
                                $deleteRows = true,
                                $filterRecursive = true)
    {
        parent::__construct($table);
        $this->truncateAfter = $truncateAfter;
        $this->labelSummaryRow = $labelSummaryRow;
        $this->columnToSortByBeforeTruncating = $columnToSortByBeforeTruncating;
        $this->deleteRows = $deleteRows;
        $this->filterRecursive = $filterRecursive;
    }

    /**
     * Truncates the table after X rows and adds a summary row
     *
     * @param DataTable $table
     */
    public function filter($table)
    {
        $this->addSummaryRow($table);
        $table->filter('ReplaceSummaryRowLabel');

        if ($this->filterRecursive) {
            foreach ($table->getRows() as $row) {
                if ($row->isSubtableLoaded()) {
                    $idSubTable = $row->getIdSubDataTable();
                    $subTable = Manager::getInstance()->getTable($idSubTable);
                    $subTable->filter('Truncate', array($this->truncateAfter));
                }
            }
        }
    }

    public function addSummaryRow($table)
    {
        $table->filter('Sort',
            array($this->columnToSortByBeforeTruncating, 'desc'));

        if ($table->getRowsCount() <= $this->startRowToSummarize + 1) {
            return;
        }

        $rows = $table->getRows();
        $count = $table->getRowsCount();
        $newRow = new Row();
        for ($i = $this->startRowToSummarize; $i < $count; $i++) {
            if (!isset($rows[$i])) {
                // case when the last row is a summary row, it is not indexed by $cout but by DataTable::ID_SUMMARY_ROW
                $summaryRow = $table->getRowFromId(DataTable::ID_SUMMARY_ROW);

                //FIXME: I'm not sure why it could return false, but it was reported in: http://forum.piwik.org/read.php?2,89324,page=1#msg-89442
                if ($summaryRow) {
                    $newRow->sumRow($summaryRow, $enableCopyMetadata = false, $table->getMetadata(DataTable::COLUMN_AGGREGATION_OPS_METADATA_NAME));
                }
            } else {
                $newRow->sumRow($rows[$i], $enableCopyMetadata = false, $table->getMetadata(DataTable::COLUMN_AGGREGATION_OPS_METADATA_NAME));
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