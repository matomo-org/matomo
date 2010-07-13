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
	
	public function get( $idSite, $period, $date, $columns = array() )
	{
		Piwik::checkUserHasViewAccess( $idSite );
		$archive = Piwik_Archive::build($idSite, $period, $date );
	
		$bounceRateRequested = $actionsPerVisitRequested = $averageVisitDurationRequested = false;
		$countColumnsRequested = count($columns);
		if(!empty($columns))
		{
			$toFetch = $columns;
			if(($bounceRateRequested = array_search('bounce_rate', $toFetch)) !== false)
			{
				$toFetch = array('nb_visits', 'bounce_count');
			}
			elseif(($actionsPerVisitRequested = array_search('nb_actions_per_visit', $toFetch)) !== false)
			{
				$toFetch = array('nb_actions', 'nb_visits');
			}
			elseif(($averageVisitDurationRequested = array_search('avg_visit_length', $toFetch)) !== false)
			{
				$toFetch = array('sum_visit_length', 'nb_visits');
			}
		}
		else
		{
    		$bounceRateRequested = $actionsPerVisitRequested = $averageVisitDurationRequested = true;
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
		
		// Process ratio metrics from base metrics, when requested
		if($bounceRateRequested !== false)
		{
			$dataTable->filter('ColumnCallbackAddColumnPercentage', array('bounce_rate', 'bounce_count', 'nb_visits', 0));
		}
		if($actionsPerVisitRequested !== false)
		{
			$dataTable->filter('ColumnCallbackAddColumnQuotient', array('nb_actions_per_visit', 'nb_actions', 'nb_visits', 1));
		}
		if($averageVisitDurationRequested !== false)
		{
			$dataTable->filter('ColumnCallbackAddColumnQuotient', array('avg_visit_length', 'sum_visit_length', 'nb_visits', 0));
		}
		
		// If only a computed metrics was requested, we delete other metrics 
		// that we selected only to process this one metric 
		if($countColumnsRequested == 1
			&& ($bounceRateRequested || $actionsPerVisitRequested || $averageVisitDurationRequested)
			) 
		{
			$dataTable->deleteColumns($toFetch);
		}
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
	
	public function getBounceCount( $idSite, $period, $date )
	{
		return $this->getNumeric( $idSite, $period, $date, 'bounce_count');
	}
	
	public function getVisitsConverted( $idSite, $period, $date )
	{
		return $this->getNumeric( $idSite, $period, $date, 'nb_visits_converted');
	}
	
	public function getSumVisitsLength( $idSite, $period, $date )
	{
		return $this->getNumeric( $idSite, $period, $date, 'sum_visit_length');
	}
	
	public function getSumVisitsLengthPretty( $idSite, $period, $date )
	{
		return Piwik::getPrettyTimeFromSeconds($this->getSumVisitsLength( $idSite, $period, $date ));
	}
}
