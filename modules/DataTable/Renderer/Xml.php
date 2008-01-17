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
require_once "XML/Serializer.php";
/**
 * XML export. Using the excellent Pear::XML_Serializer.
 * We had to fix the PEAR library so that it works under PHP5 STRICT mode.
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
		
		if($table instanceof Piwik_DataTable_Simple)
		{
			if(is_array($array))
			{
				$out = $this->renderDataTableSimple($array);
				$out = "<result>\n".$out."</result>";
				return $this->output($out);
			}
			else
			{
				$out = "<result>".$array."</result>";
				return $this->output($out);
			}
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
	  			$xml .= "\t<result $nameDescriptionAttribute=\"$valueAttribute\">$value</result>\n";
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
	  			$dataTableSimple = $this->renderDataTableSimple($dataTableSimple, "\t");
	  			$xml .= "\t<result $nameDescriptionAttribute=\"$valueAttribute\">\n".$dataTableSimple."\t</result>\n";
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
		//          'nb_unique_visitors' => int 11
		//          'nb_visits' => int 13
		//      1 => 
		//        array
		//          'label' => string 'phpmyvisits'
		//          'nb_unique_visitors' => int 2
		//          'nb_visits' => int 2
		//  'day2' => 
		//    array
		//      0 => 
		//        array
		//          'label' => string 'piwik'
		//          'nb_unique_visitors' => int 121
		//          'nb_visits' => int 130
		//      1 => 
		//        array
		//          'label' => string 'piwik bis'
		//          'nb_unique_visitors' => int 20
		//          'nb_visits' => int 120
		if($firstTable instanceof Piwik_DataTable)
		{
			$out = "<results>\n";
			$nameDescriptionAttribute = $table->getNameKey();
			foreach($array as $keyName => $arrayForSingleDate)
			{
				$out .= "\t<result $nameDescriptionAttribute=\"$keyName\">\n";
				
				$out .= $this->renderDataTable( $arrayForSingleDate, "\t" );
				$out .= "\t</result>\n";
			}
			$out .= "</results>";
			return $this->output($out);
		}
	}
	
	protected function renderDataTable( $array, $prefixLine = "" )
	{
//		var_dump($array);exit;
		
		$out = '';
		foreach($array as $row)
		{
			$out .= $prefixLine."\t<row>\n";
			foreach($row as $name => $value)
			{
				$out .= $prefixLine."\t\t<$name>$value</$name>\n";
			} 
			$out .= $prefixLine."\t</row>\n";
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
		@header('Content-type: text/xml');		
		return $xml;
	}
}