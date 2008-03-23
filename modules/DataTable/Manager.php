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
 * easy way to access them. This is used to store all the DataTable during the archiving process.
 * At the end of archiving, the ArchiveProcessing will read the stored datatable and record them in the DB.
 * 
 * @package Piwik_DataTable
 */
class Piwik_DataTable_Manager
{
	static private $instance = null;
	protected function __construct()
	{}
	
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
	 * Number of datatable currently stored in the array
	 *
	 * @var int
	 */
	protected $count = 0;
	
	/**
	 * Add a DataTable to the registry
	 * 
	 * @param Piwik_DataTable
	 * @return int Number of tables registered in the manager (including the one just added)
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
	 * @return Piwik_DataTable The table 
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
	 * Returns all the dataTable registered in the manager
	 * 
	 * @return array of Piwik_DataTable
	 */
	public function getTables()
	{
		return $this->tables;
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

