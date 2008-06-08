<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: Request.php 380 2008-03-17 14:59:24Z matt $
 * 
 * 
 * @package Piwik_Archive
 */

require_once "DataTable/Simple.php";
require_once "DataTable/Array.php";
/**
 * This class is used to store multiple archives, when the user requests a period's archive.
 *
 */
abstract class Piwik_Archive_Array extends Piwik_Archive
{	
	/**
	 * This array contains one Piwik_Archive per entry in the period
	 * 
	 * @var array
	 */
	protected $archives = array();
	
	abstract protected function getIndexName();
	abstract protected function getDataTableLabelValue( $archive );
	
	public function prepareArchive()
	{
		foreach($this->archives as $archive)
		{
			$archive->prepareArchive();
		}
	}
	
	/**
	 * Returns a newly created Piwik_DataTable_Array.
	 *
	 * @return Piwik_DataTable_Array
	 */
	protected function getNewDataTableArray()
	{
		$table = new Piwik_DataTable_Array;
		$table->setKeyName($this->getIndexName());
		return $table;
	}
	
	
	
	/**
	 * Adds metadata information to the Piwik_DataTable_Array 
	 * using the information given by the Archive
	 *
	 * @param Piwik_DataTable_Array $table
	 * @param unknown_type $archive
	 */
	protected function loadMetadata(Piwik_DataTable_Array $table, $archive)
	{
	}
	
	/**
	 * Returns a DataTable_Array containing numeric values 
	 * of the element $name from the archives in this Archive_Array.
	 *
	 * @param string $name Name of the mysql table field to load eg. Referers_distinctKeywords
	 * 
	 * @return Piwik_DataTable_Array containing the requested numeric value for each Archive
	 */
	public function getNumeric( $name )
	{
		$table = $this->getNewDataTableArray();
		
		foreach($this->archives as $archive)
		{
			$numeric = $archive->getNumeric( $name ) ;
			$subTable = new Piwik_DataTable_Simple();
			$subTable->loadFromArray( array( $numeric ) );
			$table->addTable($subTable, $this->getDataTableLabelValue($archive));
			
			$this->loadMetadata($table, $archive);
		}
		
		return $table;
	}
	
	
	/**
	 * Returns a DataTable_Array containing values 
	 * of the element $name from the archives in this Archive_Array.
	 *
	 * The value to be returned are blob values (stored in the archive_numeric_* tables in the DB).	 * 
	 * It can return anything from strings, to serialized PHP arrays or PHP objects, etc.
	 *
	 * @param string $name Name of the mysql table field to load eg. Referers_keywordBySearchEngine 
	 * 
	 * @return Piwik_DataTable_Array containing the requested blob values for each Archive
	 */
	public function getBlob( $name )
	{
		$table = $this->getNewDataTableArray();
		
		foreach($this->archives as $archive)
		{
			$blob = $archive->getBlob( $name ) ;
			$subTable = new Piwik_DataTable_Simple();
			$subTable->loadFromArray( array('blob' => $blob));
			$table->addTable($subTable, $this->getDataTableLabelValue($archive));
			
			$this->loadMetadata($table, $archive);
		}
		return $table;
	}
	
	/**
	 * Given a BLOB field name (eg. 'Referers_searchEngineByKeyword'), it will return a Piwik_DataTable_Array
	 * which is an array of Piwik_DataTable, ordered by chronological order
	 * 
	 * @param string $name Name of the mysql table field to load
	 * @param int $idSubTable optional idSubDataTable
	 * @return Piwik_DataTable_Array
	 * @throws exception If the value cannot be found
	 */
	public function getDataTable( $name, $idSubTable = null )
	{		
		$table = $this->getNewDataTableArray();
		foreach($this->archives as $archive)
		{
			$subTable =  $archive->getDataTable( $name, $idSubTable ) ;
			$table->addTable($subTable, $this->getDataTableLabelValue($archive));
			
			$this->loadMetadata($table, $archive);
		}
		return $table;
	}
	
	
	/**
	 * Same as getDataTable() except that it will also load in memory
	 * all the subtables for the DataTable $name. 
	 * You can then access the subtables by using the Piwik_DataTable_Manager::getInstance()->getTable($idSubTable);
	 *
	 * @param string $name Name of the mysql table field to load
	 * @param int $idSubTable optional idSubDataTable
	 * @return Piwik_DataTable_Array
	 */
	public function getDataTableExpanded($name, $idSubTable = null)
	{
		$table = $this->getNewDataTableArray();
		foreach($this->archives as $archive)
		{
			$subTable =  $archive->getDataTableExpanded( $name, $idSubTable ) ;
			$table->addTable($subTable, $this->getDataTableLabelValue($archive));
			
			$this->loadMetadata($table, $archive);
		}
		return $table;
	}
}
