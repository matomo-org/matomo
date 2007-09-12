<?php

/**
 * Returns the equivalent PHP array of the DataTable.
 * You can specify in the constructor if you want the serialized version.
 * 
 * @package Piwik_DataTable
 * @subpackage Piwik_DataTable_Renderer
 */
class Piwik_DataTable_Renderer_PHP extends Piwik_DataTable_Renderer
{
	protected $serialize;
	function __construct($table = null, $serialize = true)
	{
		parent::__construct($table);
		$this->setSerialize($serialize);
	}
	
	function setSerialize( $bool )
	{
		$this->serialize = $bool;
	}
	
	function __toString()
	{
		$data = $this->render();
		if(!is_string($data))
		{
			$data = serialize($data);
		}
		return $data;
	}
	
	function render()
	{
		if($this->table instanceof Piwik_DataTable_Simple)
		{
			$array = $this->renderSimpleTable($this->table);
		}
		else
		{
			$array = $this->renderTable($this->table);
		}
				
		if($this->serialize)
		{
			$array = serialize($array);
		}
		
		return $array;
	}
	
	protected function renderTable($table)
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
		return $array;
	}
	
	protected function renderSimpleTable($table)
	{
		$array = array();

		foreach($table->getRows() as $row)
		{
			$array[$row->getColumn('label')] = $row->getColumn('value');
		}
		return $array;
	}
}