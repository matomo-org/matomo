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
require_once 'Period.php';
require_once 'ArchiveProcessing.php';

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
	
	// to be used only once
	public function setPeriod( Piwik_Period $period ) 
	{
		$this->period = $period;
	}
	
	function setSite( Piwik_Site $site )
	{
		$this->site = $site;
	}
	
	
	function prepareArchive()
	{
		if(!$this->alreadyChecked)
		{
			// we make sure the archive is available for the given date
			$periodLabel = $this->period->getLabel();
			$archiveProcessing = Piwik_ArchiveProcessing::factory($periodLabel);
			$archiveProcessing->setSite($this->site);
			$archiveProcessing->setPeriod($this->period);
			$IdArchive = $archiveProcessing->loadArchive();
			
			$isThereSomeVisits = Zend_Registry::get('db')->fetchOne(
					'SELECT value 
					FROM '.$archiveProcessing->getTableArchiveNumericName().
					' WHERE name = ? AND idarchive = ?', array('nb_visits',$IdArchive));
					
			if($isThereSomeVisits!==false)
			{
				$this->isThereSomeVisits = true;
			}
			$this->alreadyChecked = true;
		}
	}

	// returns a field of the archive
	function get( $name )
	{
		$this->prepareArchive();
		Piwik::log("-- get '$name'");
		
		if(!$this->isThereSomeVisits)
		{
			return false;
		}
		return 1;
		// select the data requested
		
	}
	
	// fetches many fields at once for performance
	function preFetch( $aName )
	{
		
	}	
}





