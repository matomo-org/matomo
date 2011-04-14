<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 *
 * @category Piwik_Plugins
 * @package Piwik_Live
 */

/**
 * @see plugins/Referers/functions.php
 */
require_once PIWIK_INCLUDE_PATH . '/plugins/Live/Visitor.php';

/**
 * @package Piwik_Live
 */
class Piwik_Live_API
{
	static private $instance = null;
	/**
	 * @return Piwik_Live_API
	 */
	static public function getInstance()
	{
		if (self::$instance == null)
		{
			self::$instance = new self;
		}
		return self::$instance;
	}

	/**
	 * This will return simple counters, for a given website ID, for visits over the last N minutes
	 * 
	 * @param int Id Site
	 * @param int Number of minutes to look back at
	 * 
	 * @return array( visits => N, actions => M, visitsConverted => P )
	 */
	public function getCounters($idSite, $lastMinutes, $segment = false)
	{
		Piwik::checkUserHasViewAccess($idSite);
		$lastMinutes = (int)$lastMinutes;
		
		$segment = new Piwik_Segment($segment, $idSite);
		$segmentSql = $segment->getSql();
		$sqlSegment = $segmentSql['sql'];
		if(!empty($sqlSegment)) $sqlSegment = ' AND '.$sqlSegment;
		
		$sql = "SELECT 
				count(*) as visits,
				SUM(visit_total_actions) as actions,
				SUM(visit_goal_converted) as visitsConverted
		FROM ". Piwik_Common::prefixTable('log_visit') ." 
		WHERE idsite = ?
			AND visit_last_action_time >= ?
			$sqlSegment
		";
		$whereBind = array(
			$idSite,
			Piwik_Date::factory(time() - $lastMinutes * 60)->toString('Y-m-d H:i:s')
		);
		$whereBind = array_merge ( $whereBind, $segmentSql['bind'] );
		
		$data = Piwik_FetchAll($sql, $whereBind);
		
		// These could be unset for some reasons, ensure they are set to 0
		empty($data[0]['actions']) ? $data[0]['actions'] = 0 : '';
		empty($data[0]['visitsConverted']) ? $data[0]['visitsConverted'] = 0 : '';
		return $data;
	}
	
	/**
	 * Given a visitorId, will return the last $filter_limit visits for this visitor
	 * 
	 * @param string 16 characters Visitor ID. Typically, you would use the Tracking JS getVisitorId() 
	 * 					(or the PHP tracking equivalent getVisitorId()) to get this value
	 * @param int Site ID
	 * @param int Number of visits to return 
	 * 
	 * @return Piwik_DataTable
	 */
	public function getLastVisitsForVisitor( $visitorId, $idSite, $filter_limit = 10 )
	{
		Piwik::checkUserHasViewAccess($idSite);
		$visitorDetails = $this->loadLastVisitorDetailsFromDatabase($idSite, $period = false, $date = false, $segment = false, $filter_limit, $maxIdVisit = false, $visitorId);
		$table = $this->getCleanedVisitorsFromDetails($visitorDetails, $idSite);
		return $table;
	}

	/**
	 * Returns the last visits tracked in the specified website
	 * You can define any number of filters: none, one, many or all parameters can be defined
	 * 
	 * @param int Site ID
	 * @param string (optional) Period to restrict to when looking at the logs
	 * @param string (optional) Date to restrict to
	 * @param int (optional) Number of visits rows to return
	 * @param int (optional) Maximum idvisit to restrict the query to (useful when paginating)
	 * @param int (optional) Minimum timestamp to restrict the query to (useful when paginating or refreshing visits)
	 * 
	 * @return Piwik_DataTable
	 */
	public function getLastVisitsDetails( $idSite, $period = false, $date = false, $segment = false, $filter_limit = false, $maxIdVisit = false, $minTimestamp = false )
	{
		if(empty($filter_limit)) 
		{
			$filter_limit = 10;
		}
		Piwik::checkUserHasViewAccess($idSite);
		$visitorDetails = $this->loadLastVisitorDetailsFromDatabase($idSite, $period, $date, $segment, $filter_limit, $maxIdVisit, $visitorId = false, $minTimestamp); 
		$dataTable = $this->getCleanedVisitorsFromDetails($visitorDetails, $idSite);
		return $dataTable;
	}

	/**
	 * @deprecated
	 */
	public function getLastVisits( $idSite, $filter_limit = 10, $minTimestamp = false )
	{
		return $this->getLastVisitsDetails($idSite, $period = false, $date = false, $filter_limit, $maxIdVisit = false, $minTimestamp );
	}

	/**
	 * For an array of visits, query the list of pages for this visit 
	 * as well as make the data human readable
	 */
	private function getCleanedVisitorsFromDetails($visitorDetails, $idSite)
	{
		$table = new Piwik_DataTable();

		$site = new Piwik_Site($idSite);
		$timezone = $site->getTimezone();
		foreach($visitorDetails as $visitorDetail)
		{
			$this->cleanVisitorDetails($visitorDetail, $idSite);
			$visitor = new Piwik_Live_Visitor($visitorDetail);
			$visitorDetailsArray = $visitor->getAllVisitorDetails();

			$visitorDetailsArray['siteCurrency'] = $site->getCurrency();
			$visitorDetailsArray['serverTimestamp'] = $visitorDetailsArray['lastActionTimestamp'];
			$dateTimeVisit = Piwik_Date::factory($visitorDetailsArray['lastActionTimestamp'], $timezone);
			$visitorDetailsArray['serverTimePretty'] = $dateTimeVisit->getLocalized('%time%');
			$visitorDetailsArray['serverDatePretty'] = $dateTimeVisit->getLocalized('%shortDay% %day% %shortMonth%');
			
			$dateTimeVisitFirstAction = Piwik_Date::factory($visitorDetailsArray['firstActionTimestamp'], $timezone);
			$visitorDetailsArray['serverDatePrettyFirstAction'] = $dateTimeVisitFirstAction->getLocalized('%shortDay% %day% %shortMonth%');
			$visitorDetailsArray['serverTimePrettyFirstAction'] = $dateTimeVisitFirstAction->getLocalized('%time%');
			
			$idvisit = $visitorDetailsArray['idVisit'];

			// The second join is a LEFT join to allow returning records that don't have a matching page title
			// eg. Downloads, Outlinks. For these, idaction_name is set to 0
			$sql = "
				SELECT
					log_action.type as type,
					log_action.name AS url,
					log_action_title.name AS pageTitle,
					log_action.idaction AS pageIdAction,
					log_link_visit_action.idlink_va AS pageId,
					log_link_visit_action.server_time as serverTimePretty
				FROM " .Piwik_Common::prefixTable('log_link_visit_action')." AS log_link_visit_action
					INNER JOIN " .Piwik_Common::prefixTable('log_action')." AS log_action
					ON  log_link_visit_action.idaction_url = log_action.idaction
					LEFT JOIN " .Piwik_Common::prefixTable('log_action')." AS log_action_title
					ON  log_link_visit_action.idaction_name = log_action_title.idaction
				WHERE log_link_visit_action.idvisit = ?
				 ";
			$actionDetails = Piwik_FetchAll($sql, array($idvisit));
			
			// If the visitor converted a goal, we shall select all Goals
			$sql = "
				SELECT 
						'goal' as type,
						goal.name as goalName,
						goal.revenue as revenue,
						log_conversion.idlink_va as goalPageId,
						log_conversion.server_time as serverTimePretty,
						log_conversion.url as url
				FROM ".Piwik_Common::prefixTable('log_conversion')." AS log_conversion
				LEFT JOIN ".Piwik_Common::prefixTable('goal')." AS goal 
					ON (goal.idsite = log_conversion.idsite
						AND  
						goal.idgoal = log_conversion.idgoal)
					AND goal.deleted = 0
				WHERE log_conversion.idvisit = ?
			";
			$goalDetails = Piwik_FetchAll($sql, array($idvisit));

			$actions = array_merge($actionDetails, $goalDetails);
			
			usort($actions, array($this, 'sortByServerTime'));
			
			$visitorDetailsArray['actionDetails'] = $actions;   
			// Convert datetimes to the site timezone
			foreach($visitorDetailsArray['actionDetails'] as &$details)
			{
				switch($details['type'])
				{
					case 'goal':
					break;
					case Piwik_Tracker_Action_Interface::TYPE_DOWNLOAD:
						$details['type'] = 'download';
					break;
					case Piwik_Tracker_Action_Interface::TYPE_OUTLINK:
						$details['type'] = 'outlink';
					break;
					default:
						$details['type'] = 'action';
					break;
				}
				$dateTimeVisit = Piwik_Date::factory($details['serverTimePretty'], $timezone);
				$details['serverTimePretty'] = $dateTimeVisit->getLocalized('%shortDay% %day% %shortMonth% %time%'); 
			}
			$visitorDetailsArray['goalConversions'] = count($goalDetails);
			
			$table->addRowFromArray( array(Piwik_DataTable_Row::COLUMNS => $visitorDetailsArray));
		}
		return $table;
	}

	private function sortByServerTime($a, $b)
	{
		$ta = strtotime($a['serverTimePretty']);
		$tb = strtotime($b['serverTimePretty']);
		return $ta < $tb 
					? -1 
					: ($ta == $tb 
						? 0 
						: 1 ); 
	}
	
	private function loadLastVisitorDetailsFromDatabase($idSite, $period = false, $date = false, $segment = false, $filter_limit = false, $maxIdVisit = false, $visitorId = false, $minTimestamp = false)
	{
//		var_dump($period); var_dump($date); var_dump($filter_limit); var_dump($maxIdVisit); var_dump($visitorId);
//var_dump($minTimestamp);
		if(empty($filter_limit))
		{
			$filter_limit = 100;
		}
		$where = $whereBind = array();
		$where[] = "log_visit.idsite = ? ";
		$whereBind[] = $idSite;
		
		if(!empty($visitorId))
		{
			$where[] = "log_visit.idvisitor = ? ";
			$whereBind[] = Piwik_Common::hex2bin($visitorId);
		}

		if(!empty($maxIdVisit))
		{
			$where[] = "log_visit.idvisit < ? ";
			$whereBind[] = $maxIdVisit;
		}
		
		if(!empty($minTimestamp))
		{
			$where[] = "log_visit.visit_last_action_time > ? ";
			$whereBind[] = date("Y-m-d H:i:s", $minTimestamp);
		}
		
		// If no other filter, only look at the last 24 hours of stats
		if(empty($visitorId)
			&& empty($maxIdVisit)
			&& empty($period) 
			&& empty($date))
		{
			$period = 'day';
			$date = 'yesterdaySameTime';
		}

		// SQL Filter with provided period
		if (!empty($period) && !empty($date))
		{
			$currentSite = new Piwik_Site($idSite);
			$currentTimezone = $currentSite->getTimezone();
		
			if($period == 'range') 
			{ 
				$processedPeriod = new Piwik_Period_Range('range', $date);
				if($parsedDate = Piwik_Period_Range::parseDateRange($date))
				{
					$dateString = $parsedDate[2];
				}
			}
			else
			{
				$dateString = $date;
				$processedDate = Piwik_Date::factory($date);
				$processedPeriod = Piwik_Period::factory($period, $processedDate); 
			}
			$dateStart = $processedPeriod->getDateStart()->setTimezone($currentTimezone);
			$where[] = "log_visit.visit_last_action_time >= ?";
			$whereBind[] = $dateStart->toString('Y-m-d H:i:s');
			
			if(!in_array($date, array('now', 'today', 'yesterdaySameTime'))
				&& strpos($date, 'last') === false
				&& Piwik_Date::factory($dateString)->toString('Y-m-d') != date('Y-m-d'))
			{
				$dateEnd = $processedPeriod->getDateEnd()->setTimezone($currentTimezone);
				$where[] = " log_visit.visit_last_action_time <= ?";
				$whereBind[] = $dateEnd->addDay(1)->toString('Y-m-d H:i:s');
			}
		}

		$sqlWhere = "";
		if(count($where) > 0)
		{
			$sqlWhere = "
			WHERE " . join(" 
				AND ", $where);
		}

		$segment = new Piwik_Segment($segment, $idSite);
		$segmentSql = $segment->getSql();
		$sqlSegment = $segmentSql['sql'];
		if(!empty($sqlSegment)) $sqlSegment = ' AND '.$sqlSegment;
		$whereBind = array_merge ( $whereBind, $segmentSql['bind'] );
		
		// Subquery to use the indexes for ORDER BY
		// Group by idvisit so that a visitor converting 2 goals only appears twice
		$sql = "
				SELECT sub.* 
				FROM ( 
					SELECT 	*
					FROM " . Piwik_Common::prefixTable('log_visit') . " AS log_visit
					$sqlWhere
					$sqlSegment
					ORDER BY idsite, visit_last_action_time DESC
					LIMIT ".(int)$filter_limit."
				) AS sub
				GROUP BY sub.idvisit
				ORDER BY sub.visit_last_action_time DESC
			"; 
		try {
			$data = Piwik_FetchAll($sql, $whereBind);
		} catch(Exception $e) {
			echo $e->getMessage();exit;
		}
		
//var_dump($whereBind);	echo($sql);
//var_dump($data);
		return $data;
	}
	

	/**
	 * Removes fields that are not meant to be displayed (md5 config hash)
	 * Or that the user should only access if he is super user or admin (cookie, IP)
	 *
	 * @return void
	 */
	private function cleanVisitorDetails( &$visitorDetails, $idSite )
	{
		$toUnset = array('config_id');
		if(Piwik::isUserIsAnonymous())
		{
			$toUnset[] = 'idvisitor';
			$toUnset[] = 'location_ip';
		}
		foreach($toUnset as $keyName)
		{
			if(isset($visitorDetails[$keyName]))
			{
				unset($visitorDetails[$keyName]);
			}
		}
	}
}
