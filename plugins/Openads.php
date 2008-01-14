<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @package Piwik_Openads
 */
	
/**
 * 
 * @package Piwik_Openads
 */
class Piwik_Openads extends Piwik_Plugin
{	
	public function __construct()
	{
		parent::__construct();
	}
	
	public function getInformation()
	{
		$info = array(
			// name must be the className prefix!
			'name' => 'OpenAds Integration',
			'description' => 'Installing piwik from openads, importing openads users & websites, 
							sharing authentication',
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
			'Installation.startInstallation' => 'installationInit',
			'InstallationController.construct' => 'installationControllerInit'
		);
		return $hooks;
	}
	
	// triggered in constructor of the Installation plugin
	function installationInit( $notification )
	{
		$installPlugin = $notification->getNotificationObject();
		require_once "Openads/Controller.php";
		$installPlugin->setControllerToLoad( 'Piwik_Openads_Controller');
	}
	
	function installationControllerInit($notification)
	{
		$installationController = $notification->getNotificationObject();
		
		$install = $installationController;
		
		// we remove two steps from the installation
		unset($install->steps[array_search('generalSetup', $install->steps)]);
		unset($install->steps[array_search('firstWebsiteSetup', $install->steps)]);
		unset($install->steps[array_search('displayJavascriptCode', $install->steps)]);
		
		$install->steps = array_values($install->steps);
		
		// we add the openads integration just before the last step
		$lastStepIndex = count($install->steps) - 1;
		$install->steps[$lastStepIndex + 1] = $install->steps[$lastStepIndex];
		$install->steps[$lastStepIndex] = 'openadsIntegration';
		
	}
}

