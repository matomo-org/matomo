<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 * 
 * @category Piwik_Plugins
 * @package Piwik_VisitTime
 */

/**
 * VisitTime API lets you access reports by Hour (Server time), and by Hour Local Time of your visitors.
 * 
 * @package Piwik_VisitTime
 */
class Piwik_VisitTime_API
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
	
	protected function getDataTable($name, $idSite, $period, $date, $segment )
	{
		Piwik::checkUserHasViewAccess( $idSite );
		$archive = Piwik_Archive::build($idSite, $period, $date, $segment );
		$dataTable = $archive->getDataTable($name);
		$dataTable->filter('Sort', array('label', 'asc', true));
		$dataTable->queueFilter('ColumnCallbackReplace', array('label', 'Piwik_getTimeLabel'));
		$dataTable->queueFilter('ReplaceColumnNames');
		return $dataTable;
	}
	
	public function getVisitInformationPerLocalTime( $idSite, $period, $date, $segment = false )
	{
		return $this->getDataTable('VisitTime_localTime', $idSite, $period, $date, $segment );
	}
	
	public function getVisitInformationPerServerTime( $idSite, $period, $date, $segment = false, $hideFutureHoursWhenToday = false )
	{
		$table = $this->getDataTable('VisitTime_serverTime', $idSite, $period, $date, $segment );
		
		if($hideFutureHoursWhenToday)
		{
			$table = $this->removeHoursInFuture($table, $idSite, $period, $date);
		}
		return $table;
	}
	
	protected function removeHoursInFuture($table, $idSite, $period, $date)
	{
		$site = new Piwik_Site($idSite);
		if(	$period == 'day'
			&& ($date == 'today'
				||  $date == Piwik_Date::factory('now', $site->getTimezone())->toString()))
		{
			$currentHour = Piwik_Date::factory('now', $site->getTimezone())->toString('G');
			$idsToDelete = array();
			foreach($table->getRows() as $id => $row)
			{
				$hour = $row->getColumn('label');
				if($hour > $currentHour)
				{
					$idsToDelete[] = $id;
				}
			}
			$table->deleteRows($idsToDelete);
		}
		return $table;
	}
}

function Piwik_getTimeLabel($label)
{
	return sprintf(Piwik_Translate('VisitTime_NHour'), $label);
}
