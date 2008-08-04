<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: TablePartitioning.php 386 2008-03-18 19:27:54Z julien $
 * 
 * @package Piwik
 */

/**
 * 
 * NB: When a new table is partitionned using this class, we have to update the method
 *     Piwik::getTablesInstalled() to add the new table to the list of tablename_* to fetch
 * 
 * @package Piwik
 */
abstract class Piwik_TablePartitioning
{
	protected $tableName = null;
	protected $generatedTableName = null;
	protected $timestamp = null;
	
	static public $tablesAlreadyInstalled = null;
	
	public function __construct( $tableName )
	{
		$this->tableName = $tableName;
	}
	
	abstract protected function generateTableName() ;
	
	
	public function setTimestamp( $timestamp )
	{
		$this->timestamp = $timestamp;
		$this->generatedTableName = null;
		$this->getTableName();
	}
		
	public function getTableName()
	{
		// table name already processed
		if(!is_null($this->generatedTableName))
		{
			return $this->generatedTableName;
		}
		
		if(is_null($this->timestamp))
		{
			throw new Exception("You have to specify a timestamp for a Table Partitioning by date.");
		}
		
		// generate table name
		$this->generatedTableName = $this->generateTableName();
		 
		// we make sure the table already exists
		$this->checkTableExists();
	}
	
	protected function checkTableExists()
	{
		if(is_null(self::$tablesAlreadyInstalled))
		{
			self::$tablesAlreadyInstalled = Piwik::getTablesInstalled( $forceReload = false );
		}
		
		if(!in_array($this->generatedTableName, self::$tablesAlreadyInstalled))
		{
			$db = Zend_Registry::get('db');
			$sql = Piwik::getTableCreateSql($this->tableName);
			
			$config = Zend_Registry::get('config');
			$prefixTables = $config->database->tables_prefix;
			$sql = str_replace( $prefixTables . $this->tableName, $this->generatedTableName, $sql);
			
			$db->query( $sql );
			
			self::$tablesAlreadyInstalled[] = $this->generatedTableName;
		}
	}
	
	protected function __toString()
	{
		return $this->getTableName();
	}
}

/**
 * 
 * @package Piwik
 */
class Piwik_TablePartitioning_Monthly extends Piwik_TablePartitioning
{
	public function __construct( $tableName )
	{
		parent::__construct($tableName);
	}
	protected function generateTableName()
	{
		$config = Zend_Registry::get('config');
		$prefixTables = $config->database->tables_prefix;
		
		$date = date("Y_m", $this->timestamp);
		
		return $prefixTables . $this->tableName . "_" . $date;
	}
		
}
/**
 * 
 * @package Piwik
 */
class Piwik_TablePartitioning_Daily extends Piwik_TablePartitioning
{
	public function __construct( $tableName )
	{
		parent::__construct($tableName);
	}
	protected function generateTableName()
	{
		$config = Zend_Registry::get('config');
		$prefixTables = $config->database->tables_prefix;
		
		$date = date("Y_m_d", $this->timestamp);
		
		return $prefixTables . $this->tableName . "_" . $date;
	}
		
}

