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

// no direct access
defined('PIWIK_INCLUDE_PATH') or die;

/**
 * @see plugins/Referers/functions.php
 */
require_once PIWIK_INCLUDE_PATH . '/plugins/Live/Visitor.php';

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

	/*
	 * @return Piwik_DataTable
	 */
	public function getLastVisitForVisitor( $visitorId, $idSite = null )
	{
		return $this->getLastVisitsForVisitor($visitorId, $idSite, 1);
	}

	/*
	 * @return Piwik_DataTable
	 */
	public function getLastVisitsForVisitor( $visitorId, $idSite, $limit = 10 )
	{
		if(is_null($idSite))
		{
			Piwik::checkUserIsSuperUser();
		}
		else
		{
			Piwik::checkUserHasViewAccess($idSite);
		}
		$visitorDetails = $this->loadLastVisitorDetailsFromDatabase($visitorId, $idSite, $limit);
		$table = $this->getCleanedVisitorsFromDetails($visitorDetails);

		return $table;
	}

	/*
	 * @return Piwik_DataTable
	 */
	public function getLastVisits( $idSite = false, $limit = 10, $minIdVisit = false )
	{
		// for checking given vars
		// echo $idSite.'|'.$limit.'|'.$minIdVisit.'<br>';
		if(is_null($idSite))
		{
			Piwik::checkUserIsSuperUser();
		}
		else
		{
			Piwik::checkUserHasViewAccess($idSite);
		}
		$visitorDetails = $this->loadLastVisitorDetailsFromDatabase(null, $idSite, $limit, $minIdVisit);
		$table = $this->getCleanedVisitorsFromDetails($visitorDetails);

		return $table;
	}

	/*
	 * @return Piwik_DataTable
	 */
	public function getLastVisitsDetails( $idSite = false, $limit = 1000, $minIdVisit = false )
	{
		// for checking given vars
		if(is_null($idSite))
		{
			Piwik::checkUserIsSuperUser();
		}
		else
		{
			Piwik::checkUserHasViewAccess($idSite);
		}
		$visitorDetails = $this->loadLastVisitorDetailsFromDatabase(null, $idSite, $limit, $minIdVisit);
		$dataTable = $this->getCleanedVisitorsFromDetails($visitorDetails);
//		$dataTable->queueFilter('ColumnCallbackAddMetadata', array('operatingSystem', 'icon', 'Piwik_Live_Visitor::getVisitLength()'));

		return $dataTable;
	}


	/*
	 * @return Piwik_DataTable
	 */
	private function getCleanedVisitorsFromDetails($visitorDetails)
	{
		$table = new Piwik_DataTable();

		foreach($visitorDetails as $visitorDetail)
		{
			$this->cleanVisitorDetails($visitorDetail);
			$visitor = new Piwik_Live_Visitor($visitorDetail);

			// $visitorDetail must contain the match_atribute
			$visitorDetailsArray = $visitor->getAllVisitorDetails();

//			$visitorDetailsArray['goalIcon'] = "themes/default/images/goal.png";

			$dateTimeVisit = Piwik_Date::factory($visitorDetailsArray['firstActionTimestamp']);
			//TODO TO FIX
			$visitorDetailsArray['serverDatePretty'] = $dateTimeVisit->getLocalized('%shortDay% %day% %shortMonth%');
			$visitorDetailsArray['serverTimePretty'] = $dateTimeVisit->getLocalized('%time%');

			// get Detail - 100 single SQL Statements - Performance Issue
			$idvisit = $visitorDetailsArray['idVisit'];

			$sql = "SELECT DISTINCT `" .Piwik::prefixTable('log_action')."`.`name` AS pageUrl
				FROM `" .Piwik::prefixTable('log_link_visit_action')."`
					INNER JOIN `" .Piwik::prefixTable('log_action')."` ON  `" .Piwik::prefixTable('log_link_visit_action')."`.`idaction_url` = `" .Piwik::prefixTable('log_action')."`.`idaction`
				WHERE `" .Piwik::prefixTable('log_link_visit_action')."`.`idvisit` = $idvisit;
				 ";

			$visitorDetailsArray['actionDetails'] = Piwik_FetchAll($sql);

			$sql = "SELECT DISTINCT `" .Piwik::prefixTable('log_action')."`.`name` AS pageUrl
				FROM `" .Piwik::prefixTable('log_link_visit_action')."`
					INNER JOIN `" .Piwik::prefixTable('log_action')."` ON  `" .Piwik::prefixTable('log_link_visit_action')."`.`idaction_name` = `" .Piwik::prefixTable('log_action')."`.`idaction`
				WHERE `" .Piwik::prefixTable('log_link_visit_action')."`.`idvisit` = $idvisit;
				 ";

			$visitorDetailsArray['actionDetailsTitle'] = Piwik_FetchAll($sql);

			$table->addRowFromArray( array(Piwik_DataTable_Row::COLUMNS => $visitorDetailsArray));
		}

		return $table;
	}

	/*
	 * @return array
	 */
	private function loadLastVisitorDetailsFromDatabase($visitorId = null, $idSite = null, $limit = null, $minIdVisit = false )
	{
		// for checking given vars
		// echo $visitorId.'|'.$idSite.'|'.$limit.'|'.$minIdVisit.'<br>';
		$where = $whereBind = array();

		if(!is_null($idSite))
		{
			$where[] = Piwik::prefixTable('log_visit') . ".idsite = ? ";
			$whereBind[] = $idSite;
		}

		if(!is_null($visitorId))
		{
			$where[] = Piwik::prefixTable('log_visit') . ".visitor_idcookie = ? ";
			$whereBind[] = $visitorId;
		}

		if(!is_null($minIdVisit))
		{
			$where[] = Piwik::prefixTable('log_visit') . ".idvisit > ? ";
			$whereBind[] = $minIdVisit;
		}

		$sqlWhere = "";
		if(count($where) > 0)
		{
			$sqlWhere = " WHERE " . join(' AND ', $where);
		}

		$sql = "SELECT 	" . Piwik::prefixTable('log_visit') . ".* , " . Piwik::prefixTable ( 'goal' ) . ".`match_attribute`
				FROM " . Piwik::prefixTable('log_visit') . "
				LEFT JOIN ".Piwik::prefixTable('log_conversion')." ON " . Piwik::prefixTable('log_visit') . ".`idvisit` = " . Piwik::prefixTable('log_conversion') . ".`idvisit`
				LEFT JOIN ".Piwik::prefixTable('goal')." ON " . Piwik::prefixTable('goal') . ".`idgoal` = " . Piwik::prefixTable('log_conversion') . ".`idgoal` AND " . Piwik::prefixTable('goal') . ".`deleted` = 0
				$sqlWhere
				ORDER BY idvisit DESC
				LIMIT $limit";

		return Piwik_FetchAll($sql, $whereBind);
	}

	/*
	 *
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



	/*
	 * @return Piwik_DataTable
	 */
	public function getUsersInLastXMin( $idSite = false, $limit = 10, $minIdVisit = false, $minutes = 30 )
	{
		if(is_null($idSite))
		{
			Piwik::checkUserIsSuperUser();
		}
		else
		{
			Piwik::checkUserHasViewAccess($idSite);
		}
//		$visitorDetails = $this->loadLastVisitorDetailsInLastXMinFromDatabase(null, $idSite, $limit, $minIdVisit, $minutes);
		$visitorDetails = $this->loadLastVisitorInLastXTimeFromDatabase(null, $idSite, $limit, $minIdVisit, $minutes, 0, 1);

		$table = $this->getCleanedVisitorsFromDetails($visitorDetails);

		return $table;
	}

	/*
	 * @return Piwik_DataTable
	 */
	public function getUsersInLastXDays( $idSite = false, $limit = 10, $minIdVisit = false, $days = 10 )
	{

		if(is_null($idSite))
		{
			Piwik::checkUserIsSuperUser();
		}
		else
		{
			Piwik::checkUserHasViewAccess($idSite);
		}
//		$visitorDetails = $this->loadLastVisitorDetailsInLastXDaysFromDatabase(null, $idSite, $limit, $minIdVisit, $days);
		$visitorDetails = $this->loadLastVisitorInLastXTimeFromDatabase(null, $idSite, $limit, $minIdVisit, 0, $days, 1);

		$table = $this->getCleanedVisitorsFromDetails($visitorDetails);

		return $table;
	}

	/*
	 * @return array
	 */
	public function getPageImpressionsInLastXDays($idSite = false, $limit = 10, $minIdVisit = false, $days = 10){
		// for checking given vars
		#echo $idSite.'|'.$limit.'|'.$minIdVisit.'|'.$days.'<br>';

		if(is_null($idSite))
		{
			Piwik::checkUserIsSuperUser();
		}
		else
		{
			Piwik::checkUserHasViewAccess($idSite);
		}
//		$pageDetails = $this->loadLastVisitedPagesInLastXDaysFromDatabase(null, $idSite, $limit, $minIdVisit, $days);
		$pageDetails = $this->loadLastVisitorInLastXTimeFromDatabase(null, $idSite, $limit, $minIdVisit, 0, $days, 2);

		$i = -1;
		foreach ($pageDetails as $detail) {
			$i++;
			if(strlen($pageDetails[$i]['name']) > 30) {
				$pageDetails[$i]['name']  = substr($pageDetails[$i]['name'] , 0, 30 - 3).'...';
			}
		}

		return $pageDetails;
	}

	/*
	 * @return array
	 */
	public function getPageImpressionsInLastXMin($idSite = false, $limit = 10, $minIdVisit = false, $minutes = 30){

		if(is_null($idSite))
		{
			Piwik::checkUserIsSuperUser();
		}
		else
		{
			Piwik::checkUserHasViewAccess($idSite);
		}
//		$pageDetails = $this->loadLastVisitedPagesInLastXMinFromDatabase(null, $idSite, $limit, $minIdVisit, $minutes);
		$pageDetails = $this->loadLastVisitorInLastXTimeFromDatabase(null, $idSite, $limit, $minIdVisit, $minutes, 0, 2);

		$i = -1;
		foreach ($pageDetails as $detail) {
			$i++;
			if(strlen($pageDetails[$i]['name']) > 30) {
				$pageDetails[$i]['name']  = substr($pageDetails[$i]['name'] , 0, 30 - 3).'...';
			}
		}
		return $pageDetails;
	}


	/**
	 * Load last Visitors PAGES or DETAILS in MINUTES or DAYS from database
	 *
	 * @param boolen $visitorId
	 * @param boolen $idSite
	 * @param int $limit int
	 * @param int $minIdVisit
	 * @param int $minutes
	 * @param int $days
	 * @param int $type 1 = DETAILS; 2 = PAGES
	 *
	 * @return mixed
	 */
	private function loadLastVisitorInLastXTimeFromDatabase($visitorId = null, $idSite = null, $limit = 1000, $minIdVisit = false, $minutes = 0, $days = 0, $type = 0 )
	{
		$where = $whereBind = array();

		if(!is_null($idSite))
		{
			$where[] = " " . Piwik::prefixTable('log_visit') . ".`idsite` = ? ";
			$whereBind[] = $idSite;
		}

		if(!is_null($visitorId))
		{
			$where[] = " `visitor_idcookie` = ? ";
			$whereBind[] = $visitorId;
		}

		if(!is_null($minIdVisit))
		{
			$where[] = " " . Piwik::prefixTable('log_visit') . ".`idvisit` > ? ";
			$whereBind[] = $minIdVisit;
		}

		if($minutes != 0)
		{
			$timeLimit = mktime(date("H"), date("i") - $minutes, 0, date("m"),   date("d"),   date("Y"));
			$where[] = " `visit_last_action_time` > '".date('Y-m-d H:i:s',$timeLimit)."'";
		}

		if($days != 0)
		{
			$timeLimit = mktime(0, 0, 0, date("m"),   date("d") - $days + 1,   date("Y"));
			$where[] = " `visit_last_action_time` > '".date('Y-m-d H:i:s',$timeLimit)."'";
		}

		$sqlWhere = "";
		if(count($where) > 0)
		{
			$sqlWhere = " WHERE " . join(' AND ', $where);
		}

		// Details
		if($type == 1)
		{
			$sql = "SELECT 	" . Piwik::prefixTable('log_visit') . ".*
				FROM " . Piwik::prefixTable('log_visit') . "
				$sqlWhere
				ORDER BY idvisit DESC
				LIMIT " . (int)$limit;
		 }
		 // Pages
		 elseif($type == 2)
		 {
		 	// different SELECT between $minutes & $days
		 	if($minutes != 0)
		 	{
		 		$sql_select = "SELECT " . Piwik::prefixTable('log_link_visit_action') . ".`idaction_url`, " . Piwik::prefixTable('log_action') . ".`idaction`, " . Piwik::prefixTable('log_action') . ".`name` , " . Piwik::prefixTable('log_visit') . ".*";
			}
			elseif($days != 0)
			{
			    $sql_select = "SELECT " . Piwik::prefixTable('log_link_visit_action') . ".`idaction_url`, " . Piwik::prefixTable('log_action') . ".`idaction`, " . Piwik::prefixTable('log_action') . ".`name` , " . Piwik::prefixTable('log_link_visit_action') . ".*";
			}
			else
			{
				// neither $minutes nor $days --> ERROR
				return false;
			}

			$sql = $sql_select."
				FROM " . Piwik::prefixTable('log_link_visit_action') . "
				INNER JOIN " . Piwik::prefixTable('log_action') . " ON " . Piwik::prefixTable('log_link_visit_action') . ".`idaction_url`= " . Piwik::prefixTable('log_action') . ".`idaction`
				INNER JOIN " . Piwik::prefixTable('log_visit') . " ON " . Piwik::prefixTable('log_visit') . ".`idvisit` = " . Piwik::prefixTable('log_link_visit_action') . ".`idvisit`
				$sqlWhere";
		 }
		 else
		 {
		 	// no $type is set --> ERROR
		 	return false;
		 }

		// return $sql by fetching
		return Piwik_FetchAll($sql, $whereBind);
	}
}
