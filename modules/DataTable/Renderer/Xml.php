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
		    
		$serializer = new XML_Serializer($options);
		
		if($table instanceof Piwik_DataTable_Simple)
		{
			$serializer->setOption(XML_SERIALIZER_OPTION_ROOT_NAME, 'result');
		}
		
		$result = $serializer->serialize($array);

		$xmlStr = $serializer->getSerializedData();
		
		if(get_class($table) == 'Piwik_DataTable')
		{
			$xmlStr = "<result>\n".$xmlStr."\n</result>";
			$xmlStr = str_replace(">\n", ">\n\t",$xmlStr);
			$xmlStr = str_replace("\t</result>", "</result>",$xmlStr);
		}
		
		// silent fail because otherwise it throws an exception in the unit tests
		@header('Content-type: text/xml');		
		return $xmlStr;
	}
}