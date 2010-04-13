<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @category Piwik
 * @package Piwik
 */

/**
 * The ArchiveProcessing module is a module that reads the Piwik logs from the DB and
 * compute all the reports, which are then stored in the database.
 * 
 * The ArchiveProcessing class is used by the Archive object to make sure the given Archive is processed and available in the DB.
 * 
 * A record in the Database for a given report is defined by
 * - idarchive	= unique ID that is associated to all the data of this archive (idsite+period+date)
 * - idsite		= the ID of the website 
 * - date1 		= starting day of the period
 * - date2 		= ending day of the period
 * - period 	= integer that defines the period (day/week/etc.). @see period::getId()
 * - ts_archived = timestamp when the archive was processed
 * - name 		= the name of the report (ex: uniq_visitors or search_keywords_by_search_engines)
 * - value 		= the actual data
 * 
 * @package Piwik
 * @subpackage Piwik_ArchiveProcessing
 */
abstract class Piwik_ArchiveProcessing
{
	/**
	 * Flag stored at the end of the archiving
	 *
	 * @var int
	 */
	const DONE_OK = 1;
	
	/**
	 * Flag stored at the start of the archiving
	 * When requesting an Archive, we make sure that non-finished archive are not considered valid
	 *
	 * @var int
	 */
	const DONE_ERROR = 2;
	
	/**
	 * Flag indicates the archive is over a period that is not finished, eg. the current day, current week, etc.
	 * Archives flagged will be regularly purged from the DB.
	 * 
	 * @var int
	 */
	const DONE_OK_TEMPORARY = 3;

	/**
	 * Idarchive in the DB for the requested archive
	 *
	 * @var int
	 */
	protected $idArchive;
	
	/**
	 * Period id @see Piwik_Period::getId()
	 *
	 * @var int
	 */
	protected $periodId;
	
	/**
	 * Timestamp for the first date of the period
	 *
	 * @var int unix timestamp
	 */
	protected $timestampDateStart = null;
	
	/**
	 * Starting date of the archive
	 * 
	 * @var Piwik_Date
	 */
	protected $dateStart;
	
	/**
	 * Ending date of the archive
	 * 
	 * @var Piwik_Date
	 */
	protected $dateEnd;
	
	/**
	 * Object used to generate (depending on the $dateStart) the name of the DB table to use to store numeric values
	 * 
	 * @var Piwik_TablePartitioning
	 */
	protected $tableArchiveNumeric;
	
	/**
	 * Object used to generate (depending on the $dateStart)  the name of the DB table to use to store numeric values
	 * 
	 * @var Piwik_TablePartitioning
	 */
	protected $tableArchiveBlob;
	
	/**
	 * Minimum timestamp looked at for processed archives  
	 *
	 * @var int
	 */
	protected $minDatetimeArchiveProcessedUTC = false;

	/**
	 * Compress blobs
	 *
	 * @var bool
	 */
	protected $compressBlob;
	
	/**
	 * Is the current archive temporary. ie.
	 * - today 
	 * - current week / month / year
	 */
	protected $temporaryArchive;
	
	/**
	 * Id of the current site
	 * Can be accessed by plugins (that is why it's public)
	 * 
	 * @var int
	 */
	public $idsite	= null;
	
	/**
	 * Period of the current archive
	 * Can be accessed by plugins (that is why it's public)
	 * 
	 * @var $period Piwik_Period
	 */
	public $period 	= null;
	
	/**
	 * Site of the current archive
	 * Can be accessed by plugins (that is why it's public)
	 * 
	 * @var Piwik_Site
	 */
	public $site 	= null;
	
	/**
	 * Starting datetime in UTC
	 *
	 * @var string
	 */
	public $startDatetimeUTC;
	
	/**
	 * Ending date in UTC
	 *
	 * @var string
	 */
	public $strDateEnd;
	
	/**
	 * Name of the DB table _log_visit
	 *
	 * @var string
	 */
	public $logTable;
	
	/**
	 * Name of the DB table _log_link_visit_action
	 *
	 * @var string
	 */
	public $logVisitActionTable;
	
	/**
	 * Name of the DB table _log_action
	 *
	 * @var string
	 */
	public $logActionTable;
	
	/**
	 * When set to true, we always archive, even if the archive is already available.
	 * You can change this settings automatically in the config/global.ini.php always_archive_data under the [Debug] section
	 *
	 * @var bool
	 */
	protected $debugAlwaysArchive = false;
	
	/**
	 * If the archive has at least 1 visit, this is set to true.
	 *
	 * @var bool
	 */
	public $isThereSomeVisits = false;
	
	protected $startTimestampUTC;
	protected $endTimestampUTC;
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
	}
	
	/**
	 * Returns the Piwik_ArchiveProcessing_Day or Piwik_ArchiveProcessing_Period object
	 * depending on $name period string
	 *
	 * @param string $name day|week|month|year
	 * @return Piwik_ArchiveProcessing Piwik_ArchiveProcessing_Day|Piwik_ArchiveProcessing_Period
	 */
	static function factory($name )
	{
		switch($name)
		{
			case 'day':
				$process = new Piwik_ArchiveProcessing_Day();
			break;
			
			case 'week':
			case 'month':
			case 'year':
				$process = new Piwik_ArchiveProcessing_Period();
			break;
			
			default:
				throw new Exception("Unknown period specified $name");
			break;
		}
		return $process;
	}
	
	const OPTION_TODAY_ARCHIVE_TTL = 'todayArchiveTimeToLive';
	const OPTION_BROWSER_TRIGGER_ARCHIVING = 'enableBrowserTriggerArchiving';
	
	static public function setTodayArchiveTimeToLive($timeToLiveSeconds)
	{
		$timeToLiveSeconds = (int)$timeToLiveSeconds;
		if($timeToLiveSeconds <= 0)
		{
			throw new Exception('Today archive time to live must be a number of seconds greater than zero');
		}
		Piwik_SetOption(self::OPTION_TODAY_ARCHIVE_TTL, $timeToLiveSeconds, $autoload = true);
	}
	
	static public function getTodayArchiveTimeToLive()
	{
		$timeToLive = Piwik_GetOption(self::OPTION_TODAY_ARCHIVE_TTL);
		if($timeToLive !== false)
		{
			return $timeToLive;
		}
		return Zend_Registry::get('config')->General->time_before_today_archive_considered_outdated;
	}

	static public function setBrowserTriggerArchiving($enabled)
	{
		if(!is_bool($enabled))
		{
			throw new Exception('Browser trigger archiving must be set to true or false.');
		}
		Piwik_SetOption(self::OPTION_BROWSER_TRIGGER_ARCHIVING, (int)$enabled, $autoload = true);
		
	}
	static public function isBrowserTriggerArchivingEnabled()
	{
		$browserArchivingEnabled = Piwik_GetOption(self::OPTION_BROWSER_TRIGGER_ARCHIVING);
		if($browserArchivingEnabled !== false)
		{
			return (bool)$browserArchivingEnabled;
		}
		return (bool)Zend_Registry::get('config')->General->enable_browser_archiving_triggering;
	}
	
	public function getIdArchive()
	{
		return $this->idArchive;
	}
	
	/**
	 * Sets object attributes that will be used throughout the process
	 */
	public function init()
	{
		$this->idsite = $this->site->getId();
		$this->periodId = $this->period->getId();

		$dateStartLocalTimezone = $this->period->getDateStart();
		$dateEndLocalTimezone = $this->period->getDateEnd();
		
		$this->tableArchiveNumeric = new Piwik_TablePartitioning_Monthly('archive_numeric');
		$this->tableArchiveNumeric->setIdSite($this->idsite);
		$this->tableArchiveNumeric->setTimestamp($dateStartLocalTimezone->getTimestamp());
		$this->tableArchiveBlob = new Piwik_TablePartitioning_Monthly('archive_blob');
		$this->tableArchiveBlob->setIdSite($this->idsite);	
		$this->tableArchiveBlob->setTimestamp($dateStartLocalTimezone->getTimestamp());

		$dateStartUTC = $dateStartLocalTimezone->setTimezone($this->site->getTimezone());
		$dateEndUTC = $dateEndLocalTimezone->setTimezone($this->site->getTimezone());
		$this->startDatetimeUTC = $dateStartUTC->getDateStartUTC();
		$this->endDatetimeUTC = $dateEndUTC->getDateEndUTC();

		$this->startTimestampUTC = $dateStartUTC->getTimestamp();
		$this->endTimestampUTC = strtotime($this->endDatetimeUTC);
		
		$this->minDatetimeArchiveProcessedUTC = $this->getMinTimeArchivedProcessed();
		$db = Zend_Registry::get('db');
		$this->compressBlob = $db->hasBlobDataType();
	}

	public function getStartDatetimeUTC()
	{
		return $this->startDatetimeUTC;
	}
	
	public function getEndDatetimeUTC()
	{
		return $this->endDatetimeUTC;
	}
	
	public function isArchiveTemporary()
	{
		return $this->temporaryArchive;
	}
	
	/**
	 * Returns the minimum archive processed datetime to look at
	 *  
	 * @return string Datetime string, or false if must look at any archive available
	 */
	public function getMinTimeArchivedProcessed()
	{
		$this->temporaryArchive = false;
		// if the current archive is a DAY and if it's today,
		// we set this minDatetimeArchiveProcessedUTC that defines the lifetime value of today's archive
		if( $this->period->getNumberOfSubperiods() == 0
			&& $this->startTimestampUTC <= time() && $this->endTimestampUTC > time()
			)
		{
			$this->temporaryArchive = true;
			$minDatetimeArchiveProcessedUTC = time() - self::getTodayArchiveTimeToLive();
			$browserArchivingEnabled = $this->isArchivingDisabled();
			// see #1150; if new archives are not triggered from the browser, 
			// we still want to try and return the latest archive available for today (rather than return nothing)
			if(!$browserArchivingEnabled)
			{
				return false;
			}
		}
		// either
		// - if the period we're looking for is finished, we look for a ts_archived that 
		//   is greater than the last day of the archive 
		// - if the period we're looking for is not finished, we look for a recent enough archive
		//   recent enough means minDatetimeArchiveProcessedUTC = 00:00:01 this morning
		else
		{
			if($this->endTimestampUTC <= time())
			{
				$minDatetimeArchiveProcessedUTC = $this->endTimestampUTC+1;
			}
			else
			{
    			$this->temporaryArchive = true;
				$minDatetimeArchiveProcessedUTC = Piwik_Date::today()
													->setTimezone($this->site->getTimezone())
													->getTimestamp();
			}
		}
		return $minDatetimeArchiveProcessedUTC;
	}
	
	/**
	 * This method returns the idArchive ; if necessary, it triggers the archiving process.
	 * 
	 * If the archive was not processed yet, it will launch the archiving process.
	 * If the current archive needs sub-archives (eg. a month archive needs all the days archive)
	 *  it will recursively launch the archiving (using this loadArchive() on the sub-periods)
	 *
	 * @return int|false The idarchive of the archive, false if the archive is not archived yet
	 */
	public function loadArchive()
	{
		$this->init();
		$this->idArchive = $this->isArchived();
	
		if($this->idArchive === false
			&& $this->isArchivingDisabled())
		{
			$this->isThereSomeVisits = false;
		}
		elseif($this->idArchive === false
				||	$this->debugAlwaysArchive)
		{
			return null;
		}
		return $this->idArchive;
	}
	
	/**
	 * @see loadArchive()
	 */
	public function launchArchiving()
	{
		$this->initCompute();
		$this->compute();
		$this->postCompute();
		// we execute again the isArchived that does some initialization work
		$this->idArchive = $this->isArchived();
	}
	
	/**
	 * This methods reads the subperiods if necessary, 
	 * and computes the archive of the current period.
	 */
	abstract protected function compute();
	
	/**
	 * Init the object before launching the real archive processing
	 */
	protected function initCompute()
	{
		$this->loadNextIdarchive();
		$this->insertNumericRecord('done', Piwik_ArchiveProcessing::DONE_ERROR);
		$this->logTable 			= Piwik::prefixTable('log_visit');
		$this->logVisitActionTable 	= Piwik::prefixTable('log_link_visit_action');
		$this->logActionTable	 	= Piwik::prefixTable('log_action');
		$this->logConversionTable	= Piwik::prefixTable('log_conversion');
		
		$temporary = 'definitive archive';
		if($this->isArchiveTemporary())
		{
			$temporary = 'temporary archive';
		}
		Piwik::log("Processing archive '" . $this->period->getLabel() . "', 
								idsite = ". $this->idsite." ($temporary) - 
								UTC datetime [".$this->startDatetimeUTC." -> ".$this->endDatetimeUTC." ]...");
	}
	
	/**
	 * Post processing called at the end of the main archive processing.
	 * Makes sure the new archive is marked as "successful" in the DB
	 * 
	 * We also try to delete some stuff from memory but really there is still a lot...
	 */
	protected function postCompute()
	{
		// delete the first done = ERROR 
		Piwik_Query("/* SHARDING_ID_SITE = ".$this->idsite." */ 
					DELETE FROM ".$this->tableArchiveNumeric->getTableName()." 
					WHERE idarchive = ? AND name = 'done'",
					array($this->idArchive)
		);
		
		$flag = Piwik_ArchiveProcessing::DONE_OK;
		if($this->isArchiveTemporary())
		{
			$flag = Piwik_ArchiveProcessing::DONE_OK_TEMPORARY;
		}
		$this->insertNumericRecord('done', $flag);
		
		Piwik_DataTable_Manager::getInstance()->deleteAll();
	}
	
	/**
	 * Returns the name of the numeric table where the archive numeric values are stored
	 *
	 * @return string 
	 */
	public function getTableArchiveNumericName()
	{
		return $this->tableArchiveNumeric->getTableName();
	}
	
	/**
	 * Returns the name of the blob table where the archive blob values are stored
	 *
	 * @return string 
	 */
	public function getTableArchiveBlobName()
	{
		return $this->tableArchiveBlob->getTableName();
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
	 * Returns the timestamp of the first date of the period
	 *
	 * @return int
	 */
	public function getTimestampStartDate()
	{
		return $this->timestampDateStart;
	}

	// exposing the number of visits publicly (number used to compute conversions rates)
	protected $nb_visits = null;
	protected $nb_visits_converted = null;
	
	protected function setNumberOfVisits($nb_visits)
	{
		$this->nb_visits = $nb_visits;
	}
	public function getNumberOfVisits()
	{
		return $this->nb_visits;
	}
	protected function setNumberOfVisitsConverted($nb_visits_converted)
	{
		$this->nb_visits_converted = $nb_visits_converted;
	}
	public function getNumberOfVisitsConverted()
	{
		return $this->nb_visits_converted;
	}
	
	/**
	 * Returns the idArchive we will use for the current archive
	 *
	 * @return int IdArchive to use when saving the current Archive
	 */
	protected function loadNextIdarchive()
	{
		$db = Zend_Registry::get('db');
		$id = $db->fetchOne("/* SHARDING_ID_SITE = ".$this->idsite." */ 
						SELECT max(idarchive) 
						FROM ".$this->tableArchiveNumeric->getTableName());
		if(empty($id))
		{
			$id = 0;
		}
		$this->idArchive = $id + 1;
		
	}

	/**
	 * @param string $name
	 * @param int|float $value
	 * @return Piwik_ArchiveProcessing_Record_Numeric
	 */
	public function insertNumericRecord($name, $value)
	{
		$record = new Piwik_ArchiveProcessing_Record_Numeric($name, $value);
		$this->insertRecord($record);
		return $record;
	}
	
	/**
	 * @param string $name
	 * @param string|array of string $aValues
	 * @return true
	 */
	public function insertBlobRecord($name, $value)
	{
		if(is_array($value))
		{
			$records = new Piwik_ArchiveProcessing_RecordArray($name, $value);
			foreach($records->get() as $record)
			{
				$this->insertRecord($record);
			}
			destroy($records);
			return true;
		}

		if($this->compressBlob)
		{
			$record = new Piwik_ArchiveProcessing_Record_Blob($name, $value);
		}
		else
		{
			$record = new Piwik_ArchiveProcessing_Record($name, $value);
		}

		$this->insertRecord($record);
		destroy($record);
		return true;
	}
	
	/**
	 * Inserts a record in the right table (either NUMERIC or BLOB)
	 *
	 * @param Piwik_ArchiveProcessing_Record $record
	 */
	protected function insertRecord($record)
	{
		// table to use to save the data
		if(is_numeric($record->value))
		{
			$table = $this->tableArchiveNumeric;
		}
		else
		{
			$table = $this->tableArchiveBlob;
		}

		// ignore duplicate idarchive
		// @see http://dev.piwik.org/trac/ticket/987
		$query = "INSERT IGNORE INTO ".$table->getTableName()." (idarchive, idsite, date1, date2, period, ts_archived, name, value)
					VALUES (?,?,?,?,?,?,?,?)";
		Piwik_Query($query, 
							array(	$this->idArchive,
									$this->idsite, 
									$this->period->getDateStart(), 
									$this->period->getDateEnd(), 
									$this->periodId, 
									date("Y-m-d H:i:s"),
									$record->name,
									$record->value,
							)
					);
	}
	
	/**
	 * Returns the idArchive if the archive is available in the database.
	 * Returns false if the archive needs to be computed.
	 * 
	 * An archive is available if
	 * - for today, the archive was computed less than minDatetimeArchiveProcessedUTC seconds ago
	 * - for any other day, if the archive was computed once this day was finished
	 * - for other periods, if the archive was computed once the period was finished
	 *
	 * @return int|false
	 */
	protected function isArchived()
	{
		$bindSQL = array(	$this->idsite, 
							$this->period->getDateStart()->toString('Y-m-d'), 
							$this->period->getDateEnd()->toString('Y-m-d'), 
							$this->periodId, 
		);
		
		$timeStampWhere = '';
		
		if($this->minDatetimeArchiveProcessedUTC)
		{
    		$timeStampWhere = " AND ts_archived >= ? ";
    		$bindSQL[] = Piwik_Date::factory($this->minDatetimeArchiveProcessedUTC)->getDatetime();
		}
		
		$sqlQuery = "	SELECT idarchive, value, name, date1 as startDate
						FROM ".$this->tableArchiveNumeric->getTableName()."
						WHERE idsite = ?
							AND date1 = ?
							AND date2 = ?
							AND period = ?
							AND ( (name = 'done' AND value = ".Piwik_ArchiveProcessing::DONE_OK.")
									OR (name = 'done' AND value = ".Piwik_ArchiveProcessing::DONE_OK_TEMPORARY.")
									OR name = 'nb_visits')
							$timeStampWhere
						ORDER BY ts_archived DESC";
		$results = Piwik_FetchAll($sqlQuery, $bindSQL );
		if(empty($results))
		{
			return false;
		}
		
		$idarchive = false;
		// we look for the more recent idarchive
		foreach($results as $result)
		{
			if($result['name'] == 'done')
			{
				$idarchive = $result['idarchive'];
				$this->timestampDateStart = Piwik_Date::factory($result['startDate'])->getTimestamp();
				break;
			}
		}
		
		// case when we have a nb_visits entry in the archive, but the process is not finished yet or failed to finish
		// therefore we don't have the done=OK
		if($idarchive === false)
		{
			return false;
		}
		
		// we look for the nb_visits result for this most recent archive
		foreach($results as $result)
		{
			if($result['name'] == 'nb_visits' 
				&& $result['idarchive'] == $idarchive)
			{
				$this->isThereSomeVisits = ($result['value'] != 0);
				break;
			}
		}
		return $idarchive;
	}
	
	/**
	 * Returns true if, for some reasons, triggering the archiving is disabled.
	 *
	 * @return bool
	 */
	protected function isArchivingDisabled()
	{
		if(!self::isBrowserTriggerArchivingEnabled()
			&& !Piwik_Common::isPhpCliMode())
		{
			return true;
		}
		return false;
	}
}
