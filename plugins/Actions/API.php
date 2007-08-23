<?php

require_once "DataFiles/Browsers.php";
require_once "DataFiles/OS.php";
		
class Piwik_Actions_API extends Piwik_Apiable
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
	
	public function getActions( $idSite, $period, $date )
	{
		Piwik::checkUserHasViewAccess( $idSite );
		$archive = Piwik_Archive::build($idSite, $date, $period );
		$dataTable = $archive->getDataTableExpanded('Actions_actions');
		return $dataTable;
	}
}