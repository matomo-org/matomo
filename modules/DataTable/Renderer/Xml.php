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
 * XML export of a given DataTable.
 * See the tests cases for more information about the XML format (/tests/modules/DataTable/Renderer.test.php)
 * Or have a look at the API calls examples.
 * 
 * Works with recursive DataTable (when a row can be associated with a subDataTable).
 * 
 * @package Piwik_DataTable
 * @subpackage Piwik_DataTable_Renderer
 */
class Piwik_DataTable_Renderer_Xml extends Piwik_DataTable_Renderer
{
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
		$renderer = new Piwik_DataTable_Renderer_Php($table, $serialize = false);
		
		$array = $renderer->flatRender();
		
		// case DataTable_Array
		if($table instanceof Piwik_DataTable_Array)
		{
			return $this->renderDataTableArray($table, $array);
		}
	
		// integer value of ZERO is a value we want to display
		if($array != 0 && empty($array))
		{
			$out = "<result />";
			return $this->output($out);
		}
		if($table instanceof Piwik_DataTable_Simple)
		{
			if(is_array($array))
			{
				$out = $this->renderDataTableSimple($array);
				$out = "<result>\n".$out."</result>";
			}
			else
			{
				$out = "<result>".$array."</result>";
			}
			return $this->output($out);
		}
		
		if($table instanceof Piwik_DataTable)
		{
			$out = $this->renderDataTable($array);
			$out = "<result>\n$out</result>";
			return $this->output($out);
		}
		
		
	}
	
	protected function renderDataTableArray($table, $array)
	{
		// CASE 1
		//array
  		//  'day1' => string '14' (length=2)
  		//  'day2' => string '6' (length=1)
		$firstTable = current($array);
		if(!is_array( $firstTable ))
		{
	  		$xml = "<results>\n";
	  		$nameDescriptionAttribute = $table->getNameKey();
	  		foreach($array as $valueAttribute => $value)
	  		{
	  			if(!empty($value))
	  			{
		  			$xml .= "\t<result $nameDescriptionAttribute=\"$valueAttribute\">$value</result>\n";
	  			}
	  			else
	  			{
		  			$xml .= "\t<result $nameDescriptionAttribute=\"$valueAttribute\" />\n";	  				
	  			}
	  		}
	  		$xml .= "</results>";
	  		return $this->output($xml);
		}
		
		$subTables = $table->getArray();
		$firstTable = current($subTables);
		
		// CASE 2
		//array
  		//  'day1' => 
  		//    array
  		//      'nb_uniq_visitors' => string '18'
  		//      'nb_visits' => string '101' 
  		//  'day2' => 
  		//    array
  		//      'nb_uniq_visitors' => string '28' 
  		//      'nb_visits' => string '11' 
		if( $firstTable instanceof Piwik_DataTable_Simple)
		{
	  		$xml = "<results>\n";
			$nameDescriptionAttribute = $table->getNameKey();
	  		foreach($array as $valueAttribute => $dataTableSimple)
	  		{
	  			
	  			if(count($dataTableSimple) == 0)
				{
					$xml .= "\t<result $nameDescriptionAttribute=\"$valueAttribute\" />\n";
				}
	  			else
	  			{
		  			if(is_array($dataTableSimple))
		  			{
			  			$dataTableSimple = "\n" . $this->renderDataTableSimple($dataTableSimple, "\t") . "\t" ;
		  			}
		  			$xml .= "\t<result $nameDescriptionAttribute=\"$valueAttribute\">".$dataTableSimple."</result>\n";
	  			}
	  		}
	  		$xml .= "</results>";
	  		return $this->output($xml);
		}
		
		// CASE 3
		//array
		//  'day1' => 
		//    array
		//      0 => 
		//        array
		//          'label' => string 'phpmyvisites'
		//          'nb_uniq_visitors' => int 11
		//          'nb_visits' => int 13
		//      1 => 
		//        array
		//          'label' => string 'phpmyvisits'
		//          'nb_uniq_visitors' => int 2
		//          'nb_visits' => int 2
		//  'day2' => 
		//    array
		//      0 => 
		//        array
		//          'label' => string 'piwik'
		//          'nb_uniq_visitors' => int 121
		//          'nb_visits' => int 130
		//      1 => 
		//        array
		//          'label' => string 'piwik bis'
		//          'nb_uniq_visitors' => int 20
		//          'nb_visits' => int 120
		if($firstTable instanceof Piwik_DataTable)
		{
			$out = "<results>\n";
			$nameDescriptionAttribute = $table->getNameKey();
			foreach($array as $keyName => $arrayForSingleDate)
			{
				$dataTableOut = $this->renderDataTable( $arrayForSingleDate, "\t" );
				
				if(empty($dataTableOut))
				{
					$out .= "\t<result $nameDescriptionAttribute=\"$keyName\" />\n";
				}
				else
				{
					$out .= "\t<result $nameDescriptionAttribute=\"$keyName\">\n";
					$out .= $dataTableOut;
					$out .= "\t</result>\n";
				}
			}
			$out .= "</results>";
			return $this->output($out);
		}
	}
	
	protected function renderDataTable( $array, $prefixLine = "" )
	{
		$out = '';
		foreach($array as $row)
		{
			$out .= $prefixLine."\t<row>";
			
			if(count($row) === 1
				&& key($row) === 0)
			{
				$value = current($row);
				$out .= $prefixLine . $value;				
			}
			else
			{
				$out .= "\n";
				foreach($row as $name => $value)
				{
					// handle the recursive dataTable case by XML outputting the recursive table
					if(is_array($value))
					{
						$value = "\n".$this->renderDataTable($value, $prefixLine."\t\t");
						$value .= $prefixLine."\t\t"; 
					}
					$out .= $prefixLine."\t\t<$name>$value</$name>\n";
				} 
				$out .= "\t";
			}
			$out .= $prefixLine."</row>\n";
		}
		return $out;
	}
	
	protected function renderDataTableSimple( $array, $prefixLine = "")
	{
		$out = '';
		foreach($array as $keyName => $value)
		{
			$out .= $prefixLine."\t<$keyName>$value</$keyName>\n"; 
		}
		return $out;
	}
	
	protected function output( $xml )
	{
		// silent fail because otherwise it throws an exception in the unit tests
		@header("Content-Type: text/xml;charset=utf-8");
		$xml = '<?xml version="1.0" encoding="utf-8" ?>' .  "\n" . $xml;
		return $xml;
	}
}