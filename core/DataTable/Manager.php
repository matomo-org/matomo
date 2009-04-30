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
	 * Id of the last inserted table in the Manager
	 *
	 * @var int
	 */
	protected $lastTableid = 0;
	
	/**
	 * Add a DataTable to the registry
	 * 
	 * @param Piwik_DataTable
	 * @return int Number of tables registered in the manager (including the one just added)
	 */
	public function addTable( $table )
	{
		$this->tables[] = $table;
		$this->lastTableId++;
		return $this->lastTableId - 1;
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
		foreach($this->tables as $id => $table) 
		{
			destroy($table);
		}
		$this->tables = array();
		$this->lastTableId = 0;
	}
	
	/**
	 * Deletes (unsets) the datatable given its id and removes it from the manager
	 * Subsequent get for this table will fail
	 *
	 * @param int $id
	 */
	public function deleteTable( $id )
	{
		if(isset($this->tables[$id]))
		{
			$this->setTableDeleted($id);
			destroy($this->tables[$id]);
		}
	}
	
	/**
	 * Remove the table from the manager (table has already been unset)
	 * @param $id
	 * @return void
	 */
	public function setTableDeleted($id)
	{
		$this->tables[$id] = null;
	}
	
	/**
	 * Debug only. Dumps all tables currently registered in the Manager
	 *
	 * @return void
	 */
	public function dumpAllTables()
	{
		echo "<hr>Piwik_DataTable_Manager->dumpAllTables()<br>";
		foreach($this->tables as $id => $table)
		{
			if(!($table instanceof Piwik_DataTable ))
			{
				echo "Error table $id is not instance of datatable<br>";
				var_dump($table);
			}
			else
			{
				echo "<hr>";
				echo "Table (index=$id) TableId = ". $table->getId() . "<br>";
				echo $table;
				echo "<br>";
			}
		}
		echo "<br>-- End Piwik_DataTable_Manager->dumpAllTables()<hr>";
	}
}

