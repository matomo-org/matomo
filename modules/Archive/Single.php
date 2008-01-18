<?php
class Piwik_Archive_Single extends Piwik_Archive
{
	public $archiveProcessing = null;
	public $isThereSomeVisits = false;

	protected $period = null;
	
	protected $blobCached = array();
	protected $cacheEnabledForNumeric = true;
	protected $numericCached = array();
	protected $idArchive = null;
	
	public function getPrettyDate()
	{
		return $this->period->getPrettyString();
	}
	
	public function getIdArchive()
	{
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
	
	public function getTimestampStartDate()
	{
		if(!is_null($this->archiveProcessing))
		{
//			var_dump($this->archiveProcessing->period->getPrettyString());
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
			// if the END of the period is BEFORE the website creation date
			// we already know there are no stats for this period
			// we add one day to make sure we don't miss the day of the website creation
			if( $this->period->getDateEnd()->addDay(2)->isEarlier( $this->site->getCreationDate() ) )
			{
				$this->isThereSomeVisits = false;
				$this->alreadyChecked = true;
				return;				
			}
			
			// if the starting date is in the future we know there is no visit
			if( $this->period->getDateStart()->subDay(1)->isLater( Piwik_Date::today() ) )
			{
				$this->isThereSomeVisits = false;
				$this->alreadyChecked = true;
				return;
			}
			
			// we make sure the archive is available for the given date
			$periodLabel = $this->period->getLabel();
			$archiveProcessing = Piwik_ArchiveProcessing::factory($periodLabel);
			$archiveProcessing->setSite($this->site);
			$archiveProcessing->setPeriod($this->period);
			
			$IdArchive = $archiveProcessing->loadArchive();
			$this->isThereSomeVisits = $archiveProcessing->isThereSomeVisits;
			
			$this->archiveProcessing = $archiveProcessing; 

			$this->idArchive = $IdArchive;
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
	
	
	public function getNumeric( $name )
	{
		// we cast the result as float because returns false when no visitors
		return (float)$this->get($name, 'numeric');
	}

	public function getBlob( $name )
	{
		return $this->get($name, 'blob');		
	}
	
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
	
	public function getDataTableExpanded($name, $idSubTable = null)
	{
		$this->preFetchBlob($name);
		$dataTableToLoad = $this->getDataTable($name, $idSubTable);
		$this->loadSubDataTables($name, $dataTableToLoad, $addDetailSubtableId = true);
		return $dataTableToLoad;		
	}
}
?>