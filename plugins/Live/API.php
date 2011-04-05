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

	const TYPE_FETCH_VISITS = 1;
	const TYPE_FETCH_PAGEVIEWS = 2;

	/*
	 * @return Piwik_DataTable
	 */
	public function getLastVisitForVisitor( $visitorId, $idSite )
	{
		Piwik::checkUserHasViewAccess($idSite);
		$filter_limit = 1;
		$visitorDetails = $this->loadLastVisitorDetailsFromDatabase($idSite, $period = false, $date = false, $filter_limit, $minIdVisit = false, $visitorId);
		$table = $this->getCleanedVisitorsFromDetails($visitorDetails, $idSite);
		return $table;
	}

	/*
	 * @return Piwik_DataTable
	 */
	public function getLastVisitsForVisitor( $visitorId, $idSite, $filter_limit = 10 )
	{
		Piwik::checkUserHasViewAccess($idSite);
		$visitorDetails = $this->loadLastVisitorDetailsFromDatabase($idSite, $period = false, $date = false, $filter_limit, $minIdVisit = false, $visitorId);
		$table = $this->getCleanedVisitorsFromDetails($visitorDetails, $idSite);
		return $table;
	}

	/*
	 * @return Piwik_DataTable
	 */
	public function getLastVisits( $idSite, $filter_limit = 10, $minTimestamp = false )
	{
		Piwik::checkUserHasViewAccess($idSite);
		$visitorDetails = $this->loadLastVisitorDetailsFromDatabase($idSite, $period = false, $date = false, $filter_limit, $minIdVisit = false, $visitorId = false, $previous = false, $minTimestamp);
		$table = $this->getCleanedVisitorsFromDetails($visitorDetails, $idSite);
//		echo $table;
		return $table;
	}

	/*
	 * @return Piwik_DataTable
	 */
	public function getLastVisitsDetails( $idSite, $period = false, $date = false, $filter_limit = false, $minIdVisit = false, $previous = false )
	{
		if(empty($filter_limit)) 
		{
			$filter_limit = 20;
		}
		Piwik::checkUserHasViewAccess($idSite);
		$visitorDetails = $this->loadLastVisitorDetailsFromDatabase($idSite, $period, $date, $filter_limit, $minIdVisit, $visitorId = false, $previous); 
		$dataTable = $this->getCleanedVisitorsFromDetails($visitorDetails, $idSite);
		return $dataTable;
	}


	/*
	 * @return Piwik_DataTable
	 */
	public function getUsersInLastXMin( $idSite, $minutes = 30 )
	{
		Piwik::checkUserHasViewAccess($idSite);
		$visitorData = $this->loadLastVisitorInLastXTimeFromDatabase($idSite, $minutes, $days = 0, self::TYPE_FETCH_VISITS);
		return $visitorData;
	}

	/*
	 * @return Piwik_DataTable
	 */
	public function getUsersInLastXDays( $idSite, $days = 10 )
	{
		Piwik::checkUserHasViewAccess($idSite);
		$visitorData = $this->loadLastVisitorInLastXTimeFromDatabase($idSite, $minutes = 0, $days, self::TYPE_FETCH_VISITS);
		return $visitorData;
	}

	/*
	 * @return array
	 */
	public function getPageImpressionsInLastXDays($idSite, $days = 10)
	{
		Piwik::checkUserHasViewAccess($idSite);
		$visitorData = $this->loadLastVisitorInLastXTimeFromDatabase($idSite, $minutes = 0, $days, self::TYPE_FETCH_PAGEVIEWS);
		return $visitorData;
	}

	/*
	 * @return array
	 */
	public function getPageImpressionsInLastXMin($idSite, $minutes = 30)
	{
		Piwik::checkUserHasViewAccess($idSite);
		$visitorData = $this->loadLastVisitorInLastXTimeFromDatabase($idSite, $minutes, $days = 0, self::TYPE_FETCH_PAGEVIEWS);
		return $visitorData;
	}

	/*
	 * @return Piwik_DataTable
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
			
			$dateTimeVisitFirstAction = Piwik_Date::factory($visitorDetailsArray['firstActionTimestamp'], $timezone);
			$visitorDetailsArray['serverDatePretty'] = $dateTimeVisitFirstAction->getLocalized('%shortDay% %day% %shortMonth%');
			$visitorDetailsArray['serverTimePrettyFirstAction'] = $dateTimeVisitFirstAction->getLocalized('%time%');
			$visitorDetailsArray['goalConversions'] = $visitorDetail['count_goal_conversions'];
			if(!empty($visitorDetailsArray['goalTimePretty']))
			{
				$dateTimeConversion = Piwik_Date::factory($visitorDetailsArray['goalTimePretty'], $timezone);
				$visitorDetailsArray['goalTimePretty'] = $dateTimeConversion->getLocalized('%shortDay% %day% %shortMonth% %time%');
			}
			
			$idvisit = $visitorDetailsArray['idVisit'];

			$sql = "
				SELECT
				log_action.name AS pageUrl,
				log_action_title.name AS pageTitle,
				log_action.idaction AS pageIdAction,
				log_link_visit_action.idlink_va AS pageId,
				log_link_visit_action.server_time as serverTime
				FROM " .Piwik_Common::prefixTable('log_link_visit_action')." AS log_link_visit_action
					INNER JOIN " .Piwik_Common::prefixTable('log_action')." AS log_action
					ON  log_link_visit_action.idaction_url = log_action.idaction
					INNER JOIN " .Piwik_Common::prefixTable('log_action')." AS log_action_title
					ON  log_link_visit_action.idaction_name = log_action_title.idaction
				WHERE log_link_visit_action.idvisit = ?
				 ";

			$visitorDetailsArray['actionDetails'] = Piwik_FetchAll($sql, array($idvisit));
			// Convert datetimes to the site timezone
			foreach($visitorDetailsArray['actionDetails'] as &$details)
			{
				$dateTimeVisit = Piwik_Date::factory($details['serverTime'], $timezone);
				$details['serverTime'] = $dateTimeVisit->getDatetime(); 
			}
			$table->addRowFromArray( array(Piwik_DataTable_Row::COLUMNS => $visitorDetailsArray));
		}
		return $table;
	}

	/*
	 * @return array
	 */
	private function loadLastVisitorDetailsFromDatabase($idSite, $period = false, $date = false, $filter_limit = false, $minIdVisit = false, $visitorId = false, $previous = false, $minTimestamp = false)
	{
//		var_dump($period); var_dump($date); var_dump($filter_limit); var_dump($minIdVisit); var_dump($visitorId);
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

		if(!empty($minIdVisit)
			// we can't look back and return fast, 
			// so we disallow: previous is the same as "Start of results"
			&& !$previous)
		{
			$where[] = "log_visit.idvisit < ? ";
			$whereBind[] = $minIdVisit;
		}
		
		if(!empty($minTimestamp))
		{
			$where[] = "log_visit.visit_last_action_time > ? ";
			$whereBind[] = date("Y-m-d H:i:s", $minTimestamp);
		}
		
		// If no other filter, only look at the last 24 hours of stats
		if(empty($visitorId)
			&& empty($minIdVisit)
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

		// Subquery to use the indexes for ORDER BY
		// Group by idvisit so that a visitor converting 2 goals only appears twice
		$sql = "
			SELECT sub.* ,
					goal.match_attribute as goal_match_attribute,
					goal.name as goal_name,
					goal.revenue as goal_revenue,
					count(*) as count_goal_conversions,
					log_conversion.idlink_va as idlink_va,
					log_conversion.server_time as goal_server_time
			FROM (
					SELECT 	*
					FROM " . Piwik_Common::prefixTable('log_visit') . " AS log_visit
					$sqlWhere
					GROUP BY idvisit
					ORDER BY idsite, visit_last_action_time DESC
					LIMIT ".(int)$filter_limit."
				) AS sub
				LEFT JOIN ".Piwik_Common::prefixTable('log_conversion')." AS log_conversion
					ON sub.idvisit = log_conversion.idvisit
				LEFT JOIN ".Piwik_Common::prefixTable('goal')." AS goal 
					ON (goal.idsite = sub.idsite
						AND  
						goal.idgoal = log_conversion.idgoal)
					AND goal.deleted = 0
				GROUP BY sub.idvisit
			"; 
		try {
			$data = Piwik_FetchAll($sql, $whereBind);
		} catch(Exception $e) {
			echo $e->getMessage();exit;
		}
		
//var_dump($whereBind);	echo($sql);//var_dump($data);
		return $data;
	}

	/**
	 * Load last Visitors PAGES or DETAILS in MINUTES or DAYS from database
	 *
	 * @param int $idSite
	 * @param int $minutes
	 * @param int $days
	 * @param int $type self::TYPE_FETCH_VISITS or self::TYPE_FETCH_PAGEVIEWS
	 *
	 * @return mixed
	 */
	private function loadLastVisitorInLastXTimeFromDatabase($idSite, $minutes = 0, $days = 0, $type = false )
	{
		$where = $whereBind = array();

		$where[] = " " . Piwik_Common::prefixTable('log_visit') . ".idsite = ? ";
		$whereBind[] = $idSite;

		if($minutes != 0)
		{
			$timeLimit = mktime(date('H'), date('i') - $minutes, 0, date('m'),  date('d'), date('Y'));
			$where[] = " visit_last_action_time > ?";
			$whereBind[] = date('Y-m-d H:i:s', $timeLimit);
		}

		if($days != 0)
		{
			$timeLimit = mktime(date('H'), date('i'), 0, date('m'), date('d') - $days, date('Y'));
			$where[] = " visit_last_action_time > ?";
			$whereBind[] = date('Y-m-d H:i:s', $timeLimit);
		}

		$sqlWhere = "";
		if(count($where) > 0)
		{
			$sqlWhere = " WHERE " . join(' AND ', $where);
		}

		// Details
		if($type == self::TYPE_FETCH_VISITS)
		{
			$sql = "SELECT 	" . Piwik_Common::prefixTable('log_visit') . ".idvisit
				FROM " . Piwik_Common::prefixTable('log_visit') . "
				$sqlWhere
				ORDER BY idvisit DESC";
		}
		// Pages
		elseif($type == self::TYPE_FETCH_PAGEVIEWS)
		{
			$sql = "SELECT " . Piwik_Common::prefixTable('log_link_visit_action') . ".idaction_url
					FROM " . Piwik_Common::prefixTable('log_link_visit_action') . "
    					INNER JOIN " . Piwik_Common::prefixTable('log_visit') . "
    					ON " . Piwik_Common::prefixTable('log_visit') . ".idvisit = " . Piwik_Common::prefixTable('log_link_visit_action') . ".idvisit
    					$sqlWhere";
		}
		else
		{
			// no $type is set --> ERROR
			throw new Exception("type parameter is not properly set.");
		}

		// return $sql by fetching
		return Piwik_FetchAll($sql, $whereBind);
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
