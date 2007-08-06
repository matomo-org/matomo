<?php

class Piwik_Plugin_LogStats_Provider extends Piwik_Plugin
{	
	public function __construct()
	{
	}

	public function getInformation()
	{
		$info = array(
			'name' => 'LogProvider',
			'description' => 'Log in the DB the hostname looked up from the IP',
			'author' => 'Piwik',
			'homepage' => 'http://piwik.org/plugins/LogProvider',
			'version' => '0.1',
		);
		
		return $info;
	}
	
	function install()
	{
		// add column hostname / hostname ext in the visit table
	}
	
	function uninstall()
	{
		// add column hostname / hostname ext in the visit table
	}
	
	function getListHooksRegistered()
	{
		$hooks = array(
			'LogsStats.NewVisitor' => 'detectHostname'
		);
		return $hooks;
	}
	
	function detectHostname( $notification )
	{
		$object = $notification->getNotificationObject();
		printDebug();
	}
}
/*
class Piwik_Plugin_LogStats_UserSettings extends Piwik_Plugin
{
	
}*/

?>
