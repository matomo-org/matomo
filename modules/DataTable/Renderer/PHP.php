<?php


class Piwik_DataTable_Renderer_PHP extends Piwik_DataTable_Renderer
{
	protected $serialize;
	function __construct($table = null, $serialize = true)
	{
		parent::__construct($table);
		$this->serialize = $serialize;
	}
	
	function render()
	{
		return $this->renderTable($this->table);
	}
	
	function renderTable($table)
	{
		$array = array();

		foreach($table->getRows() as $row)
		{
			$newRow = array(
				'columns' => $row->getColumns(),
				'details' => $row->getDetails(),
				'idsubdatatable' => $row->getIdSubDataTable()
				);
			$array[] = $newRow;
		}
		
		if($this->serialize)
		{
			serialize($array);
		}	
		else
		{
			return $array;
		}
	}
}