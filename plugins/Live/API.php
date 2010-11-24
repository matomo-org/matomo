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
		return $this->getLastVisitsForVisitor($visitorId, $idSite, $limit = 1);
	}

	/*
	 * @return Piwik_DataTable
	 */
	public function getLastVisitsForVisitor( $visitorId, $idSite, $limit = 10 )
	{
		Piwik::checkUserHasViewAccess($idSite);
		$visitorDetails = $this->loadLastVisitorDetailsFromDatabase($idSite, $period = false, $date = false, $limit, $offset = false, $minIdVisit = false, $visitorId);
		$table = $this->getCleanedVisitorsFromDetails($visitorDetails, $idSite);
		return $table;
	}

	/*
	 * @return Piwik_DataTable
	 */
	public function getLastVisits( $idSite, $limit = 10, $minIdVisit = false )
	{
		Piwik::checkUserHasViewAccess($idSite);
		$visitorDetails = $this->loadLastVisitorDetailsFromDatabase($idSite, $period = false, $date = false, $limit, $offset = false, $minIdVisit, $visitorId = false);
		$table = $this->getCleanedVisitorsFromDetails($visitorDetails, $idSite);
		return $table;
	}

	/*
	 * @return Piwik_DataTable
	 */
	public function getLastVisitsDetails( $idSite, $period = false, $date = false, $limit = 25, $filter_offset = 0, $minIdVisit = false )
	{
		Piwik::checkUserHasViewAccess($idSite);
		$visitorDetails = $this->loadLastVisitorDetailsFromDatabase($idSite, $period, $date, $limit, $filter_offset, $minIdVisit); 
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

			$dateTimeVisit = Piwik_Date::factory($visitorDetailsArray['firstActionTimestamp'], $timezone);
			$visitorDetailsArray['serverDatePretty'] = $dateTimeVisit->getLocalized('%shortDay% %day% %shortMonth%');
			$visitorDetailsArray['serverTimePretty'] = $dateTimeVisit->getLocalized('%time%');
			$visitorDetailsArray['goalConversions'] = $visitorDetail['count_goal_conversions'];
			if(!empty($visitorDetailsArray['goalTimePretty']))
			{
				$dateTimeConversion = Piwik_Date::factory($visitorDetailsArray['goalTimePretty'], $timezone);
				$visitorDetailsArray['goalTimePretty'] = $dateTimeConversion->getLocalized('%shortDay% %day% %shortMonth% %time%');
			}
			
			$idvisit = $visitorDetailsArray['idVisit'];

			$sql = "
				SELECT
				" .Piwik_Common::prefixTable('log_action').".name AS pageUrl,
				" .Piwik_Common::prefixTable('log_action').".idaction AS pageIdAction
				FROM " .Piwik_Common::prefixTable('log_link_visit_action')."
					INNER JOIN " .Piwik_Common::prefixTable('log_action')."
					ON  " .Piwik_Common::prefixTable('log_link_visit_action').".idaction_url = " .Piwik_Common::prefixTable('log_action').".idaction
				WHERE " .Piwik_Common::prefixTable('log_link_visit_action').".idvisit = $idvisit;
				 ";

			$visitorDetailsArray['actionDetails'] = Piwik_FetchAll($sql);

			$sql = "
				SELECT
				" .Piwik_Common::prefixTable('log_action').".name AS pageTitle,
				" .Piwik_Common::prefixTable('log_action').".idaction AS pageIdAction
				FROM " .Piwik_Common::prefixTable('log_link_visit_action')."
					INNER JOIN " .Piwik_Common::prefixTable('log_action')."
					ON  " .Piwik_Common::prefixTable('log_link_visit_action').".idaction_name = " .Piwik_Common::prefixTable('log_action').".idaction
				WHERE " .Piwik_Common::prefixTable('log_link_visit_action').".idvisit = $idvisit;
				 ";

			$visitorDetailsArray['actionDetailsTitle'] = Piwik_FetchAll($sql);
			
			$table->addRowFromArray( array(Piwik_DataTable_Row::COLUMNS => $visitorDetailsArray));
		}

		return $table;
	}

	/*
	 * @return array
	 */
	private function loadLastVisitorDetailsFromDatabase($idSite, $period = false, $date = false, $limit = false, $offset = false, $minIdVisit = false, $visitorId = false)
	{
		$where = $whereBind = array();

		$where[] = Piwik_Common::prefixTable('log_visit') . ".idsite = ? ";
		$whereBind[] = $idSite;
		
		if(!empty($visitorId))
		{
			$where[] = Piwik_Common::prefixTable('log_visit') . ".visitor_idcookie = ? ";
			$whereBind[] = $visitorId;
		}

		if(!empty($minIdVisit))
		{
			$where[] = Piwik_Common::prefixTable('log_visit') . ".idvisit > ? ";
			$whereBind[] = $minIdVisit;
		}

		// increase limit by offset when visitor paginates
		if(!empty($offset))
		{
			$limit += (int)$offset;
		}
		
		// SQL Filter with provided period
		if (!empty($period) && !empty($date))
		{

			$currentSite = new Piwik_Site($idSite);
			$currentTimezone = $currentSite->getTimezone();

			if($date == 'now' || $date == 'today')
			{
				$date = date('Y-m-d', Piwik_Date::factory('now', $currentTimezone)->getTimestamp());
			}
			elseif($date == 'yesterday' || $date == 'yesterdaySameTime')
			{
				$date = date('Y-m-d', Piwik_Date::factory('now', $currentTimezone)->subDay(1)->getTimestamp());
			}
			$processedDate = Piwik_Date::factory($date, $currentTimezone);
			$processedPeriod = Piwik_Period::factory($period, $processedDate);

			array_push(     $where, Piwik_Common::prefixTable('log_visit') . ".visit_first_action_time BETWEEN ? AND ?");
			array_push(     $whereBind,
				$processedPeriod->getDateStart()->toString(),
				$processedPeriod->getDateEnd()->addDay(1)->toString()
			);
		}

		$sqlWhere = "";
		if(count($where) > 0)
		{
			$sqlWhere = " WHERE " . join(' AND ', $where);
		}

		// Group by idvisit so that a visitor converting 2 goals only appears twice
		$sql = "SELECT 	" . Piwik_Common::prefixTable('log_visit') . ".* ,
						" . Piwik_Common::prefixTable ( 'goal' ) . ".match_attribute as goal_match_attribute,
						" . Piwik_Common::prefixTable ( 'goal' ) . ".name as goal_name,
						" . Piwik_Common::prefixTable ( 'goal' ) . ".revenue as goal_revenue,
						" . Piwik_Common::prefixTable ( 'log_conversion' ) . ".idaction_url as goal_idaction_url,
						" . Piwik_Common::prefixTable ( 'log_conversion' ) . ".server_time as goal_server_time,
						count(*) as count_goal_conversions
				FROM " . Piwik_Common::prefixTable('log_visit') . "
					LEFT JOIN ".Piwik_Common::prefixTable('log_conversion')."
					ON " . Piwik_Common::prefixTable('log_visit') . ".idvisit = " . Piwik_Common::prefixTable('log_conversion') . ".idvisit
					LEFT JOIN ".Piwik_Common::prefixTable('goal')."
					ON (" . Piwik_Common::prefixTable('goal') . ".idsite = " . Piwik_Common::prefixTable('log_visit') . ".idsite
						AND  " . Piwik_Common::prefixTable('goal') . ".idgoal = " . Piwik_Common::prefixTable('log_conversion') . ".idgoal)
					AND " . Piwik_Common::prefixTable('goal') . ".deleted = 0
					$sqlWhere
				GROUP BY idvisit
				ORDER BY idvisit DESC
				LIMIT ".(int)$limit;
		return Piwik_FetchAll($sql, $whereBind);
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
			$timeLimit = mktime(date("H"), date("i") - $minutes, 0, date("m"),   date("d"),   date("Y"));
			$where[] = " visit_last_action_time > ?";
			$whereBind[] = date('Y-m-d H:i:s',$timeLimit);
		}

		if($days != 0)
		{
			$oSite = new Piwik_Site($idSite);
			$sTimezone = $oSite->getTimezone();

			$oDate = Piwik_Date::factory("now", $sTimezone)
				->setTimezone($sTimezone)
				->subDay($days - 1);
			$where[] = " visit_last_action_time > ?";
			$whereBind[] = $oDate->getDateStartUTC();

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
		$toUnset = array('config_md5config');
		if(!Piwik::isUserIsSuperUser()
			&& !Piwik::isUserHasAdminAccess($idSite))
		{
			$toUnset[] = 'visitor_idcookie';
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
