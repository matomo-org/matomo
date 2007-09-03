<?php
/**
 * Simple output
 * 
 * @package Piwik_DataTable
 * @subpackage Piwik_DataTable_Renderer
 */
class Piwik_DataTable_Renderer_Console extends Piwik_DataTable_Renderer
{
	protected $prefixRows;
	function __construct($table = null)
	{
		parent::__construct($table);
		$this->setPrefixRow('#');
	}
	
	function render()
	{
		return $this->renderTable($this->table);
	}
	
	function setPrefixRow($str)
	{
		$this->prefixRows = $str;
	}
	function renderTable($table)
	{
		if($table->getRowsCount() == 0)
		{
			return "Empty table <br>\n";
		}
		
		static $depth=0;
		$output = '';
		$i = 1;
		foreach($table->getRows() as $row)
		{
			$columns=array();
			foreach($row->getColumns() as $column => $value)
			{
				if(is_string($value)) $value = "'$value'";
				$columns[] = "'$column' => $value";
			}
			$columns = implode(", ", $columns);
			$details=array();
			foreach($row->getDetails() as $detail => $value)
			{
				if(is_string($value)) $value = "'$value'";
				$details[] = "'$detail' => $value";
			}
			$details = implode(", ", $details);
			$output.= str_repeat($this->prefixRows, $depth) 
						. "- $i [".$columns."] [".$details."] [idsubtable = " 
						. $row->getIdSubDataTable()."]<br>\n";
			
			if($row->getIdSubDataTable() !== null)
			{
				$depth++;
				try{
					$output.= $this->renderTable( 
									Piwik_DataTable_Manager::getInstance()->getTable(
												$row->getIdSubDataTable()
											)
										);
				} catch(Exception $e) {
					$output.= "-- Sub DataTable not loaded<br>\n";
				}
				$depth--;
			}
			$i++;
		}
		
		return $output;
		
	}	
}


