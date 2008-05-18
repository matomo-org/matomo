<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @package Piwik_VisitFrequency
 */


/**
 * 
 * @package Piwik_VisitFrequency
 */
class Piwik_VisitFrequency_API extends Piwik_Apiable
{
	static private $instance = null;
	static public function getInstance()
	{
		if (self::$instance == null)
		{            
			$c = __CLASS__;
			self::$instance = new $c();
		}
		return self::$instance;
	}
	
	public function getSummary( $idSite, $period, $date )
	{
		Piwik::checkUserHasViewAccess( $idSite );
		$archive = Piwik_Archive::build($idSite, $period, $date );
		$toFetch = array( 	'nb_uniq_visitors_returning',
							'nb_visits_returning',
							'nb_actions_returning',
							'max_actions_returning',
							'sum_visit_length_returning',
							'bounce_count_returning',
				);
		$dataTable = $archive->getDataTableFromNumeric($toFetch);

		return $dataTable;
	}

	protected function getNumeric( $idSite, $period, $date, $toFetch )
	{
		Piwik::checkUserHasViewAccess( $idSite );
		$archive = Piwik_Archive::build($idSite, $period, $date );
		$dataTable = $archive->getNumeric($toFetch);
		return $dataTable;		
	}

	public function getVisitsReturning( $idSite, $period, $date )
	{
		return $this->getNumeric( $idSite, $period, $date, 'nb_visits_returning');
	}
	public function getActionsReturning( $idSite, $period, $date )
	{
		return $this->getNumeric( $idSite, $period, $date, 'nb_actions_returning');
	}
	public function getMaxActionsReturning( $idSite, $period, $date )
	{
		return $this->getNumeric( $idSite, $period, $date, 'max_actions_returning');
	}
	public function getSumVisitsLengthReturning( $idSite, $period, $date )
	{
		return $this->getNumeric( $idSite, $period, $date, 'sum_visit_length_returning');
	}
	public function getBounceCountReturning( $idSite, $period, $date )
	{
		return $this->getNumeric( $idSite, $period, $date, 'bounce_count_returning');
	}
}

