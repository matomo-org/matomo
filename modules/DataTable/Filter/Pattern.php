<?php

class Piwik_DataTable_Filter_Pattern extends Piwik_DataTable_Filter
{
	private $columnToFilter;
	private $patternToSearch;
	
	public function __construct( $table, $columnToFilter, $patternToSearch )
	{
		parent::__construct($table);
		$this->patternToSearch = $patternToSearch;
		$this->columnToFilter = $columnToFilter;
		$this->filter();
	}
	
	protected function filter()
	{
		foreach($this->table->getRows() as $key => $row)
		{
			if( !ereg($this->patternToSearch, $row->getColumn($this->columnToFilter)))
			{
				$this->table->deleteRow($key);
			}
		}
	}
}
?>
