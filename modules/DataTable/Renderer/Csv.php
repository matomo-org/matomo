<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @package Piwik_DataTable
 */

require_once "DataTable/Renderer/Php.php";
/**
 * CSV export
 * 
 * When rendered using the default settings, a CSV report has the following characteristics:
 * The first record contains headers for all the columns in the report.
 * All rows have the same number of columns.
 * The default field delimiter string is a comma (,).
 * Formatting and layout are ignored.
 * 
 * @package Piwik_DataTable
 * @subpackage Piwik_DataTable_Renderer
 * 
 */

class Piwik_DataTable_Renderer_Csv extends Piwik_DataTable_Renderer
{
	public $separator = ',';
	public $exportDetail = true;
	public $exportIdSubtable = true;
	public $lineEnd = "\n";
	
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
		$csv = array();		

		// keep track of all the existing columns in the csv file
		$allColumns = array();
		
		if($table instanceof Piwik_DataTable_Simple 
			&& $table->getRowsCount() ==1)
		{
			$str = 'value' . $this->lineEnd . $table->getRowFromId(0)->getColumn('value');
			return $this->output($str);
		}
		
		foreach($table->getRows() as $row)
		{
			$csvRow = array();
			
			// COLUMNS
			$columns = $row->getColumns();
			foreach($columns as $name => $value)
			{
				if(!isset($allColumns[$name]))
				{
					$allColumns[$name] = true;
				}
				$csvRow[$name] = $value;
			}
			
			if($this->exportDetail)
			{
				// DETAILS
				$details = $row->getDetails();
				foreach($details as $name => $value)
				{
					//if a detail and a column have the same name make sure they dont overwrite
					$name = 'detail_'.$name;
					
					$allColumns[$name] = true;
					$csvRow[$name] = $value;
				}
			}		
			
			if($this->exportIdSubtable)
			{
				// ID SUB DATATABLE
				$idsubdatatable = $row->getIdSubDataTable();
				if($idsubdatatable !== false)
				{
					$csvRow['idsubdatatable'] = $idsubdatatable;
				}
			}
			
			$csv[] = $csvRow;
		}
		
		// now we make sure that all the rows in the CSV array have all the columns
		foreach($csv as &$row)
		{
			foreach($allColumns as $columnName => $true)
			{
				if(!isset($row[$columnName]))
				{
					$row[$columnName] = '';
				}
			}
		}
//		var_dump($csv);exit;
		$str = '';
		
		
		// specific case, we have only one column and this column wasn't named properly (indexed by a number)
		// we don't print anything in the CSV file => an empty line
		if(sizeof($allColumns) == 1 
			&& reset($allColumns) 
			&& !is_string(key($allColumns))  )
		{
			$str .= '';
		}
		else
		{
			$keys = array_keys($allColumns);
//			foreach($keys as &$key)
//			{
//				$key = '"' . $key . '"';
//			}
			$str .= implode($this->separator, $keys);
		}
		
		// we render the CSV
		foreach($csv as $theRow)
		{
			$rowStr = $this->lineEnd;
			foreach($allColumns as $columnName => $true)
			{
				$rowStr .= $theRow[$columnName] . $this->separator;
			}
			// remove the last separator
			$rowStr = substr_replace($rowStr,"",-strlen($this->separator));
			
			$str .= $rowStr;
		}
		
		return $this->output($str);
	}
	
	protected function output( $str )
	{
		// silent fail otherwise unit tests fail
		@header("Content-type: application/vnd.ms-excel");
		@header("Content-Disposition: attachment; filename=piwik-report-export.csv");			
		return $str;
	}
}