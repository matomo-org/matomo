<?php

class Piwik_DataTable_Renderer
{
	protected $table;
	
	function __construct($table = null)
	{
		if(!is_null($table))
		{
			$this->setTable($table);
		}
	}
	
	public function setTable($table)
	{
		if(!($table instanceof Piwik_DataTable))
		{
			throw new Exception("The renderer accepts only a Piwik_DataTable object.");
		}
		$this->table = $table;
	}
	
	public function __toString()
	{
		return $this->render();
	}
	
	static public function factory( $name )
	{
		$name = strtolower($name);
		switch ($name) 
		{
			case 'console':
				require_once "DataTable/Renderer/Console.php";
				$class = 'Piwik_DataTable_Renderer_Console';
				break;
			
			case 'xml':
				require_once "DataTable/Renderer/XML.php";
				$class = 'Piwik_DataTable_Renderer_XML';
				break;
			
			case 'rss':
				require_once "DataTable/Renderer/RSS.php";
				$class = 'Piwik_DataTable_Renderer_RSS';
				break;
			
			case 'php':
				require_once "DataTable/Renderer/PHP.php";
				$class = 'Piwik_DataTable_Renderer_PHP';
				break;
		
			default:
				throw new Exception("Renderer format $name unknown!");
				break;
		}
		
		return new $class;
	}
	
	
}
?>
