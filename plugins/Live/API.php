<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
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
	/*
	 * @return Piwik_Live_API
	 */
	static public function getInstance()
	{
		if (self::$instance == null)
		{
			$c = __CLASS__;
			self::$instance = new $c();
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
		$visitorDetails = $this->loadLastVisitorDetailsFromDatabase($idSite, $visitorId, $limit);
		$table = $this->getCleanedVisitorsFromDetails($visitorDetails, $idSite);
		return $table;
	}

	/*
	 * @return Piwik_DataTable
	 */
	public function getLastVisits( $idSite, $limit = 10, $minIdVisit = false )
	{
		Piwik::checkUserHasViewAccess($idSite);
		$visitorDetails = $this->loadLastVisitorDetailsFromDatabase($idSite, $visitorId = null, $limit, $minIdVisit);
		$table = $this->getCleanedVisitorsFromDetails($visitorDetails, $idSite);
		return $table;
	}

	/*
	 * @return Piwik_DataTable
	 */
	public function getLastVisitsDetails( $idSite, $limit = 1000, $minIdVisit = false )
	{
		Piwik::checkUserHasViewAccess($idSite);
		$visitorDetails = $this->loadLastVisitorDetailsFromDatabase($idSite, $visitorId = null, $limit, $minIdVisit);
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

		foreach($visitorDetails as $visitorDetail)
		{
			$this->cleanVisitorDetails($visitorDetail);
			$visitor = new Piwik_Live_Visitor($visitorDetail);
			$visitorDetailsArray = $visitor->getAllVisitorDetails();

			$site = new Piwik_Site($idSite);
			$timezone = $site->getTimezone();
			$dateTimeVisit = Piwik_Date::factory($visitorDetailsArray['firstActionTimestamp'], $timezone);
			$visitorDetailsArray['serverDatePretty'] = $dateTimeVisit->getLocalized('%shortDay% %day% %shortMonth%');
			$visitorDetailsArray['serverTimePretty'] = $dateTimeVisit->getLocalized('%time%');

			// get Detail - 100 single SQL Statements - Performance Issue
			$idvisit = $visitorDetailsArray['idVisit'];

			$sql = "
				SELECT DISTINCT " .Piwik_Common::prefixTable('log_action').".name AS pageUrl
				FROM " .Piwik_Common::prefixTable('log_link_visit_action')."
					INNER JOIN " .Piwik_Common::prefixTable('log_action')." 
					ON  " .Piwik_Common::prefixTable('log_link_visit_action').".idaction_url = " .Piwik_Common::prefixTable('log_action').".idaction
				WHERE " .Piwik_Common::prefixTable('log_link_visit_action').".idvisit = $idvisit;
				 ";

			$visitorDetailsArray['actionDetails'] = Piwik_FetchAll($sql);

			$sql = "
				SELECT DISTINCT " .Piwik_Common::prefixTable('log_action').".name AS pageUrl
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
	private function loadLastVisitorDetailsFromDatabase($idSite, $visitorId = null, $limit = null, $minIdVisit = false )
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

		$sqlWhere = "";
		if(count($where) > 0)
		{
			$sqlWhere = " WHERE " . join(' AND ', $where);
		}

		$sql = "SELECT 	" . Piwik_Common::prefixTable('log_visit') . ".* , 
						" . Piwik_Common::prefixTable ( 'goal' ) . ".match_attribute
				FROM " . Piwik_Common::prefixTable('log_visit') . "
					LEFT JOIN ".Piwik_Common::prefixTable('log_conversion')." 
					ON " . Piwik_Common::prefixTable('log_visit') . ".idvisit = " . Piwik_Common::prefixTable('log_conversion') . ".idvisit
					LEFT JOIN ".Piwik_Common::prefixTable('goal')." 
					ON (" . Piwik_Common::prefixTable('goal') . ".idsite = " . Piwik_Common::prefixTable('log_visit') . ".idsite
						AND  " . Piwik_Common::prefixTable('goal') . ".idgoal = " . Piwik_Common::prefixTable('log_conversion') . ".idgoal)
					AND " . Piwik_Common::prefixTable('goal') . ".deleted = 0
				$sqlWhere
				ORDER BY idsite,idvisit DESC
				LIMIT $limit";

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
			$where[] = " visit_last_action_time > '".date('Y-m-d H:i:s',$timeLimit)."'";
		}

		if($days != 0)
		{
			$timeLimit = mktime(0, 0, 0, date("m"),   date("d") - $days + 1,   date("Y"));
			$where[] = " visit_last_action_time > '".date('Y-m-d H:i:s', $timeLimit)."'";
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
				ORDER BY idsite,idvisit DESC";
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
	 * Or that the user should only access if he is super user (cookie, IP)
	 * 
	 * @return void
	 */
	private function cleanVisitorDetails( &$visitorDetails )
	{
		$toUnset = array('config_md5config');
		if(!Piwik::isUserIsSuperUser())
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
