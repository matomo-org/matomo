<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: Limit.php 168 2008-01-14 05:26:43Z matt $
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
	public $labelSummaryRow = 'Others';
	
	public function __construct( $table, $startRowToSummarize )
	{
		parent::__construct($table);
		$this->startRowToSummarize = $startRowToSummarize;
		
		if($table->getRowsCount() > $startRowToSummarize + 1)
		{
			$this->filter();
		}
	}
	
	protected function filter()
	{
		$copied = clone $this->table;
		$filter = new Piwik_DataTable_Filter_Limit($copied, $this->startRowToSummarize);
		$newRow = new Piwik_DataTable_Row_DataTableSummary($copied);
		$newRow->addColumn('label',$this->labelSummaryRow);
		$filter = new Piwik_DataTable_Filter_Limit($this->table, 0, $this->startRowToSummarize);
		$this->table->addRow($newRow);
	}
}


