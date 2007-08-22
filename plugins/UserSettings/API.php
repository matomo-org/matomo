<?php

class Piwik_UserSettings_API extends Piwik_Apiable
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
	
	public function getResolution( $idSite, $period, $date )
	{
		Piwik::checkUserHasViewAccess( $idSite );
		$archive = Piwik_Archive::build($idSite, $date, $period );
		$dataTable = $archive->getDataTable('UserSettings_resolution');
		return $dataTable;
	}
//				'UserSettings_configuration',
//				'UserSettings_os',
//				'UserSettings_browser',
//				'UserSettings_browserType',
//				'UserSettings_resolution',
//				'UserSettings_wideScreen',
//				'UserSettings_plugin',
	
}
?>
