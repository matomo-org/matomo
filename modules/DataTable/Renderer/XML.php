<?php

require_once "DataTable/Renderer/PHP.php";
class Piwik_DataTable_Renderer_XML extends Piwik_DataTable_Renderer
{
	function __construct($table = null)
	{
		parent::__construct($table);
	}
	
	function render()
	{
		return $this->renderTable($this->table);
	}
	
	function renderTable($table)
	{
		$renderer = new Piwik_DataTable_Renderer_PHP($table, $serialize = false);
		$array = $renderer->render();
		$xmlStr = $this->array_to_simplexml($array)->asXML();
		$xmlStr = str_replace("<","\n<",$xmlStr);
		
		return $xmlStr;
	}	
	
	// from http://uk3.php.net/simplexml
	function array_to_simplexml($array, $name="result" ,&$xml=null )
	{
	    if(is_null($xml))
	    {
	        $xml = new SimpleXMLElement("<{$name}/>");
	    }
	   
	    foreach($array as $key => $value)
	    {
	        if(is_array($value))
	        {
	            $xml->addChild($key);
	            $this->array_to_simplexml($value, $name, $xml->$key);
	        }
	        else
	        {
	            $xml->addChild($key, $value);
	        }
	    }
	    return $xml;
	}
}