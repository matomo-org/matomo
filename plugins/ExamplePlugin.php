<?php
	
class Piwik_Plugin_Example extends Piwik_Plugin
{	
	public function __construct()
	{
		parent::__construct();
	}
	
	public function getInformation()
	{
		$info = array(
			'name' => 'Name',
			'description' => 'Description',
			'author' => 'Piwik',
			'homepage' => 'http://piwik.org/',
			'version' => '0.1',
		);
		
		return $info;
	}
	
	function install()
	{
	}
	
	function uninstall()
	{
	}
	
	function getListHooksRegistered()
	{
		$hooks = array(
			'ArchiveProcessing_Day.compute' => 'myMethod'
		);
		return $hooks;
	}
	
	function myMethod($notification)
	{
		$objectPassed = $notification->getNotificationObject();
	}
}