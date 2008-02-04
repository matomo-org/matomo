<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
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
	const DONE_OK = 1;
	const DONE_ERROR = 2;


	protected $idArchive;
	protected $periodId;
	protected $timestampDateStart = null;
	
	/**
	 * @var Piwik_Date
	 */
	protected $dateStart;
	/**
	 * @var Piwik_Date
	 */
	protected $dateEnd;
	
	/**
	 * @var Piwik_TablePartitioning
	 */
	protected $tableArchiveNumeric;
	/**
	 * @var Piwik_TablePartitioning
	 */
	protected $tableArchiveBlob;
	
	protected $maxTimestampArchive;
	
	// Attributes that can be accessed by plugins (that is why they are public)
	public $idsite	= null;
	
	/**
	 * @var Piwik_Period
	 */
	public $period 	= null;
	
	/**
	 * @var Piwik_Site
	 */
	public $site 	= null;
	
	
	// strings
	public $strDateStart;
	public $strDateEnd;
	public $logTable;
	public $logVisitActionTable;
	public $logActionTable;
	
	protected $debugAlwaysArchive = false;
	
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
	 * Assign helper variables // init the object
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
			$this->maxTimestampArchive = time() - Zend_Registry::get('config')->General->time_before_archive_considered_outdated;
		}
	}
	
	/**
	 * Returns the name of the numeric table where the archive numeric values are stored
	 *
	 * @return string 
	 */
	public function getTableArchiveNumericName()
	{
		return (string)$this->tableArchiveNumeric;
	}
	
	/**
	 * Returns the name of the blob table where the archive blob values are stored
	 *
	 * @return string 
	 */
	public function getTableArchiveBlobName()
	{
		return (string)$this->tableArchiveBlob;
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
			|| $this->debugAlwaysArchive)
		{
//			Piwik::printMemoryUsage('Before loading subperiods');
			$this->archivesSubperiods = $this->loadSubperiodsArchive();
//			Piwik::printMemoryUsage('After loading subperiods');
			$this->initCompute();
//			Piwik::printMemoryUsage('After init compute');
			$this->compute();
//			Piwik::printMemoryUsage('After compute');
			$this->postCompute();
//			Piwik::printMemoryUsage('After post compute');

			// we execute again the isArchived that does some initialization work
			$this->idArchive = $this->isArchived();
			
//			Piwik::log("New archive computed, id = {$this->idArchive}");
		}
		else
		{
			//Piwik::log("Archive already available, id = {$this->idArchive}");
			$this->isThereSomeVisits = true;
		}
		
		return $this->idArchive;
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
		$this->insertRecord( $record);
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
	 *
	 */
	protected function postCompute()
	{
//		echo "<br>".Piwik_ArchiveProcessing_Record_Manager::getInstance()->toString();
		
		// delete the first done = ERROR 
		Zend_Registry::get('db')->query("
							DELETE FROM ".$this->tableArchiveNumeric." 
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
	 * Returns the idArchive we will use for the current archive
	 *
	 * @return int IdArchive to use when saving the current Archive
	 */
	protected function loadNextIdarchive()
	{
		$db = Zend_Registry::get('db');
		$id = $db->fetchOne("SELECT max(idarchive) FROM ".$this->tableArchiveNumeric);
		if(empty($id))
		{
			$id = 0;
		}
		$this->idArchive = $id + 1;
		
	}
	
	/**
	 * Inserts a record in the good table (either NUMERIC or BLOB)
	 *
	 * @param unknown_type $record
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
		
		$query = "INSERT INTO ".$table." (idarchive, idsite, date1, date2, period, ts_archived, name, value)
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
	 * Returns the ID of the archived subperiods.
	 * 
	 * @return array Array of the idArchive of the subperiods
	 */
	protected function loadSubperiodsArchive()
	{
		$periods = array();
		
		// we first compute every subperiod of the archive
		foreach($this->period->getSubperiods() as $period)
		{
			$archivePeriod = new Piwik_Archive_Single;
			$archivePeriod->setSite( $this->site );
			$archivePeriod->setPeriod( $period );
			$archivePeriod->prepareArchive();
			
			$periods[] = $archivePeriod;
		}
		
		return $periods;
	}
	
	/**
	 * Returns the idArchive if the archive is available in the database.
	 * Returns false if the archive needs to be computed.
	 *
	 * @return int|false
	 */
	protected function isArchived()
	{
//		Piwik::log("Is archive site=$idsite for period = ".$this->period->getLabel()." for date_start = $strDateStart ?");
		$bindSQL = array(	$this->idsite, 
								$this->strDateStart, 
								$this->strDateEnd, 
								$this->periodId, 
								);
		$timeStampWhere = '';
		if( $this->maxTimestampArchive != 0)
		{
			$timeStampWhere = " AND UNIX_TIMESTAMP(ts_archived) >= ? ";
			$bindSQL[] = $this->maxTimestampArchive;
		}
			
		$sqlQuery = "	SELECT idarchive, value, name, UNIX_TIMESTAMP(date1) as timestamp
						FROM ".$this->tableArchiveNumeric."
						WHERE idsite = ?
							AND date1 = ?
							AND date2 = ?
							AND period = ?
							AND ( (name = 'done' AND value = ".Piwik_ArchiveProcessing::DONE_OK.")
									OR name = 'nb_visits')
							$timeStampWhere
						ORDER BY ts_archived DESC";
		
		$results = Zend_Registry::get('db')->fetchAll($sqlQuery, $bindSQL );
		// the archive exists in the table
		if(!empty($results))
		{
			echo $this->strDateStart . " " . $this->strDateEnd;
			var_dump($results);
			$idarchive = false;
			// let's look for the more recent idarchive
			foreach($results as $result)
			{
				if($result['name'] == 'done')
				{
					$idarchive = $result['idarchive'];
					$this->timestampDateStart = $result['timestamp'];
					break;
				}
			}
			
			if($idarchive === false)
			{
				throw new Exception("Error during the archiving process: ". var_export($results,true));
			}
			
			// let's look for the nb_visits result for this more recent archive
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
		else
		{
			return false;
		}
	}
	
	public function getTimestampStartDate()
	{
		// debug
		if(is_null($this->timestampDateStart))
		{
			throw new Exception("The starting date timestamp has not been set!");
		}
		return $this->timestampDateStart;
	}
}

