<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 *
 * @package Piwik_Live
 */

require_once "Live/Visitor.php";

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
		$visitorDetails = self::loadLastVisitorDetailsFromDatabase($visitorId, $idSite, $limit);
		$table = self::getCleanedVisitorsFromDetails($visitorDetails);
		return $table;
	}

	/*
	 * @return Piwik_DataTable
	 */
	public function getLastVisits( $idSite = false, $limit = 10, $minIdVisit = false )
	{
		if(is_null($idSite))
		{
			Piwik::checkUserIsSuperUser();
		}
		else
		{
			Piwik::checkUserHasViewAccess($idSite);
		}
		$visitorDetails = self::loadLastVisitorDetailsFromDatabase(null, $idSite, $limit, $minIdVisit);
		$table = self::getCleanedVisitorsFromDetails($visitorDetails);
		return $table;
	}
	
	/*
	 * @return Piwik_DataTable
	 */
	static private function getCleanedVisitorsFromDetails($visitorDetails)
	{
		$table = new Piwik_DataTable();
		foreach($visitorDetails as $visitorDetail)
		{
			self::cleanVisitorDetails($visitorDetail);
			$visitor = new Piwik_Live_Visitor($visitorDetail);
			$visitorDetailsArray = $visitor->getAllVisitorDetails();
			$dateTimeVisit = Piwik_Date::factory($visitorDetailsArray['firstActionTimestamp']);
			//TODO TO FIX
			$visitorDetailsArray['serverDatePretty'] = $dateTimeVisit->getLocalized('%shortDay% %day% %shortMonth%');
			$visitorDetailsArray['serverTimePretty'] = $dateTimeVisit->getLocalized('%time%');
			$table->addRowFromArray( array(Piwik_DataTable_Row::COLUMNS => $visitorDetailsArray));
		}
		return $table;
	}
	
	/*
	 * @return array
	 */
	private function loadLastVisitorDetailsFromDatabase($visitorId = null, $idSite = null, $limit = null, $minIdVisit = false )
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
		
		$sqlWhere = "";
		if(count($where) > 0)
		{
			$sqlWhere = " WHERE " . join(' AND ', $where);
		}
		
		$sql = "SELECT 	*
				FROM " . Piwik::prefixTable('log_visit') . "
				$sqlWhere 
				ORDER BY idvisit DESC
				LIMIT $limit";
				
		return Piwik_FetchAll($sql, $whereBind);
	}

	/*
	 * @return void
	 */
	static private function cleanVisitorDetails( &$visitorDetails )
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
