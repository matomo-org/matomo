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
class Piwik_Archive_Array extends Piwik_Archive
{	
	// this array contains one Piwik_Archive per entry in the period
	protected $archives = array();
	
	// stores the timestamp of each archive, used to sort the archives by date
	protected $idArchiveToTimestamp = array();
	
	// array containing the id of the archives stored in this object
	protected $idArchives = array();
	
	/**
	 * Builds an array of Piwik_Archive of a given date range
	 *
	 * @param Piwik_Site $oSite 
	 * @param string $strPeriod eg. 'day' 'week' etc.
	 * @param string $strDate A date range, eg. 'last10', 'previous5' or 'YYYY-MM-DD,YYYY-MM-DD'
	 */
	function __construct(Piwik_Site $oSite, $strPeriod, $strDate)
	{
		$rangePeriod = new Piwik_Period_Range($strPeriod, $strDate);
		
		// TODO fix this when aggregating data from multiple websites
		// CAREFUL this class wouldnt work as is if handling archives from multiple websites
		// works only when managing archives from multiples dates/periods
		foreach($rangePeriod->getSubperiods() as $subPeriod)
		{
			$startDate = $subPeriod->getDateStart();
			$archive = Piwik_Archive::build($oSite->getId(), $strPeriod, $startDate );
			$archive->prepareArchive();
			$timestamp = $archive->getTimestampStartDate();
			$this->archives[$timestamp] = $archive;
		}
		ksort( $this->archives );
	}
	
	/**
	 * Returns a newly created Piwik_DataTable_Array.
	 * The future elements of this array should be indexed by their dates (we set the index name to 'date').
	 *
	 * @return Piwik_DataTable_Array
	 */
	protected function getNewDataTableArray()
	{
		$table = new Piwik_DataTable_Array;
		$table->setNameKey('date');
		return $table;
	}

	/**
	 * Adds metaData information to the Piwik_DataTable_Array 
	 * using the information given by the Archive
	 *
	 * @param Piwik_DataTable_Array $table
	 * @param unknown_type $archive
	 */
	protected function loadMetaData(Piwik_DataTable_Array $table, $archive)
	{
		$table->metaData[$archive->getPrettyDate()] = array( 
				'timestamp' => $archive->getTimestampStartDate(),
				'site' => $archive->getSite(),
			);
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
			$table->addTable($subTable, $archive->getPrettyDate());
			
			$this->loadMetaData($table, $archive);
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
			$table->addTable($subTable, $archive->getPrettyDate());
			
			$this->loadMetaData($table, $archive);
		}
		return $table;
	}
	
	/**
	 * Given a list of fields defining numeric values, it will return a Piwik_DataTable_Array
	 * which is an array of Piwik_DataTable_Simple, ordered by chronological order
	 *
	 * @param array|string $fields array( fieldName1, fieldName2, ...)  Names of the mysql table fields to load
	 * @return Piwik_DataTable_Array
	 */
	public function getDataTableFromNumeric( $fields )
	{
		// Simple algorithm not efficient that does the same as the following code
		/*
		$table = new Piwik_DataTable_Array;
		foreach($this->archives as $archive)
		{
			$subTable =  $archive->getDataTableFromNumeric( $fields ) ;
			$table->addTable($subTable, $archive->getPrettyDate());
		}
		return $table;
		*/

		if(!is_array($fields))
		{
			$fields = array($fields);
		}
		
		$inName = "'" . implode("', '",$fields) . "'";
		
		
		// we select in different shots
		// one per distinct table (case we select last 300 days, maybe we will  select from 10 different tables)
		$queries = array();
		foreach($this->archives as $archive) 
		{
			if(!$archive->isThereSomeVisits)
			{
				continue;
			}
			
			$table = $archive->archiveProcessing->getTableArchiveNumericName();

			// for every query store IDs
			$queries[$table][] = $archive->getIdArchive();
		}
//		var_dump($queries);

		// we select the requested value
		$db = Zend_Registry::get('db');
		
		// date => array( 'field1' =>X, 'field2'=>Y)
		// date2 => array( 'field1' =>X2, 'field2'=>Y2)		
		
		$idarchiveToName = array();
		foreach($queries as $table => $aIds)
		{
			$inIds = implode(', ', $aIds);
			$sql = "SELECT value, name, idarchive, UNIX_TIMESTAMP(date1) as timestamp
									FROM $table
									WHERE idarchive IN ( $inIds )
										AND name IN ( $inName )";

			$values = $db->fetchAll($sql);
			
			foreach($values as $value)
			{
				$idarchiveToName[$value['timestamp']][$value['name']] = $value['value'];
			}			
		}
		
		// we add empty tables so that every requested date has an entry, even if there is nothing
		// example: <result date="2007-01-01" />
		$emptyTable = new Piwik_DataTable_Simple;
		foreach($this->archives as $timestamp => $archive)
		{
			$strDate = $this->archives[$timestamp]->getPrettyDate();
			$contentArray[$timestamp]['table'] = clone $emptyTable;
			$contentArray[$timestamp]['prettyDate'] = $strDate;
		}
		
		foreach($idarchiveToName as $timestamp => $aNameValues)
		{
			$contentArray[$timestamp]['table']->loadFromArray($aNameValues);
		}
		
		ksort( $contentArray );
				
		$tableArray = $this->getNewDataTableArray();
		foreach($contentArray as $timestamp => $aData)
		{
			$tableArray->addTable($aData['table'], $aData['prettyDate']);
			
			$this->loadMetaData($tableArray, $this->archives[$timestamp]);
		}
		
//		echo $tableArray;exit;
		return $tableArray;
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
			$table->addTable($subTable, $archive->getPrettyDate());
			
			$this->loadMetaData($table, $archive);
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
			$table->addTable($subTable, $archive->getPrettyDate());
			
			$this->loadMetaData($table, $archive);
		}
		return $table;
	}
}
