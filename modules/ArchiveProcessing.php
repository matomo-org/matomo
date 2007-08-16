<?php

/**
 * The Archive_Processing module is a module that reads the Piwik logs from the DB and
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
 */
require_once 'TablePartitioning.php';
require_once 'ArchiveProcessing/Record.php';
require_once 'DataTable.php';

abstract class Piwik_ArchiveProcessing
{
	const DONE_OK = 1;
	const DONE_ERROR = 2;


	protected $idArchives;
	protected $periodId;
	protected $dateStart;
	protected $dateEnd;
	protected $tableArchiveNumeric;
	protected $tableArchiveBlob;
	protected $maxTimestampArchive;
	
	// Attributes that can be used by plugins
	public $idsite	= null;
	public $period 	= null;
	public $site 	= null;
	
	public $strDateStart;
	public $strDateEnd;
	
	public $logTable;
	public $logVisitActionTable;
	public $logActionTable;
		
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
		if( $this->period->getNumberOfSubperiods() == 1
			&& $this->period->toString() == date("Y-m-d")
			)
		{
			$this->maxTimestampArchive = time() + Zend_Registry('config')->General->time_before_archive_considered_outdated;
		}
	}
	
	public function getTableArchiveNumericName()
	{
		return $this->tableArchiveNumeric;
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
		$idArchive = $this->isArchived();
		if(!$idArchive)
		{
			$this->idArchivesSubperiods = $this->loadSubperiodsArchive();
			
			$this->initCompute();
			$this->compute();
			$this->postCompute();
			
			Piwik::log("New archive computed, id = {$this->idArchives}");
		}
		else
		{
			Piwik::log("Archive already available, id = $idArchive");
		}
		
		return $idArchive;
	}
	
	/**
	 * This methods reads the subperiods if necessary, 
	 * and computes the archive of the current period.
	 */
	abstract protected function compute();
	
	protected function initCompute()
	{
		$this->loadNextIdarchives();
		
		$record = new Piwik_Archive_Processing_Record_Numeric('done', Piwik_ArchiveProcessing::DONE_ERROR);
		$this->insertRecord( $record);
		$record->delete();
		
		$this->logTable 			= Piwik::prefixTable('log_visit');
		$this->logVisitActionTable 	= Piwik::prefixTable('log_link_visit_action');
		$this->logActionTable	 	= Piwik::prefixTable('log_action');
	}
	
	protected function postCompute()
	{
		
//		echo "<br>".Piwik_Archive_ProcessingRecord_Manager::getInstance()->toString();
		
		// delete the first done = ERROR 
		Zend_Registry::get('db')->query("
							DELETE FROM ".$this->tableArchiveNumeric." 
							WHERE idarchive = ? AND name = 'done'",
					array($this->idArchives)
				);
		
		$finalRecord = new Piwik_Archive_Processing_Record_Numeric('done', Piwik_ArchiveProcessing::DONE_OK);
		
		// save in the database the records
		$records = Piwik_Archive_Processing_Record_Manager::getInstance()->getRecords();
		
		foreach($records as $record)
		{
			$this->insertRecord( $record);			
		}
		
		// we delete all tables from the table register
		Piwik_Archive_Processing_Record_Manager::getInstance()->deleteAll();
	} 
	
	
	protected function loadNextIdarchives()
	{
		$db = Zend_Registry::get('db');
		$id = $db->fetchOne("SELECT max(idarchive) FROM ".$this->tableArchiveNumeric);
		if(empty($id))
		{
			$id = 0;
		}
		$this->idArchives = $id + 1;
		
	}
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
		
		$query = "INSERT INTO ".$table." (idarchive, idsite, date1, date2, period, ts_archived, name, value)
					VALUES (?,?,?,?,?,?,?,?)";
		Zend_Registry::get('db')->query($query, array(	$this->idArchives,
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
		$periodsId = array();
		
		// we first compute every subperiod of the archive
		foreach($this->period->getSubperiods() as $period)
		{
			$archivePeriod = Piwik_ArchiveProcessing::factory($period->getLabel());
			
			$archivePeriod->setSite( $this->site );
			$archivePeriod->setPeriod( $period );
			
			$periodsId[] = $archivePeriod->loadArchive();
		}
		
		return $periodsId;
	}
	
	protected function isArchived()
	{
//		Piwik::log("Is archive site=$idsite for period = ".$this->period->getLabel()." for date_start = $strDateStart ?");
		$idarchive = Zend_Registry::get('db')->fetchOne("
						SELECT idarchive
						FROM ".$this->tableArchiveNumeric."
						WHERE idsite = ?
							AND date1 = ?
							AND date2 = ?
							AND period = ?
							AND ts_archived <= ?
							AND name = 'done'
							AND value = ".Piwik_ArchiveProcessing::DONE_OK."
						ORDER BY ts_archived DESC",
						array(	$this->idsite, 
								$this->strDateStart, 
								$this->strDateEnd, 
								$this->periodId, 
								$this->maxTimestampArchive
								)
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
				require_once 'ArchiveProcessing/Week.php';	
				$process = new Piwik_ArchiveProcessing_Week;
			break;
			case 'month':
				require_once 'ArchiveProcessing/Month.php';	
				$process = new Piwik_ArchiveProcessing_Month;
			break;
			case 'year':
				require_once 'ArchiveProcessing/Year.php';	
				$process = new Piwik_ArchiveProcessing_Year;
			break;
			default:
				throw new Exception("Unknown period specified $name");
			break;
		}
		return $process;
	}
	
}
?>
