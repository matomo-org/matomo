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

/**
 * A DataTable Renderer can produce an output given a DataTable object.
 * All new Renderers must be copied in DataTable/Renderer and added to the factory() method.
 * 
 * @package Piwik_DataTable
 * @subpackage Piwik_DataTable_Renderer
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
		if(!($table instanceof Piwik_DataTable)
			&& !($table instanceof Piwik_DataTable_Array))
		{
			throw new Exception("The renderer accepts only a Piwik_DataTable or an array of DataTable (Piwik_DataTable_Array) object.");
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
		$path = PIWIK_INCLUDE_PATH . "/modules/DataTable/Renderer/".$name.".php";
		$className = 'Piwik_DataTable_Renderer_' . $name;
		
		if( Piwik_Common::isValidFilename($name)
			&& is_file($path)
		)
		{
			require_once $path;
			return new $className;			
		}
		else
		{
			throw new Exception("Renderer format '$name' not valid.");
		}		
	}	
}

