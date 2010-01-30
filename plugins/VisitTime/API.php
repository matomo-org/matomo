<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @category Piwik_Plugins
 * @package Piwik_VisitTime
 */

/**
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
			$c = __CLASS__;
			self::$instance = new $c();
		}
		return self::$instance;
	}
	
	static protected function getDataTable($name, $idSite, $period, $date )
	{
		Piwik::checkUserHasViewAccess( $idSite );
		$archive = Piwik_Archive::build($idSite, $period, $date );
		$dataTable = $archive->getDataTable($name);
		$dataTable->filter('Sort', array('label', 'asc', true));
		$dataTable->queueFilter('ColumnCallbackReplace', array('label', 'Piwik_getTimeLabel'));
		$dataTable->queueFilter('ReplaceColumnNames');
		return $dataTable;
	}
	
	static public function getVisitInformationPerLocalTime( $idSite, $period, $date )
	{
		return self::getDataTable('VisitTime_localTime', $idSite, $period, $date );
	}
	
	static public function getVisitInformationPerServerTime( $idSite, $period, $date )
	{
		return self::getDataTable('VisitTime_serverTime', $idSite, $period, $date );
	}
}

function Piwik_getTimeLabel($label)
{
	return sprintf(Piwik_Translate('VisitTime_NHour'), $label);
}
