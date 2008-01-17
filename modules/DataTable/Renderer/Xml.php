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
//		echo $table;exit;
		$renderer = new Piwik_DataTable_Renderer_Php($table, $serialize = false);
		$array = $renderer->flatRender();
		
//		var_dump($array); exit;
		
		$options = array(
            XML_SERIALIZER_OPTION_INDENT       => '	',
            XML_SERIALIZER_OPTION_LINEBREAKS   => "\n",
			XML_SERIALIZER_OPTION_ROOT_NAME    => 'row',
            XML_SERIALIZER_OPTION_MODE         => XML_SERIALIZER_MODE_SIMPLEXML
        );
        $rootName = 'result';
        
		// case DataTable_Array
		if($table instanceof Piwik_DataTable_Array)
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
		  		foreach($array as $valueAttribute => $value)
		  		{
		  			$xml .= "\t<result $nameDescriptionAttribute=\"$valueAttribute\">".''."</result>\n";
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
		}
		
		    
		$serializer = new XML_Serializer($options);
		
		if($table instanceof Piwik_DataTable_Simple)
		{
			$serializer->setOption(XML_SERIALIZER_OPTION_ROOT_NAME, 'result');
		}
		
		$result = $serializer->serialize($array);

		$xmlStr = $serializer->getSerializedData();
		
		if($table instanceof Piwik_DataTable
			|| $table instanceof Piwik_DataTable_Array)
		{
			$xmlStr = "<$rootName>\n".$xmlStr."\n</$rootName>";
			$xmlStr = str_replace(">\n", ">\n\t",$xmlStr);
			$xmlStr = str_replace("\t</$rootName>", "</$rootName>",$xmlStr);
		}
		return $this->output($xmlStr);
	}
	
	protected function output( $xml )
	{
		// silent fail because otherwise it throws an exception in the unit tests
		@header('Content-type: text/xml');		
		return $xml;
	}
}