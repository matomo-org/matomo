<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @package Piwik_Installation
 */


require_once "Installation/Controller.php";

/**
 * 
 * @package Piwik_Installation
 */
class Piwik_Installation extends Piwik_Plugin
{	
	protected $installationControllerName = 'Piwik_Installation_Controller';
		
	public function getInformation()
	{
		$info = array(
			// name must be the className prefix!
			'name' => 'Installation',
			'description' => 'Description',
			'author' => 'Piwik',
			'homepage' => 'http://piwik.org/',
			'version' => '0.1',
			'translationAvailable' => true,
		);
		
		return $info;
	}
	
	function getListHooksRegistered()
	{
		$hooks = array(
			'FrontController.NoConfigurationFile' 		=> 'startInstallation',
		);
		return $hooks;
	}
	
	public function setControllerToLoad( $newControllerName )
	{
		$this->installationControllerName = $newControllerName;
	}
	
	protected function getInstallationController()
	{
		return new $this->installationControllerName();
	}
	
	function startInstallation()
	{
		Piwik_PostEvent('Installation.startInstallation', $this);
		
		//Piwik::redirectToModule('Installation', 'welcome');
		$step = Piwik_Common::getRequestVar('action', 'welcome', 'string');
		
		$controller = $this->getInstallationController();
		if(in_array($step, $controller->getInstallationSteps()))
		{
			$controller->$step();
		}
		else
		{
			Piwik::exitWithErrorMessage(Piwik_Translate('Installation_NoConfigFound'));
		}
		exit;
	}	
}
