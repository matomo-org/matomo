<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @package Piwik_Actions
 */


require_once "DataFiles/Browsers.php";
require_once "DataFiles/OS.php";
require_once "Actions.php";

/**
 * 
 * @package Piwik_Actions
 */
class Piwik_Actions_API extends Piwik_Apiable
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
	
	protected function getDataTable($name, $idSite, $period, $date, $expanded, $idSubtable )
	{
		Piwik::checkUserHasViewAccess( $idSite );
		$archive = Piwik_Archive::build($idSite, $period, $date );
		if($idSubtable === false)
		{
			$idSubtable = null;
		}
		
		if($expanded)
		{
			$dataTable = $archive->getDataTableExpanded($name, $idSubtable);
			$dataTable->enableRecursiveSort();
		}
		else
		{
			$dataTable = $archive->getDataTable($name, $idSubtable);
		}
		return $dataTable;
	}
	
	public function getActions( $idSite, $period, $date, $expanded = false, $idSubtable = false )
	{
		return $this->getDataTable('Actions_actions', $idSite, $period, $date, $expanded, $idSubtable );
	}

	public function getDownloads( $idSite, $period, $date, $expanded = false, $idSubtable = false )
	{
		$dataTable = $this->getDataTable('Actions_downloads', $idSite, $period, $date, $expanded, $idSubtable );
		$dataTable->queueFilter('Piwik_DataTable_Filter_ColumnCallbackAddDetail', array('full_url', 'url', create_function('$url', 'return $url;')));
		$dataTable->queueFilter('Piwik_DataTable_Filter_ColumnCallbackReplace', array('label', 'Piwik_getPathFromActionsUrl'));
		$dataTable->queueFilter('Piwik_DataTable_Filter_ColumnCallbackReplace', array('label', 'Piwik_truncateActionsPath'));
		
		return $dataTable;
	}

	public function getOutlinks( $idSite, $period, $date, $expanded = false, $idSubtable = false )
	{
		$dataTable = $this->getDataTable('Actions_outlink', $idSite, $period, $date, $expanded, $idSubtable );
		$dataTable->queueFilter('Piwik_DataTable_Filter_ColumnCallbackAddDetail', array('full_url', 'url', create_function('$url', 'return $url;')));
		$dataTable->queueFilter('Piwik_DataTable_Filter_ColumnCallbackReplace', array('label', 'Piwik_getPathFromActionsUrl'));
		$dataTable->queueFilter('Piwik_DataTable_Filter_ColumnCallbackReplace', array('label', 'Piwik_truncateActionsPath'));
		return $dataTable;
	}
}

/**
 * returns /Y in http://X/Y
 *
 * @param string $url
 * @return string
 */
function Piwik_getPathFromActionsUrl($url)
{
	$n = preg_match("#://[^/]+(/)#",$url, $matches, PREG_OFFSET_CAPTURE);
	if($n)
	{
		$returned = substr($url, $matches[1][1]);
		return $returned;
	}
	
	return $url;
}

function Piwik_truncateActionsPath( $path )
{
	$limit = 27;
	$path = htmlspecialchars_decode($path);
	$len = strlen($path);
	if($len > $limit)
	{
		$path = substr($path, 0, $limit-3) . "...";
	}
	return htmlspecialchars($path);
}
