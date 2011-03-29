<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
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
			self::$instance = new self;
		}
		return self::$instance;
	}
	
	public function get( $idSite, $period, $date, $segment = false, $columns = false)
	{
		Piwik::checkUserHasViewAccess( $idSite );
		$archive = Piwik_Archive::build($idSite, $period, $date, $segment );
	
		// array values are comma separated
		$columns = Piwik::getArrayFromApiParameter($columns);
		$countColumnsRequested = count($columns);
		
		$bounceRateRequested = $actionsPerVisitRequested = $averageVisitDurationRequested = false;
		if(!empty($columns))
		{
			if(($bounceRateRequested = array_search('bounce_rate', $columns)) !== false)
			{
				$columns = array('nb_visits', 'bounce_count');
			}
			elseif(($actionsPerVisitRequested = array_search('nb_actions_per_visit', $columns)) !== false)
			{
				$columns = array('nb_actions', 'nb_visits');
			}
			elseif(($averageVisitDurationRequested = array_search('avg_time_on_site', $columns)) !== false)
			{
				$columns = array('sum_visit_length', 'nb_visits');
			}
		}
		else
		{
    		$bounceRateRequested = $actionsPerVisitRequested = $averageVisitDurationRequested = true;
			$columns = array(	
								'nb_visits',
								'nb_uniq_visitors', 
								'nb_actions', 
								'nb_visits_converted',
								'bounce_count',
								'sum_visit_length',
								'max_actions',
							);
			if(!Piwik::isUniqueVisitorsEnabled($period))
			{
				unset($columns[array_search('nb_uniq_visitors', $columns)]);
			}
		}

		$dataTable = $archive->getDataTableFromNumeric($columns);
		
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
			$dataTable->filter('ColumnCallbackAddColumnQuotient', array('avg_time_on_site', 'sum_visit_length', 'nb_visits', 0));
		}
		
		// If only a computed metrics was requested, we delete other metrics 
		// that we selected only to process this one metric 
		if($countColumnsRequested == 1
			&& ($bounceRateRequested || $actionsPerVisitRequested || $averageVisitDurationRequested)
			) 
		{
			$dataTable->deleteColumns($columns);
		}
		return $dataTable;
	}
	
	protected function getNumeric( $idSite, $period, $date, $segment, $toFetch )
	{
		Piwik::checkUserHasViewAccess( $idSite );
		$archive = Piwik_Archive::build($idSite, $period, $date, $segment );
		$dataTable = $archive->getNumeric($toFetch);
		return $dataTable;		
	}

	public function getVisits( $idSite, $period, $date, $segment = false )
	{
		return $this->getNumeric( $idSite, $period, $date, $segment, 'nb_visits');
	}
	
	public function getUniqueVisitors( $idSite, $period, $date, $segment = false )
	{
		return $this->getNumeric( $idSite, $period, $date, $segment, 'nb_uniq_visitors');
	}
	
	public function getActions( $idSite, $period, $date, $segment = false )
	{
		return $this->getNumeric( $idSite, $period, $date, $segment, 'nb_actions');
	}
	
	public function getMaxActions( $idSite, $period, $date, $segment = false )
	{
		return $this->getNumeric( $idSite, $period, $date, $segment, 'max_actions');
	}
	
	public function getBounceCount( $idSite, $period, $date, $segment = false )
	{
		return $this->getNumeric( $idSite, $period, $date, $segment, 'bounce_count');
	}
	
	public function getVisitsConverted( $idSite, $period, $date, $segment = false )
	{
		return $this->getNumeric( $idSite, $period, $date, $segment, 'nb_visits_converted');
	}
	
	public function getSumVisitsLength( $idSite, $period, $date, $segment = false )
	{
		return $this->getNumeric( $idSite, $period, $date, $segment, 'sum_visit_length');
	}
	
	public function getSumVisitsLengthPretty( $idSite, $period, $date, $segment = false )
	{
		$table = $this->getSumVisitsLength( $idSite, $period, $date, $segment );
		if($table instanceof Piwik_DataTable_Array) {
			$table->filter('ColumnCallbackReplace', array(0, array('Piwik', 'getPrettyTimeFromSeconds')));
		} else {
			$table = Piwik::getPrettyTimeFromSeconds($table);
		}
		return $table;
	}
}
