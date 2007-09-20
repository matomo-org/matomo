<?php
require_once "Installation/Controller.php";
class Piwik_Installation extends Piwik_Plugin
{	
	public function __construct()
	{
		parent::__construct();
	}
	
	public function getInformation()
	{
		$info = array(
			// name must be the className prefix!
			'name' => 'Installation',
			'description' => 'Description',
			'author' => 'Piwik',
			'homepage' => 'http://piwik.org/',
			'version' => '0.1',
			'translationAvailable' => false,
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
	
	function startInstallation()
	{
		//Piwik::redirectToModule('Installation', 'welcome');
		$step = Piwik_Common::getRequestVar('action', 'welcome', 'string');
		
		$controller = new Piwik_Installation_Controller;
		if(in_array($step, $controller->getInstallationSteps()))
		{
			$controller->$step();
		}
		else
		{
			throw new Exception("Installation step {$step} not known.");
		}
		exit;
	}	
}

