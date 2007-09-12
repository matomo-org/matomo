<?php

/**
 * Simple HTML output
 * 
 * @package Piwik_DataTable
 * @subpackage Piwik_DataTable_Renderer
 */
class Piwik_DataTable_Renderer_HTML extends Piwik_DataTable_Renderer
{
	protected $prefixRows;
	function __construct($table = null)
	{
		parent::__construct($table);
	}
	
	function render()
	{
		return $this->renderTable($this->table);
	}
	
	protected function renderTable($table)
	{
		if($table->getRowsCount() == 0)
		{
			return "<b><i>Empty table</i></b> <br>\n";
		}
		
		static $depth=0;
		$i = 1;
		
		$tableStructure = array();
		
		/*
		 * table = array
		 * ROW1 = col1 | col2 | col3 | details | idSubTable
		 * ROW2 = col1 | col2 (no value but appears) | col3 | details | idSubTable
		 * 		subtable here
		 */
		$allColumns = array();
		foreach($table->getRows() as $row)
		{
			//TODO put that in a Simple_PHP filter that will make it easy as well to export in CSV
			foreach($row->getColumns() as $column => $value)
			{
				$allColumns[$column] = true;
				$tableStructure[$i][$column] = $value;
			}

			$details=array();
			foreach($row->getDetails() as $detail => $value)
			{
				if(is_string($value)) $value = "'$value'";
				$details[] = "'$detail' => $value";
			}
			$details = implode("<br>", $details);
			
			$tableStructure[$i]['_details'] = $details;
			$tableStructure[$i]['_idSubtable'] = $row->getIdSubDataTable();
			
			if($row->getIdSubDataTable() !== null)
			{
				$depth++;
				try{
					$tableStructure[$i]['_subtable'] =  $this->renderTable( Piwik_DataTable_Manager::getInstance()->getTable($row->getIdSubDataTable()));
				} catch(Exception $e) {
					$tableStructure[$i]['_subtable'] = "-- Sub DataTable not loaded";
				}
				$tableStructure[$i]['_subtable']['depth'] = $depth;
				$depth--;
			}
			$i++;
		}
		
		/*
		// to keep the same columns order as the columns labelled with strings
		ksort($allColumns);
		//replace the label first
		if(isset($allColumns['label']))
		{
			$allColumns = array_merge(array('label'=>true),$allColumns);
		}
		*/
		$allColumns['_details'] = true;
		$allColumns['_idSubtable'] = true;

		$html = " <br><table border=1 width=70%>";
		$html .= "<tr>";
		foreach($allColumns as $name => $true)
		{
			$html .= "<td>$name</td>";
		}
		$colspan = count($allColumns);
		
		foreach($tableStructure as $row)
		{
			$html .= "<tr>";
			foreach($allColumns as $name => $true)
			{
				$value = "-";
				if(isset($row[$name]))
				{
					$value = $row[$name];
				}
				
				$html .= "<td>$value</td>";
			}
			$html .= "</tr>";
			
			$styles='<style>';
			for($i=0;$i<11;$i++)
			{
				$padding=$i*2;
				$styles.= "TD.l$i { padding-left:{$padding}em; } \n";
			}
			$styles.="</style>";
			
			if(isset($row['_subtable']))
			{
				$html .= "<tr><td class=l{$row['_subtable']['depth']} colspan=$colspan>{$row['_subtable']}</td></tr>";
			}
		}
		$html .= "</table><br>";
		
		if($depth == 0)
		{
			$html = $styles . $html;
		}
		return $html;
	}	
}



