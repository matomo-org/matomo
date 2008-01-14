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
 * The DataTable_Manager registers all the instanciated DataTable and provides an 
 * easy way to access them.
 * 
 * @package Piwik_DataTable
 */
class Piwik_DataTable_Manager
{
	static private $instance = null;
	protected function __construct()
	{}
	
	static public function getInstance()
	{
		if (self::$instance == null)
		{            
			$c = __CLASS__;
			self::$instance = new $c();
		}
		return self::$instance;
	}
	
	protected $tables = array();
	protected $count = 0;
	
	/**
	 * Add a DataTable to the registry
	 */
	public function addTable( $table )
	{
		$this->tables[] = $table;
		$this->count++;
		return $this->count;
	}
	
	/**
	 * Returns the DataTable associated to the ID $idTable.
	 * NB: The datatable has to have been instanciated before! 
	 * This method will not fetch the DataTable from the DB.
	 * 
	 * @exception If the table can't be found
	 */
	public function getTable( $idTable )
	{
		// the array tables is indexed at 0 
		// but the index is computed as the count() of the array after inserting the table
		$idTable -= 1;
		
		if(!isset($this->tables[$idTable]))
		{
			throw new Exception("The request table $idTable couldn't be found.");
		}
		
		return $this->tables[$idTable];
	}
	
	/**
	 * Delete all the registered DataTables
	 */
	public function deleteAll()
	{
		$this->tables = array();
	}
	
	/**
	 * Returns the number of DataTable currently registered.
	 */
	function count()
	{
		return count($this->tables);
	}
}

