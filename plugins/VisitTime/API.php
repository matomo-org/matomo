<?php
		
class Piwik_VisitTime_API extends Piwik_Apiable
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
	
	protected function getDataTable($name, $idSite, $period, $date )
	{
		Piwik::checkUserHasViewAccess( $idSite );
		
		$archive = Piwik_Archive::build($idSite, $date, $period );
		$dataTable = $archive->getDataTable($name);
		//$dataTable->queueFilter('Piwik_DataTable_Filter_Sort', array('label', 'asc'));
		$dataTable->queueFilter('Piwik_DataTable_Filter_ColumnCallbackReplace', array('label', 'Piwik_getTimeLabel'));
		$dataTable->queueFilter('Piwik_DataTable_Filter_ReplaceColumnNames');
		
		
		return $dataTable;
	}
	
	public function getVisitInformationPerLocalTime( $idSite, $period, $date )
	{
		return $this->getDataTable('VisitTime_localTime', $idSite, $period, $date );
	}
	
	public function getVisitInformationPerServerTime( $idSite, $period, $date )
	{
		return $this->getDataTable('VisitTime_serverTime', $idSite, $period, $date );
	}
}

function Piwik_getTimeLabel($label)
{
	return $label . "h";
}