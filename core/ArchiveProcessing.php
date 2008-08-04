<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: ArchiveProcessing.php 536 2008-06-27 01:32:25Z matt $
 * 
 * @package Piwik_ArchiveProcessing
 */

require_once 'TablePartitioning.php';
require_once 'ArchiveProcessing/Record.php';
require_once 'DataTable.php';

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
 * @package Piwik_ArchiveProcessing
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
	 * Maximum timestamp above which a given archive is considered out of date 
	 *
	 * @var int
	 */
	protected $maxTimestampArchive;
	
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
	 * @var Piwik_Period
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
	 * Starting date @see Piwik_Date::toString()
	 *
	 * @var string
	 */
	public $strDateStart;
	
	/**
	 * Ending date @see Piwik_Date::toString()
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
	 * Builds the archive processing object, 
	 * Reads some configuration value from the config file
	 *
	 */
	public function __construct()
	{
		$this->debugAlwaysArchive = Zend_Registry::get('config')->Debug->always_archive_data;
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
				require_once 'ArchiveProcessing/Day.php';			
				$process = new Piwik_ArchiveProcessing_Day;
			break;
			
			case 'week':
			case 'month':
			case 'year':
				require_once 'ArchiveProcessing/Period.php';	
				$process = new Piwik_ArchiveProcessing_Period;
			break;
			
			default:
				throw new Exception("Unknown period specified $name");
			break;
		}
		return $process;
	}
	
	/**
	 * Inits the object
	 * 
	 * @return void
	 */
	protected function loadArchiveProperties()
	{		
		$this->idsite = $this->site->getId();
		
		$this->periodId = $this->period->getId();
		
		$this->dateStart = $this->period->getDateStart();
		$this->dateEnd = $this->period->getDateEnd();
		
		$this->tableArchiveNumeric = new Piwik_TablePartitioning_Monthly('archive_numeric');
		$this->tableArchiveNumeric->setTimestamp($this->dateStart->get());
		$this->tableArchiveBlob = new Piwik_TablePartitioning_Monthly('archive_blob');
		$this->tableArchiveBlob->setTimestamp($this->dateStart->get());

		$this->strDateStart = $this->dateStart->toString();
		$this->strDateEnd = $this->dateEnd->toString();
		
		// if the current archive is a DAY and if it's today,
		// we set this maxTimestampArchive that defines the lifetime value of today's archive
		$this->maxTimestampArchive = 0;
		if( $this->period->getNumberOfSubperiods() == 0
			&& $this->period->toString() == date("Y-m-d")
			)
		{
			//TODO this TIMESTAMP should be a mysql NOW()!!!!
			$this->maxTimestampArchive = time() - Zend_Registry::get('config')->General->time_before_archive_considered_outdated;
		}
		// either
		// - if the period we're looking for is finished, we look for a ts_archived that 
		//   is greater than the last day of the archive 
		// - if the period we're looking for is not finished, we look for a recent enough archive
		//   recent enough means maxTimestampArchive = 00:00:01 this morning
		else
		{
			if($this->period->isFinished())
			{
				$this->maxTimestampArchive = $this->period->getDateEnd()->setTime('00:00:00')->addDay(1)->getTimestamp();
			}
			else
			{
				$this->maxTimestampArchive = Piwik_Date::today()->getTimestamp();
			}
		}
	}
	
	/**
	 * This method returns the idArchive ; if necessary, it triggers the archiving process.
	 * 
	 * If the archive was not processed yet, it will launch the archiving process.
	 * If the current archive needs sub-archives (eg. a month archive needs all the days archive)
	 *  it will recursively launch the archiving (using this loadArchive() on the sub-periods)
	 *
	 * @return int The idarchive of the archive
	 */
	public function loadArchive()
	{
		$this->loadArchiveProperties();
		$this->idArchive = $this->isArchived();
	
		if($this->idArchive === false
			&& $this->isArchivingDisabled())
		{
			$this->isThereSomeVisits = false;
		}
		elseif($this->idArchive === false
				||	$this->debugAlwaysArchive)
		{
			$this->launchArchiving();
		}
		else
		{
			$this->isThereSomeVisits = true;
		}
		
		return $this->idArchive;
	}
	
	/**
	 * @see loadArchive()
	 *
	 */
	protected function launchArchiving()
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
	 * 
	 * @return void
	 */
	protected function initCompute()
	{
		$this->loadNextIdarchive();
		
		$record = new Piwik_ArchiveProcessing_Record_Numeric('done', Piwik_ArchiveProcessing::DONE_ERROR);
		$this->insertRecord($record);
		$record->delete();
		
		$this->logTable 			= Piwik::prefixTable('log_visit');
		$this->logVisitActionTable 	= Piwik::prefixTable('log_link_visit_action');
		$this->logActionTable	 	= Piwik::prefixTable('log_action');
	}
	
	/**
	 * Post processing called at the end of the main archive processing.
	 * Makes sure the new archive is marked as "successful" in the DB
	 * 
	 * We also try to delete some stuff from memory but really there is still a lot...
	 * 
	 * @return void
	 */
	protected function postCompute()
	{
		// delete the first done = ERROR 
		Zend_Registry::get('db')->query("
							DELETE FROM ".$this->tableArchiveNumeric->getTableName()." 
							WHERE idarchive = ? AND name = 'done'",
					array($this->idArchive)
				);
		
		$record = new Piwik_ArchiveProcessing_Record_Numeric('done', Piwik_ArchiveProcessing::DONE_OK);
		
		// save in the database the records
		$records = Piwik_ArchiveProcessing_Record_Manager::getInstance()->getRecords();
		
		foreach($records as $record)
		{
			$this->insertRecord( $record);	
		}
		
		// delete the records from the global manager
		foreach($records as $record)
		{
			$record->delete();	
		}
		unset($records);
		
		// we delete all tables from the table register
		Piwik_ArchiveProcessing_Record_Manager::getInstance()->deleteAll();
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
		// case when archive processing is in the past or the future, the starting date has not been set or processed yet
		if(is_null($this->timestampDateStart))
		{
			return Piwik_Date::factory($this->strDateStart)->getTimestamp();
		}
		return $this->timestampDateStart;
	}
	
	/**
	 * Returns the idArchive we will use for the current archive
	 *
	 * @return int IdArchive to use when saving the current Archive
	 */
	protected function loadNextIdarchive()
	{
		$db = Zend_Registry::get('db');
		$id = $db->fetchOne("SELECT max(idarchive) FROM ".$this->tableArchiveNumeric->getTableName());
		if(empty($id))
		{
			$id = 0;
		}
		$this->idArchive = $id + 1;
		
	}
	
	/**
	 * Inserts a record in the right table (either NUMERIC or BLOB)
	 *
	 * @param Piwik_ArchiveProcessing_Record $record
	 */
	protected function insertRecord($record)
	{
		// table to use to save the data
		if(Piwik::isNumeric($record->value))
		{
			$table = $this->tableArchiveNumeric;
		}
		else
		{
			$table = $this->tableArchiveBlob;
		}
		
		$query = "INSERT INTO ".$table->getTableName()." (idarchive, idsite, date1, date2, period, ts_archived, name, value)
					VALUES (?,?,?,?,?,?,?,?)";
		Zend_Registry::get('db')->query($query, 
							array(	$this->idArchive,
									$this->idsite, 
									$this->strDateStart, 
									$this->strDateEnd, 
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
	 * - for today, the archive was computed less than maxTimestampArchive seconds ago
	 * - for any other day, if the archive was computed once this day was finished
	 * - for other periods, if the archive was computed once the period was finished
	 *
	 * @return int|false
	 */
	protected function isArchived()
	{
		$bindSQL = array(	$this->idsite, 
								$this->strDateStart, 
								$this->strDateEnd, 
								$this->periodId, 
								);
		$timeStampWhere = " AND UNIX_TIMESTAMP(ts_archived) >= ? ";
		$bindSQL[] = $this->maxTimestampArchive;
			
		$sqlQuery = "	SELECT idarchive, value, name, UNIX_TIMESTAMP(date1) as timestamp
						FROM ".$this->tableArchiveNumeric->getTableName()."
						WHERE idsite = ?
							AND date1 = ?
							AND date2 = ?
							AND period = ?
							AND ( (name = 'done' AND value = ".Piwik_ArchiveProcessing::DONE_OK.")
									OR name = 'nb_visits')
							$timeStampWhere
						ORDER BY ts_archived DESC";
		
		$results = Zend_Registry::get('db')->fetchAll($sqlQuery, $bindSQL );
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
				$this->timestampDateStart = $result['timestamp'];
				break;
			}
		}
		
		// case when we have a nb_visits entry in the archive, but the process is not finished yet or failed to finish
		// therefore we don't have the done=OK
		if($idarchive === false)
		{
			return false;
		}
		
		// we look for the nb_visits result for this more recent archive
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
		static $archivingIsDisabled = null;
		
		if(is_null($archivingIsDisabled))
		{
			$archivingIsDisabled = false;
			
			$enableBrowserArchivingTriggering = (bool)Zend_Registry::get('config')->General->enable_browser_archiving_triggering;
			if($enableBrowserArchivingTriggering == false)
			{
				if( !Piwik::isPhpCliMode())
				{
					$archivingIsDisabled = true;
				}
			}
		}
		
		return $archivingIsDisabled;
	}
}
