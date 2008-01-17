<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @package Piwik
 */

 
require_once 'Period.php';
require_once 'Date.php';
require_once 'ArchiveProcessing.php';

/**
 * Architecture
 * - *ArchiveProcessing* entity : handle all the computation on an archive / create & delete archive
 * - *Archive* entity: 
 * 		contains the information on an archive, 
 * 		uses the ArchiveProcessing if necessary
 * 		small overhead so we can instanciate many objects of this class for example one for each day 
 * 			of the month
 * - *Website* entity: getId, getUrls, getFirstDay, etc.
 * + *Period* entity: composed of *Date* objects
 * + *Table* entity: serialize, unserialize, sort elements, limit number of elements
 * 		contains all the logic, data structure, etc.
 * 		receives data directly from the sql query via a known API
 * - The *ArchiveProcessing* saves in the DB *numbers* or *Table* objects
 *  
 * @package Piwik
 */

class Piwik_Archive
{
	const INDEX_NB_UNIQ_VISITORS = 1;
	const INDEX_NB_VISITS = 2;
	const INDEX_NB_ACTIONS = 3;
	const INDEX_MAX_ACTIONS = 4;
	const INDEX_SUM_VISIT_LENGTH = 5;
	const INDEX_BOUNCE_COUNT = 6;
	
	protected $period = null;
	protected $id = null;
	protected $isThereSomeVisits = false;
	protected $alreadyChecked = false;
	protected $archiveProcessing = null;
	protected $blobCached = array();
	
	protected $cacheEnabledForNumeric = true;
	
	public function __construct()
	{
	}
	
	static protected $alreadyBuilt = array();
	
	/**
	 * Builds an Archive object or returns the same archive if previously built.
	 *
	 * @param int $idSite
	 * @param string $date 'YYYY-MM-DD' or magic keywords 'today' See Piwik_Date::factory
	 * @param string $period 'week' 'day' etc.
	 * @return Piwik_Archive
	 */
	static public function build($idSite, $period, $date )
	{
//		$archive = new Piwik_Archive_Single($date,);
		
		$oDate = Piwik_Date::factory($date);
		$date = $oDate->toString();
		if(isset(self::$alreadyBuilt[$idSite][$date][$period]))
		{
			return self::$alreadyBuilt[$idSite][$date][$period];
		}
		
		$oPeriod = Piwik_Period::factory($period, $oDate);
		$oSite = new Piwik_Site($idSite);
		
		
		$archive = new Piwik_Archive;
		$archive->setPeriod($oPeriod);
		$archive->setSite($oSite);
		
		self::$alreadyBuilt[$idSite][$date][$period] = $archive;
		return $archive;
	}
	
	/**
	 * Returns the value of the element $name from the current archive 
	 * The value to be returned is a numeric value and is stored in the archive_numeric_* tables
	 *
	 * @param string $name For example Referers_distinctKeywords 
	 * @return float|int|false False if no value with the given name
	 */
	public function getNumeric( $name )
	{
		// we cast the result as float because returns false when no visitors
		return (float)$this->get($name, 'numeric');
	}
	
	/**
	 * Returns the value of the element $name from the current archive
	 * 
	 * The value to be returned is a blob value and is stored in the archive_numeric_* tables
	 * 
	 * It can return anything from strings, to serialized PHP arrays or PHP objects, etc.
	 *
	 * @param string $name For example Referers_distinctKeywords 
	 * @return mixed False if no value with the given name
	 */
	public function getBlob( $name )
	{
		return $this->get($name, 'blob');		
	}
	
	/**
	 * Given a list of fields defining numeric values, it will return a Piwik_DataTable_Simple 
	 * containing one row per value.
	 * 
	 * For example $fields = array( 	'max_actions',
	 *						'nb_uniq_visitors', 
	 *						'nb_visits',
	 *						'nb_actions', 
	 *						'sum_visit_length',
	 *						'bounce_count',
	 *					); 
	 *
	 * @param array $fields array( fieldName1, fieldName2, ...)
	 * @return Piwik_DataTable_Simple
	 */
	public function getDataTableFromNumeric( $fields )
	{
		require_once "DataTable/Simple.php";
		if(!is_array($fields))
		{
			$fields = array($fields);
		}
		
		$values = array();
		foreach($fields as $field)
		{
			$values[$field] = $this->getNumeric($field);
		}
		
		$table = new Piwik_DataTable_Simple;
		$table->loadFromArray($values);
		return $table;
	}

	/**
	 * This method will build a dataTable from the blob value $name in the current archive.
	 * 
	 * For example $name = 'Referers_searchEngineByKeyword' will return a  Piwik_DataTable containing all the keywords
	 * If a idSubTable is given, the method will return the subTable of $name 
	 * 
	 * @param string $name
	 * @param int $idSubTable
	 * @return Piwik_DataTable
	 * @throws exception If the value cannot be found
	 */
	public function getDataTable( $name, $idSubTable = null )
	{
		if(!is_null($idSubTable))
		{
			$name .= "_$idSubTable";
		}
		
		$data = $this->get($name, 'blob');
		
		$table = new Piwik_DataTable;
		
		if($data !== false)
		{
			$table->loadFromSerialized($data);
		}
		
		if($data === false 
			&& $idSubTable !== null)
		{
			throw new Exception("You are requesting a precise subTable but there is not such data in the Archive.");
		}
	
		return $table;
	}

	/**
	 * Same as getDataTable() except that it will also load in memory
	 * all the subtables for the DataTable $name. 
	 * You can then access the subtables by using the Piwik_DataTable_Manager getTable() 
	 *
	 * @param string $name
	 * @param int $idSubTable
	 * @return Piwik_DataTable
	 */
	public function getDataTableExpanded($name, $idSubTable = null)
	{
		$this->preFetchBlob($name);
		$dataTableToLoad = $this->getDataTable($name, $idSubTable);
		$this->loadSubDataTables($name, $dataTableToLoad, $addDetailSubtableId = true);
		return $dataTableToLoad;		
	}
	
	/**
	 * Returns a value from the current archive with the name = $name 
	 * Method used by getNumeric or getBlob
	 *
	 * @param string $name
	 * @param string $typeValue numeric|blob
	 * @return mixed|false if no result
	 */
	protected function get( $name, $typeValue = 'numeric' )
	{
		// values previously "get" and now cached
		if($typeValue == 'numeric'
			&& $this->cacheEnabledForNumeric
			&& isset($this->numericCached[$name])
			)
		{
			return $this->numericCached[$name];
		}
		
		// During archiving we prefetch the blobs recursively
		// and we get them faster from memory after
		if($typeValue == 'blob'
			&& isset($this->blobCached[$name]))
		{
			return $this->blobCached[$name];
		}
		
		$this->prepareArchive();
				
		if($name == 'idarchive')
		{
			return $this->idArchive;
		}
		
//		Piwik::log("-- get '$name'");
		
		if(!$this->isThereSomeVisits)
		{
			return false;
		}

		// select the table to use depending on the type of the data requested		
		switch($typeValue)
		{
			case 'blob':
				$table = $this->archiveProcessing->getTableArchiveBlobName();
			break;

			case 'numeric':
			default:
				$table = $this->archiveProcessing->getTableArchiveNumericName();
			break;
		}

		// we select the requested value
		$db = Zend_Registry::get('db');
		$value = $db->fetchOne("SELECT value 
								FROM $table
								WHERE idarchive = ?
									AND name = ?",	
								array( $this->idArchive , $name) 
							);

		// no result, returns false
		if($value === false)
		{
			if($typeValue == 'numeric' 
				&& $this->cacheEnabledForNumeric)
			{
				// we cache the results
				$this->numericCached[$name] = false;
			}	
			return $value;
		}
		
		// uncompress when selecting from the BLOB table
		if($typeValue == 'blob')
		{
			$value = gzuncompress($value);
		}
		
		if($typeValue == 'numeric' 
			&& $this->cacheEnabledForNumeric)
		{
			// we cache the results
			$this->numericCached[$name] = $value;
		}
		return $value;
	}
	
	/**
	 * Set the period 
	 *
	 * @param Piwik_Period $period
	 */
	public function setPeriod( Piwik_Period $period )
	{
		$this->period = $period;
	}
	
	/**
	 * Set the site
	 *
	 * @param Piwik_Site $site
	 */
	public function setSite( Piwik_Site $site )
	{
		$this->site = $site;
	}

	/**
	 * Returns the Id site associated with this archive
	 *
	 * @return int
	 */
	public function getIdSite()
	{
		return $this->site->getId();
	}
	
	/**
	 * Prepares the archive. Gets the idarchive from the ArchiveProcessing.
	 * 
	 * This will possibly launch the archiving process if the archive was not available.
	 * 
	 * @return void
	 */
	public function prepareArchive()
	{
		if(!$this->alreadyChecked)
		{
			// we make sure the archive is available for the given date
			$periodLabel = $this->period->getLabel();
			$archiveProcessing = Piwik_ArchiveProcessing::factory($periodLabel);
			$archiveProcessing->setSite($this->site);
			$archiveProcessing->setPeriod($this->period);
			$IdArchive = $archiveProcessing->loadArchive();
			
			$this->archiveProcessing = $archiveProcessing;
			$isThereSomeVisits = Zend_Registry::get('db')->fetchOne(
					'SELECT value 
					FROM '.$archiveProcessing->getTableArchiveNumericName().
					' WHERE name = ? AND idarchive = ?', array('nb_visits',$IdArchive));
					
			if($isThereSomeVisits!==false)
			{
				$this->isThereSomeVisits = true;
			}
			$this->idArchive = $IdArchive;
			$this->alreadyChecked = true;
		}
	}
	
	/**
	 * This method loads in memory all the subtables for the main table called $name.
	 * You have to give it the parent table $dataTableToLoad so we can lookup the sub tables ids to load.
	 * 
	 * If $addDetailSubtableId set to true, it will add for each row a 'detail' called 'databaseSubtableId' 
	 *  containing the child ID of the subtable  associated to this row.
	 *
	 * @param string $name
	 * @param Piwik_DataTable $dataTableToLoad
	 * @param bool $addDetailSubtableId
	 * 
	 * @return void
	 */
	public function loadSubDataTables($name, Piwik_DataTable $dataTableToLoad, $addDetailSubtableId = false)
	{
		// we have to recursively load all the subtables associated to this table's rows
		// and update the subtableID so that it matches the newly instanciated table 
		foreach($dataTableToLoad->getRows() as $row)
		{
			$subTableID = $row->getIdSubDataTable();
			
			if($subTableID !== null)
			{
				$subDataTableLoaded = $this->getDataTable($name, $subTableID);
				
				$this->loadSubDataTables($name, $subDataTableLoaded);
				
				// we edit the subtable ID so that it matches the newly table created in memory
				// NB:
				// we dont do that in the case we are displaying the table expanded.
				// in this case we wan't the user to see the REAL dataId in the database
				if($addDetailSubtableId)
				{
					$row->addDetail('databaseSubtableId', $row->getIdSubDataTable());
				}
				$row->setSubtable( $subDataTableLoaded );
			}
		}
	}
	
	
	
	/**
	 * Free the blob cache memory array
	 *
	 * @return void
	 */
	public function freeBlob( $name )
	{
		// we delete the blob
		$this->blobCached = null; 
		$this->blobCached = array(); 
	}
	
	/**
	 * Fetches all blob fields name_* at once for the current archive for performance reasons.
	 * 
	 * @return void
	 */
	public function preFetchBlob( $name )
	{
//		Piwik::log("-- prefetch blob ".$name."_*");
		
		if(!$this->isThereSomeVisits)
		{
			return false;
		}

		$tableBlob = $this->archiveProcessing->getTableArchiveBlobName();

		// we select the requested value
		$db = Zend_Registry::get('db');
		$query = $db->query("SELECT value, name
								FROM $tableBlob
								WHERE idarchive = ?
									AND name LIKE '$name%'",	
								array( $this->idArchive ) 
							);

		while($row = $query->fetch())
		{
			$value = $row['value'];
			$name = $row['name'];
						
			$this->blobCached[$name] = gzuncompress($value);
		}
	}
}





