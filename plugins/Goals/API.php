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
 * @package Piwik_Goals
 */
class Piwik_Goals_API 
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
	
	public function getConversionsReturningVisitors( $idSite, $period, $date, $idGoal = false )
	{
		
	}
	
	public function getConversionsNewVisitors( $idSite, $period, $date, $idGoal = false )
	{
		
	}
	
	// TODO
	public function getConversionRateReturningVisitors( $idSite, $period, $date, $idGoal = false )
	{
		// visits converted for returning for all goals = call Frequency API
		if($idGoal === false)
		{
			$request = new Piwik_API_Request("method=VisitFrequency.getConvertedVisitsReturning&idSite=$idSite&period=$period&date=$date&format=original");
			$nbVisitsConvertedReturningVisitors = $request->process();
		}
		// visits converted for returning = nb conversion for this goal
		else
		{
			$nbVisitsConvertedReturningVisitors = $this->getNumeric($idSite, $period, $date, Piwik_Goals::getRecordName('nb_conversions', $idGoal, 1));
		}
		// all returning visits
		$request = new Piwik_API_Request("method=VisitFrequency.getVisitsReturning&idSite=$idSite&period=$period&date=$date&format=original");
		$nbVisitsReturning = $request->process();
//		echo $nbVisitsConvertedReturningVisitors;
//		echo "<br>". $nbVisitsReturning;exit;
		return $this->getPercentage($nbVisitsConvertedReturningVisitors, $nbVisitsReturning);
	}

	public function getConversionRateNewVisitors( $idSite, $period, $date, $idGoal = false )
	{
		// new visits converted for all goals = nb visits converted - nb visits converted for returning
		if($idGoal == false)
		{
			$request = new Piwik_API_Request("method=VisitsSummary.getVisitsConverted&idSite=$idSite&period=$period&date=$date&format=original");
			$convertedVisits = $request->process();
			$request = new Piwik_API_Request("method=VisitFrequency.getConvertedVisitsReturning&idSite=$idSite&period=$period&date=$date&format=original");
			$convertedReturningVisits = $request->process();
			$convertedNewVisits = $convertedVisits - $convertedReturningVisits;
		}
		// new visits converted for a given goal = nb conversion for this goal for new visits
		else
		{
			$convertedNewVisits = $this->getNumeric($idSite, $period, $date, Piwik_Goals::getRecordName('nb_conversions', $idGoal, 0));
		}
		// all new visits = all visits - all returning visits 
		$request = new Piwik_API_Request("method=VisitFrequency.getVisitsReturning&idSite=$idSite&period=$period&date=$date&format=original");
		$nbVisitsReturning = $request->process();
		$request = new Piwik_API_Request("method=VisitsSummary.getVisits&idSite=$idSite&period=$period&date=$date&format=original");
		$nbVisits = $request->process();
		$newVisits = $nbVisits - $nbVisitsReturning;
		return $this->getPercentage($convertedNewVisits, $newVisits);
	}
	
	protected function getPercentage($a, $b)
	{
		if($b == 0)
		{
			return 0;
		}
		return round(100 * $a / $b, Piwik_Goals::ROUNDING_PRECISION);
	}
	
	public function get( $idSite, $period, $date, $idGoal = false )
	{
		Piwik::checkUserHasViewAccess( $idSite );
		$archive = Piwik_Archive::build($idSite, $period, $date );
		$toFetch = array( 	Piwik_Goals::getRecordName('nb_conversions', $idGoal),
							Piwik_Goals::getRecordName('conversion_rate', $idGoal), 
							Piwik_Goals::getRecordName('revenue', $idGoal),
						);
		$dataTable = $archive->getDataTableFromNumeric($toFetch);
		return $dataTable;
	}
	
	protected static function getNumeric( $idSite, $period, $date, $toFetch )
	{
		Piwik::checkUserHasViewAccess( $idSite );
		$archive = Piwik_Archive::build($idSite, $period, $date );
		$dataTable = $archive->getNumeric($toFetch);
		return $dataTable;		
	}

	public function getConversions( $idSite, $period, $date, $idGoal = false )
	{
		return self::getNumeric( $idSite, $period, $date, Piwik_Goals::getRecordName('nb_conversions', $idGoal));
	}
	
	public function getConversionRate( $idSite, $period, $date, $idGoal = false )
	{
		return self::getNumeric( $idSite, $period, $date, Piwik_Goals::getRecordName('conversion_rate', $idGoal));
	}
	
	public function getRevenue( $idSite, $period, $date, $idGoal = false )
	{
		return self::getNumeric( $idSite, $period, $date, Piwik_Goals::getRecordName('revenue', $idGoal));
	}
	
}
