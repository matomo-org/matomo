<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 */

/**
 * Simple HTML output
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
		if($table->getRowsCount() == 0)
		{
			return "<b><i>Empty table</i></b> <br>\n";
		}
		
		static $depth=0;
		$i = 1;
		$someDetails = false;
		$someIdSubTable = false;
		
		$tableStructure = array();
		
		/*
		 * table = array
		 * ROW1 = col1 | col2 | col3 | details | idSubTable
		 * ROW2 = col1 | col2 (no value but appears) | col3 | details | idSubTable
		 * 		subtable here
		 */
		$allColumns = array();
//		echo $table;
		foreach($table->getRows() as $row)
		{
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
			
			if(count($details) != 0)
			{
				$someDetails = true;
				$details = implode("<br>", $details);
				$tableStructure[$i]['_details'] = $details;
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
		
		/*
		// to keep the same columns order as the columns labelled with strings
		ksort($allColumns);
		//replace the label first
		if(isset($allColumns['label']))
		{
			$allColumns = array_merge(array('label'=>true),$allColumns);
		}
		*/
		$allColumns['_details'] = $someDetails;
		$allColumns['_idSubtable'] = $someIdSubTable;
		$html = "\n";
		$html .= "<table border=1 width=70%>";
		$html .= "\n<tr>";
		foreach($allColumns as $name => $toDisplay)
		{
			if($toDisplay !== false)
			{
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
//				echo ".".$row['_subtable'];exit;
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
//		echo "return={".$html."}";
		return $html;
	}	
}



