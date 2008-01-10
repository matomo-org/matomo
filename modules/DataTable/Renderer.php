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
	 * 
	 * @exception If the renderer is unknown
	 */
	static public function factory( $name )
	{
		$name = ucfirst(strtolower($name));
		$path = "DataTable/Renderer/".$name.".php";
		$className = 'Piwik_DataTable_Renderer_' . $name;
		if( Piwik::isValidFilename($name)
			&& is_file($path))
		{
			require_once $path;
			return new $className;			
		}
		else
		{
			throw new Exception("Renderer format $name not valid!");
		}		
	}	
}

