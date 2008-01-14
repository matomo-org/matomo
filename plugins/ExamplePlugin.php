<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 */
	
class Piwik_ExamplePlugin extends Piwik_Plugin
{	
	public function __construct()
	{
		parent::__construct();
	}
	
	public function getInformation()
	{
		$info = array(
			// name must be the className prefix!
			'name' => 'ExamplePlugin',
			'description' => 'Description',
			'author' => 'Piwik',
			'homepage' => 'http://piwik.org/',
			'version' => '0.1',
			'translationAvailable' => false,
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

