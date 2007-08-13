<?php
/**
 * Archiving process
 * 
 * 
 * Requirements
 * 
 * + needs powerful and easy date handling => Zend_Date
 * + Needs many date helper functions
 * 		from a day, gives the week + list of the days in the week
 * 		from a day, gives the month + list of the days in the month
 * 		from a day, gives the year + list of the days in the year + list of the months in the year
 * - Contact with DB abstracted from the archive process
 * - Handle multi periods: day, week, month, year
 * - Each period logic is separated into different classes 
 *   so that we can in the future easily add new weird periods
 * - support for partial archive (today's archive for example, but not limited to today)
 * 
 * 	Features:
 * - delete logs once used for days
 *   it means that we have to keep the useful information for months/week etc.
 *   check also that the logging process doesn't use the logs we are deleting
 * 
 * 
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
 */
Zend_Loader::loadClass('Piwik_Period');

class Piwik_Site
{
	protected $id = null;
	function __construct($idsite)
	{
		$this->id = $idsite;
	}
	function getId()
	{
		return $this->id;
	}
}

class Piwik_Archive
{
	protected $period = null;
	protected $id = null;
	
	// to be used only once
	public function setPeriod( Piwik_Period $period ) 
	{
		$this->period = $period;
	}
	
	function setSite( Piwik_Site $site )
	{
		$this->site = $site;
	}
	

	// returns a field of the archive
	function get( $name )
	{
		Piwik::log("-- get '$name'");
		// we make sure the archive is available for the given date
		$periodLabel = $this->period->getLabel();
		$archiveProcessing = Piwik_Archive_Processing::factory($periodLabel);
		$archiveProcessing->setSite($this->site);
		$archiveProcessing->setPeriod($this->period);
		$IdArchive = $archiveProcessing->loadArchive();
	}
	
	// fetches many fields at once for performance
	function preFetch( $aName ){}
	
	// check if the archive is available, if not launch the processing
	// to be called only once
	private function loadArchive(){}
	
}

/**
 * The Archive_Processing module is a module that reads the Piwik logs from the DB and
 * compute all the reports, which are then stored in the database.
 * 
 * A record in the Database for a given report is defined by
 * - idsite
 * - report name
 * - 
 * 
 */
Zend_Loader::loadClass('Piwik_TablePartitioning');
abstract class Piwik_Archive_Processing
{
	const DONE_OK = 1;
	const DONE_ERROR = 2;

	protected $period 	= null;
	protected $site 	= null;

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
		$idArchive = $this->isArchived();
		if(!$idArchive)
		{
			$this->idArchivesSubperiods = $this->loadSubperiodsArchive();
			$idArchive = $this->compute();
			Piwik::log("New archive computed, id = $idArchive");
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
	 * 
	 * 
	 */
	protected function compute()
	{		
		$idArchives = $this->idArchivesSubperiods;
		if(count($idArchives) > 0)
		{
			return 30;
		}
		return 10;
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
			$archivePeriod = Piwik_Archive_Processing::factory($period->getLabel());
			
			$archivePeriod->setSite( $this->site );
			$archivePeriod->setPeriod( $period );
			
			$periodsId[] = $archivePeriod->loadArchive();
		}
		
		return $periodsId;
	}
	
	
	protected function isArchived()
	{
		$idsite = $this->site->getId();
		
		$periodId = $this->period->getId();
		
		$dateStart = $this->period->getDateStart();
		$dateEnd = $this->period->getDateEnd();
		
		$this->table = new Piwik_TablePartitioning_Monthly('archive_numeric');
		$this->table->setTimestamp($dateStart->get());

		$strDateStart = $dateStart->toString();
		$strDateEnd = $dateEnd->toString();
		
		$maxTimestampArchive = 0;
		if( $this->period->getNumberOfSubperiods() == 1
			&& $this->period->toString() == date("Y-m-d")
			)
		{
			$maxTimestampArchive = time() + Zend_Registry('config')->General->time_before_archive_considered_outdated;
		}
		
		Piwik::log("Is archive site=$idsite for period = ".$this->period->getLabel()." for date_start = $strDateStart ?");
		$idarchive = Zend_Registry::get('db')->fetchOne("
						SELECT idarchive
						FROM ".$this->table."
						WHERE idsite = ?
							AND date1 = ?
							AND date2 = ?
							AND period = ?
							AND ts_archived <= ?
							AND done = ?",
						array(	$idsite, 
								$strDateStart, 
								$strDateEnd, 
								$periodId, 
								$maxTimestampArchive, 
								Piwik_Archive_Processing::DONE_OK
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
				$process = new Piwik_Archive_Processing_Day;
			break;
			case 'week':
				$process = new Piwik_Archive_Processing_Week;
			break;
			case 'month':
				$process = new Piwik_Archive_Processing_Month;
			break;
			case 'year':
				$process = new Piwik_Archive_Processing_Year;
			break;
			default:
				throw new Exception("Unknown period specified $name");
			break;
		}
		return $process;
	}
	
}

class Piwik_Archive_Processing_Day extends Piwik_Archive_Processing
{
	function __construct()
	{
	}	
}

class Piwik_Archive_Processing_Month extends Piwik_Archive_Processing
{
	function __construct()
	{
	}
	
	
}
class Piwik_Archive_Processing_Year extends Piwik_Archive_Processing
{
	function __construct()
	{
	}
	
	
}





