<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: Record.php 180 2008-01-17 16:32:37Z matt $
 * 
 * @package Piwik_ArchiveProcessing
 */

/**
 * Every new Piwik_ArchiveProcessing_Record will be recorded to this manager when created.
 * At the end of the archiving process, the ArchiveProcessing will getRecords() to save them in the db.
 * This class is singleton. 
 *  
 * @package Piwik_ArchiveProcessing
 * @subpackage Piwik_ArchiveProcessing_Record
 */
class Piwik_ArchiveProcessing_Record_Manager
{
	// array of Piwik_ArchiveProcessing_Record to be recorded in the DB
	protected $records = array();
	
	static private $instance = null;
	protected function __construct()
	{}
	
	/**
	 * Singleton, returns instance
	 *
	 * @return Piwik_ArchiveProcessing_Record_Manager
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
	 * Method called by Record objects to register themselves.
	 * All records registered here will be saved in the DB at the end of the archiving process. 
	 * @return void
	 */
	public function registerRecord( $record )
	{
		$this->records[$record->name] = $record;
	}
	
	/**
	 * Removes a record from the Record Manager.
	 * 
	 * @return void
	 */
	public function unregister( $deleteRecord )
	{
		unset($this->records[$deleteRecord->name]);
	}
	
	/**
	 * Returns a string containing the "name : value" of the record
	 * @return string
	 */
	public function toString()
	{
		$str = '';
		foreach($this->records as $record)
		{
			$str .= $record . "<br>\n";
		}
		return $str;
	}
	
	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->toString();
	}
	
	/**
	 * Returns the list of all the records that have to created in the database.
	 * 
	 * @return array of Records
	 */
	public function getRecords()
	{
		return $this->records;
	}
	
	/**
	 * Delete all records saved in the Manager.
	 * @return void
	 */
	public function deleteAll()
	{
		foreach($this->records as $key => $record)
		{
			unset($this->records[$key]);
		}
		$this->records = array();
	}
}
 
