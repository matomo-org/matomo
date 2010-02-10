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
//		var_dump($table);
		return $table;
	}

	/*
	 * @return Piwik_DataTable
	 */
	public function getLastVisitsDetails( $idSite = false, $limit = 1000, $minIdVisit = false )
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
		$dataTable = $this->getCleanedVisitorsFromDetails($visitorDetails);
//echo "hallo";
//
//		$dataTable->queueFilter('ColumnCallbackAddMetadata', array('operatingSystem', 'icon', 'Piwik_Live_Visitor::getVisitLength()'));
		
// echo "<pre>";
// var_dump($dataTable[0]);
// echo "</pre>";		
		
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
			$visitorDetailsArray = $visitor->getAllVisitorDetails();
			$dateTimeVisit = Piwik_Date::factory($visitorDetailsArray['firstActionTimestamp']);
			//TODO TO FIX
			$visitorDetailsArray['serverDatePretty'] = $dateTimeVisit->getLocalized('%shortDay% %day% %shortMonth%');
			$visitorDetailsArray['serverTimePretty'] = $dateTimeVisit->getLocalized('%time%');
			
			// get Detail
			$idvisit = $visitorDetailsArray['idVisit'];
			$sql = "SELECT DISTINCT`" .Piwik::prefixTable('log_action')."`.`name` AS pageUrl
				FROM `" .Piwik::prefixTable('log_visit')."`
					INNER JOIN `" .Piwik::prefixTable('log_link_visit_action')."` ON `" .Piwik::prefixTable('log_visit')."`.`idvisit` =  `" .Piwik::prefixTable('log_link_visit_action')."`.`idvisit`
					INNER JOIN `" .Piwik::prefixTable('log_action')."` ON  `" .Piwik::prefixTable('log_link_visit_action')."`.`idaction_url` = `" .Piwik::prefixTable('log_action')."`.`idaction`
				WHERE `" .Piwik::prefixTable('log_visit')."`.`idvisit` = $idvisit;
				 ";
			
			$visitorDetailsArray['actionDetails'] = Piwik_FetchAll($sql);
			
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
			$where[] = " idsite = ? ";
			$whereBind[] = $idSite;
		}
		
		if(!is_null($visitorId))
		{
			$where[] = " visitor_idcookie = ? ";
			$whereBind[] = $visitorId;
		}
		
		if(!is_null($minIdVisit))
		{
			$where[] = " idvisit > ? ";
			$whereBind[] = $minIdVisit;
		}
		
		$sqlWhere = "";
		if(count($where) > 0)
		{
			$sqlWhere = " WHERE " . join(' AND ', $where);
		}
		
		$sql = "SELECT *
				FROM " . Piwik::prefixTable('log_visit') . "
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
		$visitorDetails = $this->loadLastVisitorDetailsInLastXMinFromDatabase(null, $idSite, $limit, $minIdVisit, $minutes);
		
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
		$visitorDetails = $this->loadLastVisitorDetailsInLastXDaysFromDatabase(null, $idSite, $limit, $minIdVisit, $days);
		
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
		$pageDetails = $this->loadLastVisitedPagesInLastXDaysFromDatabase(null, $idSite, $limit, $minIdVisit, $days);
		
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
		$pageDetails = $this->loadLastVisitedPagesInLastXMinFromDatabase(null, $idSite, $limit, $minIdVisit, $minutes);
		
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
	 * TODO should be refactored with function below
	 * @return array
	 */
	private function loadLastVisitorDetailsInLastXMinFromDatabase($visitorId = null, $idSite = null, $limit = 1000, $minIdVisit = false, $minutes = 0 )
	{
		$where = $whereBind = array();
		
		if(!is_null($idSite))
		{
			$where[] = " idsite = ? ";
			$whereBind[] = $idSite;
		}
		
		if(!is_null($visitorId))
		{
			$where[] = " visitor_idcookie = ? ";
			$whereBind[] = $visitorId;
		}
		
		if(!is_null($minIdVisit))
		{
			$where[] = " idvisit > ? ";
			$whereBind[] = $minIdVisit;
		}
		
		if($minutes != 0)
		{
			$timeLimit = mktime(date("H"), date("i") - $minutes, 0, date("m"),   date("d"),   date("Y"));

			$where[] = " visit_last_action_time > '".date('Y-m-d H:i:s',$timeLimit)."'";
		}
		
		$sqlWhere = "";
		if(count($where) > 0)
		{
			$sqlWhere = " WHERE " . join(' AND ', $where);
		}
		
		$sql = "SELECT 	*
				FROM " . Piwik::prefixTable('log_visit') . "
				$sqlWhere 
				ORDER BY idvisit DESC
				LIMIT " . (int)$limit;
		return Piwik_FetchAll($sql, $whereBind);
	}
	
	/*
	 * TODO should be refactored with function above
	 * @return array
	 */
	private function loadLastVisitorDetailsInLastXDaysFromDatabase($visitorId = null, $idSite = null, $limit = 1000, $minIdVisit = false, $days = 0 )
	{
		$where = $whereBind = array();
		
		if(!is_null($idSite))
		{
			$where[] = " idsite = ? ";
			$whereBind[] = $idSite;
		}
		
		if(!is_null($visitorId))
		{
			$where[] = " visitor_idcookie = ? ";
			$whereBind[] = $visitorId;
		}
		
		if(!is_null($minIdVisit))
		{
			$where[] = " idvisit > ? ";
			$whereBind[] = $minIdVisit;
		}
		
		if($days != 0)
		{
			$timeLimit = mktime(0, 0, 0, date("m"),   date("d") - $days + 1,   date("Y"));

			$where[] = " visit_last_action_time > '".date('Y-m-d H:i:s',$timeLimit)."'";
		}
		
		$sqlWhere = "";
		if(count($where) > 0)
		{
			$sqlWhere = " WHERE " . join(' AND ', $where);
		}
		
		$sql = "SELECT 	*
				FROM " . Piwik::prefixTable('log_visit') . "
				$sqlWhere 
				ORDER BY idvisit DESC
				LIMIT " . (int)$limit;
		return Piwik_FetchAll($sql, $whereBind);
	}
	
	/*
	 * TODO should be refactored with function above
	 * @return array
	 */
	private function loadLastVisitedPagesInLastXMinFromDatabase($visitorId = null, $idSite = null, $limit = null, $minIdVisit = false, $minutes = 0 )
	{
		$where = $whereBind = array();
		
		if(!is_null($idSite))
		{
			$where[] = " idsite = ? ";
			$whereBind[] = $idSite;
		}
		
		if(!is_null($visitorId))
		{
			$where[] = " visitor_idcookie = ? ";
			$whereBind[] = $visitorId;
		}
		
		if(!is_null($minIdVisit))
		{
			$where[] = Piwik::prefixTable('log_visit') .".idvisit > ? ";
			$whereBind[] = $minIdVisit;
		}

		if($minutes != 0)
		{
			$timeLimit = mktime(date("H"), date("i") - $minutes, 0, date("m"),   date("d"),   date("Y"));

			$where[] = " visit_last_action_time > '".date('Y-m-d H:i:s',$timeLimit)."'";
		}
		
		$sqlWhere = "";
		if(count($where) > 0)
		{
			$sqlWhere = " WHERE " . join(' AND ', $where);
		}
		
		$sql = "SELECT " . Piwik::prefixTable('log_link_visit_action') . ".`idaction_url`," . Piwik::prefixTable('log_action') . ".`idaction`, " . Piwik::prefixTable('log_action') . ".`name` , " . Piwik::prefixTable('log_visit') . ".*
				FROM " . Piwik::prefixTable('log_link_visit_action') . "
				INNER JOIN " . Piwik::prefixTable('log_action') . " ON " . Piwik::prefixTable('log_link_visit_action') . ".`idaction_url`= " . Piwik::prefixTable('log_action') . ".`idaction` 
				INNER JOIN " . Piwik::prefixTable('log_visit') . " ON " . Piwik::prefixTable('log_visit') . ".`idvisit` = " . Piwik::prefixTable('log_link_visit_action') . ".`idvisit`
				$sqlWhere";
		return Piwik_FetchAll($sql, $whereBind);
	}	
		
	/*
	 * TODO should be refactored with function above
	 * @return array
	 */
	private function loadLastVisitedPagesInLastXDaysFromDatabase($visitorId = null, $idSite = null, $limit = null, $minIdVisit = false, $days = 0 )
	{
		$where = $whereBind = array();
		
		if(!is_null($idSite))
		{
			$where[] = " idsite = ? ";
			$whereBind[] = $idSite;
		}
		
		if(!is_null($visitorId))
		{
			$where[] = " visitor_idcookie = ? ";
			$whereBind[] = $visitorId;
		}
		
		if($days != 0)
		{
			$timeLimit = mktime(0, 0, 0, date("m"),   date("d") - $days + 1,   date("Y"));

			$where[] = " visit_last_action_time > '".date('Y-m-d H:i:s',$timeLimit)."'";
		}
		
		$sqlWhere = "";
		if(count($where) > 0)
		{
			$sqlWhere = " WHERE " . join(' AND ', $where);
		}
		
		$sql = "SELECT " . Piwik::prefixTable('log_link_visit_action') . ".`idaction_url`, " . Piwik::prefixTable('log_action') . ".`idaction`, " . Piwik::prefixTable('log_action') . ".`name` , " . Piwik::prefixTable('log_link_visit_action') . ".*
				FROM " . Piwik::prefixTable('log_link_visit_action') . "
				INNER JOIN " . Piwik::prefixTable('log_action') . " ON " . Piwik::prefixTable('log_link_visit_action') . ".`idaction_url`= " . Piwik::prefixTable('log_action') . ".`idaction` 
				INNER JOIN " . Piwik::prefixTable('log_visit') . " ON " . Piwik::prefixTable('log_visit') . ".idvisit=" . Piwik::prefixTable('log_link_visit_action') . ".idvisit
				$sqlWhere";

		return Piwik_FetchAll($sql, $whereBind);
	}	
}
