<?php
/**
 * Delete all rows from the table that are not in the offset,offset+limit range
 * 
 * @package Piwik_DataTable
 * @subpackage Piwik_DataTable_Filter 
 */

class Piwik_DataTable_Filter_Limit extends Piwik_DataTable_Filter
{	
	public function __construct( $table, $offset, $limit )
	{
		parent::__construct($table);
		$this->offset = $offset;
		$this->limit = abs($limit);
		$this->filter();
	}
	
	protected function filter()
	{
		$table = $this->table;
		
		$rowsCount = $table->getRowsCount();
		
		// we have to delete
		// - from 0 to offset
		
		// at this point the array has offset less elements
		// - from limit to the end
		$table->deleteRowsOffset( 0, $this->offset );
		$table->deleteRowsOffset( $this->limit );
	}
}


