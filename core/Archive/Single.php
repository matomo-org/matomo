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

/**
 * This class is used to store the data of a single archive, 
 * for example the statistics for the 'day' '2008-02-21' for the website idSite '2' 
 *
 */
class Piwik_Archive_Single extends Piwik_Archive
{
	/**
	 * The Piwik_ArchiveProcessing object used to check that the archive is available
	 * and launch the processing if the archive was not yet processed
	 * 
	 * @var Piwik_ArchiveProcessing
	 */
	public $archiveProcessing = null;
	
	/**
	 * @var bool Set to true if the archive has at least 1 visit
	 */
	public $isThereSomeVisits = false;

	/**
	 * Period of this Archive
	 *
	 * @var Piwik_Period
	 */
	protected $period = null;
	
	/**
	 * Set to true will activate numeric value caching for this archive.
	 *
	 * @var bool
	 */
	protected $cacheEnabledForNumeric = true;
	
	/**
	 * Array of cached numeric values, used to make requests faster 
	 * when requesting the same value again and again
	 *
	 * @var array of numeric
	 */
	protected $numericCached = array();
	
	/**
	 * Array of cached blob, used to make requests faster when requesting the same blob again and again
	 *
	 * @var array of mixed
	 */
	protected $blobCached = array();
	
	/**
	 * idarchive of this Archive in the database
	 *
	 * @var int
	 */
	protected $idArchive = null;
	
	/**
	 * Flag set to true once the archive has been checked (when we make sure it is archived)
	 *
	 * @var bool
	 */
	protected $alreadyChecked = false;
	
	/**
	 * Returns the pretty date of this Archive, eg. 'Thursday 20th March 2008'
	 *
	 * @return string
	 */
	public function getPrettyDate()
	{
		return $this->period->getPrettyString();
	}
	
	/**
	 * Returns the idarchive of this Archive used to index this archive in the DB
	 *
	 * @return int
	 */
	public function getIdArchive()
	{
		if(is_null($this->idArchive))
		{
			throw new Exception("idArchive is null");
		}
		return $this->idArchive;
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
	 * Returns the timestamp of the first date in the period for this Archive.
	 * This is used to sort archives by date when working on a Archive_Array
	 *
	 * @return int Unix timestamp
	 */
	public function getTimestampStartDate()
	{
		if(!is_null($this->archiveProcessing))
		{
			return $this->archiveProcessing->getTimestampStartDate();
		}
		
		return $this->period->getDateStart()->getTimestamp();
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
			$this->isThereSomeVisits = false;
			$this->alreadyChecked = true;
			
			// if the END of the period is BEFORE the website creation date
			// we already know there are no stats for this period
			// we add one day to make sure we don't miss the day of the website creation
			if( $this->period->getDateEnd()->addDay(2)->isEarlier( $this->site->getCreationDate() ) )
			{
				return;				
			}
			
			// if the starting date is in the future we know there is no visit
			if( $this->period->getDateStart()->subDay(1)->isLater( Piwik_Date::today() ) )
			{
				return;
			}
			
			// we make sure the archive is available for the given date
			$periodLabel = $this->period->getLabel();
			$archiveProcessing = Piwik_ArchiveProcessing::factory($periodLabel);
			$archiveProcessing->setSite($this->site);
			$archiveProcessing->setPeriod($this->period);
			
			$idArchive = $archiveProcessing->loadArchive();
			$this->isThereSomeVisits = $archiveProcessing->isThereSomeVisits;
			
			$this->archiveProcessing = $archiveProcessing; 

			$this->idArchive = $idArchive;
			$this->alreadyChecked = true;
		}
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
	 * This method loads in memory all the subtables for the main table called $name.
	 * You have to give it the parent table $dataTableToLoad so we can lookup the sub tables ids to load.
	 * 
	 * If $addMetadataSubtableId set to true, it will add for each row a 'metadata' called 'databaseSubtableId' 
	 *  containing the child ID of the subtable  associated to this row.
	 *
	 * @param string $name
	 * @param Piwik_DataTable $dataTableToLoad
	 * @param bool $addMetadataSubtableId
	 * 
	 * @return void
	 */
	public function loadSubDataTables($name, Piwik_DataTable $dataTableToLoad, $addMetadataSubtableId = false)
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
				// in this case we want the user to see the REAL dataId in the database
				if($addMetadataSubtableId)
				{
					$row->addMetadata('databaseSubtableId', $row->getIdSubDataTable());
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
	
	/**
	 * Returns a numeric value from this Archive, with the name '$name'
	 *
	 * @param string $name
	 * @return int|float
	 */
	public function getNumeric( $name )
	{
		// we cast the result as float because returns false when no visitors
		return (float)$this->get($name, 'numeric');
	}

	
	/**
	 * Returns a blob value from this Archive, with the name '$name'
	 * Blob values are all values except int and float.
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function getBlob( $name )
	{
		return $this->get($name, 'blob');		
	}
	
	/**
	 * Given a list of fields defining numeric values, it will return a Piwik_DataTable_Simple 
	 * containing one row per field name.
	 * 
	 * For example $fields = array( 	'max_actions',
	 *						'nb_uniq_visitors', 
	 *						'nb_visits',
	 *						'nb_actions', 
	 *						'sum_visit_length',
	 *						'bounce_count',
	 *					); 
	 *
	 * @param string|array $fields Name or array of names of Archive fields 
	 * 
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
	 * Returns a DataTable that has the name '$name' from the current Archive.
	 * If $idSubTable is specified, returns the subDataTable called '$name_$idSubTable'
	 *
	 * @param string $name
	 * @param int $idSubTable optional id SubDataTable
	 * @return Piwik_DataTable
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
	 * Returns a DataTable that has the name '$name' from the current Archive.
	 * Also loads in memory all subDataTable for this DataTable.
	 * 
	 * For example, if $name = 'Referers_keywordBySearchEngine' it will load all DataTable
	 *  named 'Referers_keywordBySearchEngine_*' and they will be set as subDataTable to the
	 *  rows. You can then go through the rows 
	 * 		$rows = DataTable->getRows();
	 *  and for each row request the subDataTable (in this case the DataTable of the keywords for each search engines)
	 * 		$idSubTable = $row->getIdSubDataTable();
	 * 		$subTable = Piwik_DataTable_Manager::getInstance()->getTable($idSubTable);
	 *  
	 * @param string $name
	 * @param int $idSubTable Optional subDataTable to load instead of loading the parent DataTable
	 * @return Piwik_DataTable
	 */
	public function getDataTableExpanded($name, $idSubTable = null)
	{
		$this->preFetchBlob($name);
		$dataTableToLoad = $this->getDataTable($name, $idSubTable);
		$this->loadSubDataTables($name, $dataTableToLoad, $addMetadataSubtableId = true);
		return $dataTableToLoad;		
	}
}
?>