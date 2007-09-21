<?php
require_once "Installation/Controller.php";
class Piwik_Installation extends Piwik_Plugin
{	
	protected $installationControllerName = 'Piwik_Installation_Controller';
	
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
			throw new Exception("Installation step {$step} not known.");
		}
		exit;
	}	
}

