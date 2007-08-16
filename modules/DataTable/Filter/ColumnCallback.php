<?php

/**
 * Delete all rows of when a given function returns false for a given column 
 */
class Piwik_DataTable_Filter_ColumnCallback extends Piwik_DataTable_Filter
{
	private $columnToFilter;
	private $function;
	
	public function __construct( $table, $columnToFilter, $function )
	{
		parent::__construct($table);
		$this->function = $function;
		$this->columnToFilter = $columnToFilter;
		$this->filter();
	}
	
	protected function filter()
	{
		foreach($this->table->getRows() as $key => $row)
		{
			if( !call_user_func( $this->function, $row->getColumn($this->columnToFilter)))
			{
				$this->table->deleteRow($key);
			}
		}
	}
}
?>
