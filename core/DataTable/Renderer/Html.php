<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: Html.php 519 2008-06-09 01:59:24Z matt $
 * 
 * @package Piwik_DataTable
 */

/**
 * Simple HTML output
 * Works with recursive DataTable (when a row can be associated with a subDataTable).
 * 
 * @package Piwik_DataTable
 * @subpackage Piwik_DataTable_Renderer
 */
class Piwik_DataTable_Renderer_Html extends Piwik_DataTable_Renderer
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
		if($table instanceof Piwik_DataTable_Array)
		{
			$columnPrefixToAdd = $table->getKeyName();
			$out = "<table border=1>";
			foreach($table->getArray() as $date => $subtable )
			{
				$out .= "<tr><td><h2>$columnPrefixToAdd = $date</h2>";
				$out .= $this->renderDataTable($subtable);
				$out .= "</td></tr>";
			}
			$out .= "</table>";
		}
		else
		{
			$out = $this->renderDataTable($table);
		}
		return $out;
	}	
	
	protected function renderDataTable($table)
	{
		if($table->getRowsCount() == 0)
		{
			return "<b><i>Empty table</i></b> <br>\n";
		}
		if($table instanceof Piwik_DataTable_Simple 
			&& $table->getRowsCount() ==1)
		{
			$table->deleteColumn('label');
		}
		
		static $depth=0;
		$i = 1;
		$someMetadata = false;
		$someIdSubTable = false;
		
		$tableStructure = array();
		
		/*
		 * table = array
		 * ROW1 = col1 | col2 | col3 | metadata | idSubTable
		 * ROW2 = col1 | col2 (no value but appears) | col3 | metadata | idSubTable
		 * 		subtable here
		 */
		$allColumns = array();
		foreach($table->getRows() as $row)
		{
			foreach($row->getColumns() as $column => $value)
			{
				$allColumns[$column] = true;
				$tableStructure[$i][$column] = $value;
			}

			$metadata=array();
			foreach($row->getMetadata() as $name => $value)
			{
				if(is_string($value)) $value = "'$value'";
				$metadata[] = "'$name' => $value";
			}
			
			if(count($metadata) != 0)
			{
				$someMetadata = true;
				$metadata = implode("<br>", $metadata);
				$tableStructure[$i]['_metadata'] = $metadata;
			}
			
			$idSubtable = $row->getIdSubDataTable();
			if(!is_null($idSubtable))
			{
				$someIdSubTable = true;
				$tableStructure[$i]['_idSubtable'] = $idSubtable;
			}
			
			if($row->getIdSubDataTable() !== null)
			{
				$depth++;
				try{
					$tableStructure[$i]['_subtable']['html'] =  $this->renderTable( Piwik_DataTable_Manager::getInstance()->getTable($row->getIdSubDataTable()));
				} catch(Exception $e) {
					$tableStructure[$i]['_subtable']['html'] = "-- Sub DataTable not loaded";
				}
				$tableStructure[$i]['_subtable']['depth'] = $depth;
				$depth--;
			}
			$i++;
		}
		
		$allColumns['_metadata'] = $someMetadata;
		$allColumns['_idSubtable'] = $someIdSubTable;
		$html = "\n";
		$html .= "<table border=1 width=70%>";
		$html .= "\n<tr>";
		foreach($allColumns as $name => $toDisplay)
		{
			if($toDisplay !== false)
			{
				if($name === 0)
				{
					$name = 'value';
				}
				$html .= "\n\t<td><b>$name</b></td>";
			}
		}
		$colspan = count($allColumns);
		
		foreach($tableStructure as $row)
		{
			$html .= "\n\n<tr>";
			foreach($allColumns as $name => $toDisplay)
			{
				if($toDisplay !== false)
				{
					$value = "-";
					if(isset($row[$name]))
					{
						$value = $row[$name];
					}
					
					$html .= "\n\t<td>$value</td>";
				}
			}
			$html .= "</tr>";
			
			if(isset($row['_subtable']))
			{
				$html .= "<tr>
						<td class=l{$row['_subtable']['depth']} colspan=$colspan>{$row['_subtable']['html']}</td></tr>";
			}
		}
		$html .= "\n\n</table>";
		
		// display styles if there is a subtable displayed
		if($someIdSubTable)
		{
			$styles="\n\n<style>\n";
			for($i=0;$i<11;$i++)
			{
				$padding=$i*2;
				$styles.= "\t TD.l$i { padding-left:{$padding}em; } \n";
			}
			$styles.="</style>\n\n";
			if($depth == 0)
			{
				$html = $styles . $html;
			}
		}
		return $html;
	}
}



