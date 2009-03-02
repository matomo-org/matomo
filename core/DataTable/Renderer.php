<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: Renderer.php 519 2008-06-09 01:59:24Z matt $
 * 
 * @package Piwik_DataTable
 */

/**
 * A DataTable Renderer can produce an output given a DataTable object.
 * All new Renderers must be copied in DataTable/Renderer and added to the factory() method.
 * To use a renderer, simply do:
 *  $render = new Piwik_DataTable_Renderer_Xml();
 *  $render->setTable($dataTable);
 *  echo $render;
 * 
 * @package Piwik_DataTable
 * @subpackage Piwik_DataTable_Renderer
 */
abstract class Piwik_DataTable_Renderer
{
	protected $table;
	protected $renderSubTables = false;
	
	public function setRenderSubTables($enableRenderSubTable)
	{
		$this->renderSubTables = (bool)$enableRenderSubTable;
	}
	
	/**
	 * Computes the dataTable output and returns the string/binary
	 * 
	 * @return string
	 */
	abstract public function render();
	
	/**
	 * @see render()
	 * @return string
	 */
	public function __toString()
	{
		return $this->render();
	}
	
	/**
	 * Set the DataTable to be rendered
	 * @param Piwik_DataTable|Piwik_DataTable_Simple|Piwik_DataTable_Array $table to be rendered
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
	 * @throws exception If the renderer is unknown
	 * @return Piwik_DataTable_Renderer
	 */
	static public function factory( $name )
	{
		$name = ucfirst(strtolower($name));
		$path = "DataTable/Renderer/".$name.".php";
		$className = 'Piwik_DataTable_Renderer_' . $name;
		
		if( Piwik_Common::isValidFilename($name)
			&& Zend_Loader::isReadable($path) )
		{
			require_once $path;
			return new $className;			
		}
		else
		{
			throw new Exception("Renderer format '$name' not valid. Try 'xml' or 'json' or 'csv' or 'html' or 'php' or 'original' instead.");
		}		
	}	
}

