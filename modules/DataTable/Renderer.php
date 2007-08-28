<?php
/**
 * A DataTable Renderer can produce an output given a DataTable object.
 * All new Renderers must be copied in DataTable/Renderer and added to the factory() method.
 * 
 * @package Piwik_DataTable
 */
abstract class Piwik_DataTable_Renderer
{
	protected $table;
	
	function __construct($table = null)
	{
		if(!is_null($table))
		{
			$this->setTable($table);
		}
	}
	
	/**
	 * Computes the output and returns the string/binary
	 */
	abstract public function render();
	
	/**
	 * @see render()
	 */
	public function __toString()
	{
		return $this->render();
	}
	
	/**
	 * Set the DataTable to be rendered
	 */
	public function setTable($table)
	{
		if(!($table instanceof Piwik_DataTable))
		{
			throw new Exception("The renderer accepts only a Piwik_DataTable object.");
		}
		$this->table = $table;
	}
	
	/**
	 * Returns the DataTable associated to the output format $name
	 * @exception If the renderer is unknown
	 */
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
		
			case 'html':
				require_once "DataTable/Renderer/HTML.php";
				$class = 'Piwik_DataTable_Renderer_HTML';
				break;
		
			default:
				throw new Exception("Renderer format $name unknown!");
				break;
		}
		
		return new $class;
	}
	
	
}

