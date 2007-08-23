<?php

/**
 * Replace a column value with a new value resulting from the function call
 */
class Piwik_DataTable_Filter_ColumnCallbackReplace extends Piwik_DataTable_Filter
{
	private $columnToFilter;
	private $functionToApply;
	
	public function __construct( $table, $columnToFilter, $functionToApply )
	{
		parent::__construct($table);
		$this->functionToApply = $functionToApply;
		$this->columnToFilter = $columnToFilter;
		$this->filter();
	}
	
	protected function filter()
	{
		foreach($this->table->getRows() as $key => $row)
		{
			$oldValue = $row->getColumn($this->columnToFilter);
			$newValue = call_user_func( $this->functionToApply, $oldValue);
			$row->setColumn($this->columnToFilter, $newValue);
		}
	}
}

