<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 *
 * @package Piwik_DataTable
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
 * @package Piwik_DataTable
 * @subpackage Piwik_DataTable_Filter
 */
class Piwik_DataTable_Filter_AddSummaryRow extends Piwik_DataTable_Filter
{
	public function __construct(	$table, 
									$startRowToSummarize, 
									$labelSummaryRow = Piwik_DataTable::LABEL_SUMMARY_ROW, 
									$columnToSortByBeforeTruncating = null )
	{
		parent::__construct($table);
		$this->startRowToSummarize = $startRowToSummarize;
		$this->labelSummaryRow = $labelSummaryRow;
		$this->columnToSortByBeforeTruncating = $columnToSortByBeforeTruncating;

		if($table->getRowsCount() > $startRowToSummarize + 1)
		{
			$this->filter();
		}
	}

	protected function filter()
	{
		$this->table->filter('Sort', 
							array( $this->columnToSortByBeforeTruncating, 'desc'));
		
		$rows = $this->table->getRows();
		$count = $this->table->getRowsCount();
		$newRow = new Piwik_DataTable_Row();
		for($i = $this->startRowToSummarize; $i < $count; $i++)
		{
			if(!isset($rows[$i]))
			{
				// case when the last row is a summary row, it is not indexed by $cout but by Piwik_DataTable::ID_SUMMARY_ROW
				$summaryRow = $this->table->getRowFromId(Piwik_DataTable::ID_SUMMARY_ROW);
				$newRow->sumRow($summaryRow);
			}
			else
			{
				$newRow->sumRow($rows[$i]);
			}
		}
		
		$newRow->setColumns(array('label' => $this->labelSummaryRow) + $newRow->getColumns());
		$this->table->filter('Limit', array(0, $this->startRowToSummarize));
		$this->table->addSummaryRow($newRow);
	}
}
