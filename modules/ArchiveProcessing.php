<?php
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
require_once 'TablePartitioning.php';
require_once 'ArchiveProcessing/Record.php';
require_once 'DataTable.php';

abstract class Piwik_ArchiveProcessing
{
	const DONE_OK = 1;
	const DONE_ERROR = 2;


	protected $idArchive;
	protected $periodId;
	protected $dateStart;
	protected $dateEnd;
	protected $tableArchiveNumeric;
	protected $tableArchiveBlob;
	protected $maxTimestampArchive;
	
	// Attributes that can be accessed by plugins (that is why they are public)
	public $idsite	= null;
	public $period 	= null;
	public $site 	= null;
	
	public $strDateStart;
	public $strDateEnd;
	
	public $logTable;
	public $logVisitActionTable;
	public $logActionTable;
	
	protected $debugAlwaysArchive = false;
	
	public function __construct()
	{
		$this->debugAlwaysArchive = Zend_Registry::get('config')->Debug->always_archive_data;
		//TODO remove
		
	}
	/**
	 * 
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
		
		$this->maxTimestampArchive = 0;
		if( $this->period->getNumberOfSubperiods() == 0
			&& $this->period->toString() == date("Y-m-d")
			)
		{
			$this->maxTimestampArchive = time() - Zend_Registry::get('config')->General->time_before_archive_considered_outdated;
		}
	}
	
	
	public function getTableArchiveNumericName()
	{
		return $this->tableArchiveNumeric;
	}
	
	public function getTableArchiveBlobName()
	{
		return $this->tableArchiveBlob;
	}
	
	
	// to be used only once
	public function setPeriod( Piwik_Period $period ) 
	{
		$this->period = $period;
	}
	
	
	public function setSite( Piwik_Site $site )
	{
		$this->site = $site;
	}
	
	public function loadArchive()
	{
		$this->loadArchiveProperties();
		$this->idArchive = $this->isArchived();
		if(!$this->idArchive)
		{
			Piwik::printMemoryUsage('Before loading subperiods');
			$this->archivesSubperiods = $this->loadSubperiodsArchive();
			Piwik::printMemoryUsage('After loading subperiods');
			$this->initCompute();
			Piwik::printMemoryUsage('After init compute');
			$this->compute();
			Piwik::printMemoryUsage('After compute');
			$this->postCompute();
			Piwik::printMemoryUsage('After post compute');
			
//			Piwik::log("New archive computed, id = {$this->idArchive}");
		}
		else
		{
			//Piwik::log("Archive already available, id = {$this->idArchive}");
		}
		
		return $this->idArchive;
	}
	
	/**
	 * This methods reads the subperiods if necessary, 
	 * and computes the archive of the current period.
	 */
	abstract protected function compute();
	
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
	 */
	protected function loadSubperiodsArchive()
	{
		$periods = array();
		
		// we first compute every subperiod of the archive
		foreach($this->period->getSubperiods() as $period)
		{
			$archivePeriod = new Piwik_Archive;
			$archivePeriod->setSite( $this->site );
			$archivePeriod->setPeriod( $period );
			$archivePeriod->prepareArchive();
			
			$periods[] = $archivePeriod;
		}
		
		return $periods;
	}
	
	protected function isArchived()
	{
		if($this->debugAlwaysArchive)
		{
			return false;
		}
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
			
		$idarchive = Zend_Registry::get('db')->fetchOne("
						SELECT idarchive
						FROM ".$this->tableArchiveNumeric."
						WHERE idsite = ?
							AND date1 = ?
							AND date2 = ?
							AND period = ?
							AND name = 'done'
							AND value = ".Piwik_ArchiveProcessing::DONE_OK."
							$timeStampWhere
						ORDER BY ts_archived DESC",
						$bindSQL
					);
		if(!empty($idarchive))
		{
			return $idarchive;
		}
		else
		{
			return false;
		}
	}
	
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
	
}

