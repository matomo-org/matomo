<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @category Piwik_Plugins
 * @package Piwik_VisitsSummary
 */

/**
 *
 * @package Piwik_VisitsSummary
 */
class Piwik_VisitsSummary_API 
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
	
	static public function get( $idSite, $period, $date, $columns = array() )
	{
		Piwik::checkUserHasViewAccess( $idSite );
		$archive = Piwik_Archive::build($idSite, $period, $date );
	
		$bounceRateRequested = false;
		if(!empty($columns))
		{
			$toFetch = $columns;
			if(($bounceRateRequested = array_search('bounce_rate', $toFetch)) !== false)
			{
				$toFetch = array('nb_visits', 'bounce_count');
			}
		}
		else
		{
			$toFetch = array(	'max_actions',
								'nb_uniq_visitors', 
								'nb_visits',
								'nb_actions', 
								'sum_visit_length',
								'bounce_count',
								'nb_visits_converted',
							);
		}
		$dataTable = $archive->getDataTableFromNumeric($toFetch);
		if($bounceRateRequested !== false)
		{
			$dataTable->filter('ColumnCallbackAddColumnPercentage', array('bounce_count', 'bounce_rate', 'nb_visits', 0));
			$dataTable->deleteColumns($toFetch);
		}
		return $dataTable;
	}
	
	static protected function getNumeric( $idSite, $period, $date, $toFetch )
	{
		Piwik::checkUserHasViewAccess( $idSite );
		$archive = Piwik_Archive::build($idSite, $period, $date );
		$dataTable = $archive->getNumeric($toFetch);
		return $dataTable;		
	}

	static public function getVisits( $idSite, $period, $date )
	{
		return self::getNumeric( $idSite, $period, $date, 'nb_visits');
	}
	
	static public function getUniqueVisitors( $idSite, $period, $date )
	{
		return self::getNumeric( $idSite, $period, $date, 'nb_uniq_visitors');
	}
	
	static public function getActions( $idSite, $period, $date )
	{
		return self::getNumeric( $idSite, $period, $date, 'nb_actions');
	}
	
	static public function getMaxActions( $idSite, $period, $date )
	{
		return self::getNumeric( $idSite, $period, $date, 'max_actions');
	}
	
	static public function getBounceCount( $idSite, $period, $date )
	{
		return self::getNumeric( $idSite, $period, $date, 'bounce_count');
	}
	
	static public function getVisitsConverted( $idSite, $period, $date )
	{
		return self::getNumeric( $idSite, $period, $date, 'nb_visits_converted');
	}
	
	static public function getSumVisitsLength( $idSite, $period, $date )
	{
		return self::getNumeric( $idSite, $period, $date, 'sum_visit_length');
	}
	
	static public function getSumVisitsLengthPretty( $idSite, $period, $date )
	{
		return Piwik::getPrettyTimeFromSeconds(self::getSumVisitsLength( $idSite, $period, $date ));
	}
}
