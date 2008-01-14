<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @package Piwik_VisitorInterest
 */



class Piwik_VisitorInterest_API extends Piwik_Apiable
{
	static private $instance = null;
	protected function __construct()
	{
		parent::__construct();
	}
	
	static public function getInstance()
	{
		if (self::$instance == null)
		{            
			$c = __CLASS__;
			self::$instance = new $c();
		}
		return self::$instance;
	}


	public function getNumberOfVisitsPerVisitDuration( $idSite, $period, $date )
	{
		Piwik::checkUserHasViewAccess( $idSite );
		$archive = Piwik_Archive::build($idSite, $date, $period );
		$dataTable = $archive->getDataTable('VisitorInterest_timeGap');
		
		$dataTable->queueFilter('Piwik_DataTable_Filter_ReplaceColumnNames');
		$dataTable->queueFilter('Piwik_DataTable_Filter_Sort', array('label', 'asc', true));
		$dataTable->queueFilter('Piwik_DataTable_Filter_ColumnCallbackReplace', array('label', 'Piwik_getDurationLabel'));
	
		return $dataTable;
	}


	public function getNumberOfVisitsPerPage( $idSite, $period, $date )
	{
		Piwik::checkUserHasViewAccess( $idSite );
		$archive = Piwik_Archive::build($idSite, $date, $period );
		$dataTable = $archive->getDataTable('VisitorInterest_pageGap');
		$dataTable->queueFilter('Piwik_DataTable_Filter_ReplaceColumnNames');
		$dataTable->queueFilter('Piwik_DataTable_Filter_Sort', array('label', 'asc', true));
		$dataTable->queueFilter('Piwik_DataTable_Filter_ColumnCallbackReplace', array('label', 'Piwik_getPageGapLabel'));
	
		return $dataTable;
	}
}

function Piwik_getDurationLabel($label)
{ 
	if(($pos = strpos($label,'-')) !== false)
	{
		$min = substr($label, 0, $pos);
		$max = substr($label, $pos+1);
		
		if($min == 0 || $min == 30)
		{
			return $min.'-'.$max.'s';
		}
		else
		{
			$min = $min / 60;
			$max = $max / 60;
			return $min.'-'.$max.' min';
		}
	}
	else
	{
		$time = intval($label) / 60;
		return urlencode('+').$time.' min';
	}
}

function Piwik_getPageGapLabel($label)
{
	$return = false;
	if(($pos = strpos($label,'-')) !== false)
	{
		$min = substr($label, 0, $pos);
		$max = substr($label, $pos+1);
		
		if($min == $max)
		{
			$return = $min;
		}
	}
	if(!$return)
	{
		$return = $label;
	}
	
	if($return == 1)
	{
		return $return . " page";
	}
	
	return $return . " pages";
}
