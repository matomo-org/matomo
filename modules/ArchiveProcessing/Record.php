<?php
/**
 * A Record is a tuple (name, value) to be saved in the database.
 * At its creation, the record registers itself to the RecordManager. 
 * The record will then be automatically saved in the DB once the Archiving process is finished. 
 * 
 * We have two record types available:
 * - numeric ; the value will be saved as float in the DB.
 * 	 It should be used for INTEGER, FLOAT
 * - blob ; the value will be saved in a binary field in the DB
 * 	 It should be used for all the other types: PHP variables, STRING, serialized OBJECTS or ARRAYS, etc.
 * 
 * @package Piwik_ArchiveProcessing
 * @subpackage Piwik_ArchiveProcessing_Record
 */
 
abstract class Piwik_ArchiveProcessing_Record
{
	public $name;
	public $value;
	
	function __construct( $name, $value)
	{
		$this->name = $name;
		$this->value = $value;
		Piwik_ArchiveProcessing_Record_Manager::getInstance()->registerRecord($this);
	}
	public function delete()
	{
		Piwik_ArchiveProcessing_Record_Manager::getInstance()->unregister($this);
	}
	public function __destruct()
	{
	}
}

/**
 * 
 * @subpackage Piwik_ArchiveProcessing_Record
 */
class Piwik_ArchiveProcessing_Record_Manager
{
	protected $records = array();
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
 
/**
 * Numeric record.
 * Example: $record = new Piwik_ArchiveProcessing_Record_Numeric('nb_visitors_live', 15);
 * 
 * @subpackage Piwik_ArchiveProcessing_Record
 */
class Piwik_ArchiveProcessing_Record_Numeric extends Piwik_ArchiveProcessing_Record
{	
	function __construct( $name, $value)
	{
		parent::__construct( $name, $value );
	}
	
	public function __toString()
	{
		return $this->name ." = ". $this->value;
	}
}

/**
 * Blob record.
 * Example: $record = new Piwik_ArchiveProcessing_Record_Blob('visitor_names', serialize(array('piwik-fan', 'php', 'stevie-vibes')));
 * The value will be compressed before being saved in the DB.
 * @subpackage Piwik_ArchiveProcessing_Record
 */
class Piwik_ArchiveProcessing_Record_Blob extends Piwik_ArchiveProcessing_Record
{
	public $name;
	public $value;
	function __construct( $name, $value)
	{
		$value = gzcompress($value);
		parent::__construct( $name, $value );
	}
	public function __toString()
	{
		return $this->name ." = BLOB";//". gzuncompress($this->value);
	}
}


/**
 * Array of blob records.
 * Useful for easily saving splited data in the DB.
 *  
 * Example: $record = new Piwik_ArchiveProcessing_Record_Blob_Array(
 * 				'veryLongBook', 
 * 				0 => serialize(	array( '1st chapter very long, 6MB of data we dont want to save' )),
 * 				1 => serialize(	array( '2nd chapter very long, 8MB of data we dont want to save' )),
 * 				2 => serialize(	array( '3rd chapter very long, 7MB of data we dont want to save' )),
 * 				3 => serialize(	array( '4th chapter very long, 10MB of data we dont want to save' )),
 * 		);
 * 
 * Will be saved in the DB as 
 * 		veryLongBook   => X
 * 		veryLongBook_1 => Y
 * 		veryLongBook_2 => Z
 * 		veryLongBook_3 => M
 * 
 * @subpackage Piwik_ArchiveProcessing_Record
 */
class Piwik_ArchiveProcessing_Record_Blob_Array extends Piwik_ArchiveProcessing_Record
{

	function __construct( $name, $aValue)
	{		
		foreach($aValue as $id => $value)
		{
			// for the parent Table we keep the name
			// for example for the Table of searchEngines we keep the name 'referer_search_engine'
			// but for the child table of 'Google' which has the ID = 9 the name would be 'referer_search_engine_9'
			if($id == 0)
			{
				$newName = $name;
			}
			else
			{
				$newName = $name . '_' . $id;
			}
			$record = new Piwik_ArchiveProcessing_Record_Blob( $newName,  $value );
			
		}
	}
	public function __toString()
	{
		throw new Exception( 'Not valid' );
	}
	public function delete()
	{
		throw new Exception( 'Not valid' );
	}
}



