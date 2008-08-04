<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: Manager.php 519 2008-06-09 01:59:24Z matt $
 * 
 * @package Piwik_DataTable
 */

/**
 * The DataTable_Manager registers all the instanciated DataTable and provides an 
 * easy way to access them. This is used to store all the DataTable during the archiving process.
 * At the end of archiving, the ArchiveProcessing will read the stored datatable and record them in the DB.
 * 
 * @package Piwik_DataTable
 */
class Piwik_DataTable_Manager
{
	static private $instance = null;
	/**
	 * Returns instance
	 *
	 * @return Piwik_DataTable_Manager
	 */
	static public function getInstance()
	{
		if (self::$instance == null)
		{            
			$c = __CLASS__;
			self::$instance = new $c();
		}
		return self::$instance;
	}
	
	/**
	 * Array used to store the DataTable
	 *
	 * @var array
	 */
	protected $tables = array();
		
	/**
	 * Add a DataTable to the registry
	 * 
	 * @param Piwik_DataTable
	 * @return int Number of tables registered in the manager (including the one just added)
	 */
	public function addTable( $table )
	{
		$this->tables[] = $table;
		return count($this->tables) - 1;
	}
	
	/**
	 * Returns the DataTable associated to the ID $idTable.
	 * NB: The datatable has to have been instanciated before! 
	 * This method will not fetch the DataTable from the DB.
	 * 
	 * @exception If the table can't be found
	 * @return Piwik_DataTable The table 
	 */
	public function getTable( $idTable )
	{
		if(!isset($this->tables[$idTable]))
		{
			throw new Exception(sprintf("The requested table (id = %d) couldn't be found in the DataTable Manager", $idTable));
		}
		return $this->tables[$idTable];
	}
	
	/**
	 * Delete all the registered DataTables from the manager
	 * 
	 * @return void
	 */
	public function deleteAll()
	{
		$this->tables = array();
	}
	
	public function deleteTable( $id )
	{
		if(isset($this->tables[$id]))
		{
			$this->tables[$id] = null;
		}
	}
	
	/**
	 * Returns the number of DataTable currently registered.
	 * 
	 * @return int
	 */
	public function count()
	{
		return count($this->tables);
	}
}

