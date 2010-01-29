<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @category Piwik_Plugins
 * @package Piwik_Installation
 */

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
			'name' => 'Installation',
			'description' => Piwik_Translate('Installation_PluginDescription'),
			'author' => 'Piwik',
			'author_homepage' => 'http://piwik.org/',
			'version' => Piwik_Version::VERSION,
		);
		
		return $info;
	}

	function getListHooksRegistered()
	{
		$hooks = array(
			'FrontController.NoConfigurationFile' => 'dispatch',
			'FrontController.badConfigurationFile' => 'dispatch',
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

	function dispatch($notification = null)
	{
		if($notification)
		{
			$exception = $notification->getNotificationObject();
			$message = $exception->getMessage();
		}
		else
		{
			$message = '';
		}

		Piwik_Translate::getInstance()->loadUserTranslation();

		Piwik_PostEvent('Installation.startInstallation', $this);

		$step = Piwik_Common::getRequestVar('action', 'welcome', 'string');
		$controller = $this->getInstallationController();
		if(in_array($step, array_keys($controller->getInstallationSteps())) || $step == 'saveLanguage')
		{
			$controller->$step($message);
		}
		else
		{
			Piwik::exitWithErrorMessage(Piwik_Translate('Installation_NoConfigFound'));
		}

		exit;
	}	
}
