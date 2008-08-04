<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: Csv.php 558 2008-07-20 23:10:38Z matt $
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
 * Note that CSV output doesn't handle recursive dataTable. It will output only the first parent level of the tables.
 * 
 * @package Piwik_DataTable
 * @subpackage Piwik_DataTable_Renderer
 * 
 */

class Piwik_DataTable_Renderer_Csv extends Piwik_DataTable_Renderer
{
	/**
	 * Column separator
	 *
	 * @var string
	 */
	public $separator = ',';
	
	/**
	 * Line end 
	 *
	 * @var string
	 */
	public $lineEnd = "\n";
	
	/**
	 * 'metadata' columns will be exported, prefixed by 'metadata_'
	 *
	 * @var bool
	 */
	public $exportMetadata = true;
	
	/**
	 * Converts the content to unicode so that UTF8 characters (eg. chinese) can be imported in Excel
	 *
	 * @var bool
	 */
	public $convertToUnicode = true;
	
	/**
	 * idSubtable will be exported in a column called 'idsubdatatable'
	 *
	 * @var bool
	 */
	public $exportIdSubtable = true;
	
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
			$str = $header = '';
			$prefixColumns = $table->getKeyName() . $this->separator;
			foreach($table->getArray() as $currentLinePrefix => $dataTable)
			{
				$returned = explode("\n",$this->renderTable($dataTable));
				// get the columns names
				if(empty($header))
				{
					$header = $returned[0];
				}
				$returned = array_slice($returned,1);
				
				// case empty datatable we dont print anything in the CSV export
				// when in xml we would output <result date="2008-01-15" />
				if(!empty($returned))
				{
					foreach($returned as &$row)
					{
						$row = $currentLinePrefix . $this->separator . $row;
					}
					$str .= "\n" .  implode("\n", $returned);
				}
			}
			if(!empty($header))
			{
				$str = $prefixColumns . $header . $str;
			}
		}
		else
		{
			$str = $this->renderDataTable($table);
		}
		
		return $this->output($str);
	}
	
	protected function renderDataTable( $table )
	{	
		if($table instanceof Piwik_DataTable_Simple 
			&& $table->getRowsCount() == 1)
		{
			$str = 'value' . $this->lineEnd . $table->getRowFromId(0)->getColumn('value');
			return $str;
		}
		
		$csv = array();		

		$allColumns = array();
		foreach($table->getRows() as $row)
		{
			$csvRow = array();
			
			$columns = $row->getColumns();
			foreach($columns as $name => $value)
			{
				if(!isset($allColumns[$name]))
				{
					$allColumns[$name] = true;
				}
				$csvRow[$name] = $value;
			}
			
			if($this->exportMetadata)
			{
				$metadata = $row->getMetadata();
				foreach($metadata as $name => $value)
				{
					//if a metadata and a column have the same name make sure they dont overwrite
					$name = 'metadata_'.$name;
					
					$allColumns[$name] = true;
					$csvRow[$name] = $value;
				}
			}		
			
			if($this->exportIdSubtable)
			{
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
		$str = '';		
		
		// specific case, we have only one column and this column wasn't named properly (indexed by a number)
		// we don't print anything in the CSV file => an empty line
		if(sizeof($allColumns) == 1 
			&& reset($allColumns) 
			&& !is_string(key($allColumns)))
		{
			$str .= '';
		}
		else
		{
			$keys = array_keys($allColumns);
			$str .= implode($this->separator, $keys);
			$str .= $this->lineEnd;
		}
		
		// we render the CSV
		foreach($csv as $theRow)
		{
			$rowStr = '';
			foreach($allColumns as $columnName => $true)
			{
				$rowStr .= $this->formatValue($theRow[$columnName]) . $this->separator;
			}
			// remove the last separator
			$rowStr = substr_replace($rowStr,"",-strlen($this->separator));
			$str .= $rowStr . $this->lineEnd;
		}
		$str = substr($str, 0, -strlen($this->lineEnd));
		return $str;
	}

	protected function formatValue($value)
	{
		if(is_string($value)
			&& !is_numeric($value)) 
		{
			$value = html_entity_decode($value, ENT_COMPAT, 'UTF-8');
		}
		return $value;
	}
	
	protected function output( $str )
	{
		if(empty($str))
		{
			return 'No data available';
		}
		// silent fail otherwise unit tests fail
		@header("Content-type: application/vnd.ms-excel");
		@header("Content-Disposition: attachment; filename=piwik-report-export.csv");
		if($this->convertToUnicode 
			&& function_exists('mb_convert_encoding'))
		{
			$str = chr(255) . chr(254) . mb_convert_encoding($str, 'UTF-16LE', 'UTF-8');
		}
		return $str;
	}
}