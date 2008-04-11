<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @package Piwik_VisitsSummary
 */


/**
 * 
 * @package Piwik_VisitsSummary
 */
class Piwik_VisitsSummary_API extends Piwik_Apiable
{
	static private $instance = null;
	protected function __construct()
	{
		parent::__construct();
	}
	
	static public function getInstance()
	{
		if (self::$instance == null)
		{            
			$c = __CLASS__;
			self::$instance = new $c();
		}
		return self::$instance;
	}
	
	public function get( $idSite, $period, $date )
	{
		Piwik::checkUserHasViewAccess( $idSite );
		$archive = Piwik_Archive::build($idSite, $period, $date );
	
		$toFetch = array( 	'max_actions',
						'nb_uniq_visitors', 
						'nb_visits',
						'nb_actions', 
						'sum_visit_length',
						'bounce_count',
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

	public function getVisits( $idSite, $period, $date )
	{
		return $this->getNumeric( $idSite, $period, $date, 'nb_visits');
	}
	public function getUniqueVisitors( $idSite, $period, $date )
	{
		return $this->getNumeric( $idSite, $period, $date, 'nb_uniq_visitors');
	}
	public function getActions( $idSite, $period, $date )
	{
		return $this->getNumeric( $idSite, $period, $date, 'nb_actions');
	}
	public function getMaxActions( $idSite, $period, $date )
	{
		return $this->getNumeric( $idSite, $period, $date, 'max_actions');
	}
	public function getSumVisitsLength( $idSite, $period, $date )
	{
		return $this->getNumeric( $idSite, $period, $date, 'sum_visit_length');
	}
	public function getBounceCount( $idSite, $period, $date )
	{
		return $this->getNumeric( $idSite, $period, $date, 'bounce_count');
	}
	
}

