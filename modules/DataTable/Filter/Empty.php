<?php
/**
 * Empty filter template.
 * 
 * @package Piwik_DataTable
 * @subpackage Piwik_DataTable_Filter 
 */
class Piwik_DataTable_Filter_Empty extends Piwik_DataTable_Filter
{
	
	public function __construct( $table )
	{
		parent::__construct($table);
		$this->filter();
	}
	
	protected function filter()
	{
		foreach($this->table->getRows() as $key => $row)
		{
		}
	}
}

