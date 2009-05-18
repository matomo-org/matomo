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
	
	static public function getGoals( $idSite )
	{
		$goals = Piwik_FetchAll("SELECT * 
											FROM ".Piwik_Common::prefixTable('goal')." 
											WHERE idsite = ?
												AND deleted = 0", $idSite);
		$cleanedGoals = array();
		foreach($goals as &$goal)
		{
			unset($goal['idsite']);
			$cleanedGoals[$goal['idgoal']] = $goal;
		}
		return $cleanedGoals;
	}

	public function addGoal( $idSite, $name, $matchAttribute, $pattern, $patternType, $caseSensitive, $revenue )
	{
		Piwik::checkUserHasAdminAccess($idSite);
		// save in db
		$db = Zend_Registry::get('db');
		$idGoal = $db->fetchOne("SELECT max(idgoal) + 1 
								FROM ".Piwik::prefixTable('goal')." 
								WHERE idsite = ?", $idSite);
		if($idGoal == false)
		{
			$idGoal = 1;
		}
		self::checkPatternIsValid($patternType, $pattern);
		$name = self::checkName($name);
		$pattern = self::checkPattern($pattern);
		$db->insert(Piwik::prefixTable('goal'),
					array( 
						'idsite' => $idSite,
						'idgoal' => $idGoal,
						'name' => $name,
						'match_attribute' => $matchAttribute,
						'pattern' => $pattern,
						'pattern_type' => $patternType,
						'case_sensitive' => $caseSensitive,
						'revenue' => $revenue,
						'deleted' => 0,
					));
		Piwik_Common::regenerateCacheWebsiteAttributes($idSite);
		return $idGoal;
	}
	
	public function updateGoal( $idSite, $idGoal, $name, $matchAttribute, $pattern, $patternType, $caseSensitive, $revenue )
	{
		Piwik::checkUserHasAdminAccess($idSite);
		$name = self::checkName($name);
		$pattern = self::checkPattern($pattern);
		self::checkPatternIsValid($patternType, $pattern);
		Zend_Registry::get('db')->update( Piwik::prefixTable('goal'), 
					array(
						'name' => $name,
						'match_attribute' => $matchAttribute,
						'pattern' => $pattern,
						'pattern_type' => $patternType,
						'case_sensitive' => $caseSensitive,
						'revenue' => $revenue,
						),
					"idsite = '$idSite' AND idgoal = '$idGoal'"
			);	
		Piwik_Common::regenerateCacheWebsiteAttributes($idSite);
	}

	private function checkPatternIsValid($patternType, $pattern)
	{
		if($patternType == 'exact' 
			&& substr($pattern, 0, 4) != 'http')
		{
			throw new Exception("If you choose 'exact match', the matching string must be a 
				URL starting with http:// or https://. For example, 'http://www.yourwebsite.com/newsletter/subscribed.html'.");
		}
	}
	
	private function checkName($name)
	{
		return urldecode($name);
	}
	
	private function checkPattern($pattern)
	{
		return urldecode($pattern);
	}
	
	public function deleteGoal( $idSite, $idGoal )
	{
		Piwik::checkUserHasAdminAccess($idSite);
		Piwik_Query("UPDATE ".Piwik::prefixTable('goal')."
										SET deleted = 1
										WHERE idsite = ? 
											AND idgoal = ?",
									array($idSite, $idGoal));
		$db->query("DELETE FROM ".Piwik::prefixTable("log_conversion")." WHERE idgoal = ?", $idGoal);
		Piwik_Common::regenerateCacheWebsiteAttributes($idSite);
	}
	
//	public function getConversionsReturningVisitors( $idSite, $period, $date, $idGoal = false )
//	{
//		
//	}
//	
//	public function getConversionsNewVisitors( $idSite, $period, $date, $idGoal = false )
//	{
//		
//	}
	
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

		return Piwik::getPercentageSafe($nbVisitsConvertedReturningVisitors, $nbVisitsReturning, Piwik_Goals::ROUNDING_PRECISION);
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
		return Piwik::getPercentageSafe($convertedNewVisits, $newVisits, Piwik_Goals::ROUNDING_PRECISION);
	}
	
	public function get( $idSite, $period, $date, $idGoal = false, $columns = array() )
	{
		Piwik::checkUserHasViewAccess( $idSite );
		$archive = Piwik_Archive::build($idSite, $period, $date );
		if(!empty($columns))
		{
			$toFetch = $columns;
		}
		else
		{
			$toFetch = array(
						'nb_conversions',
						'conversion_rate', 
						'revenue',
					);
			foreach($toFetch as &$columnName)
			{
				$columnName = Piwik_Goals::getRecordName($columnName, $idGoal);
			}
		}
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
