<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @category Piwik_Plugins
 * @package Piwik_CoreAdminHome
 */

/**
 *
 * @package Piwik_CoreAdminHome
 */
class Piwik_CoreAdminHome_Controller extends Piwik_Controller
{
	public function index()
	{
		return $this->redirectToIndex('UsersManager', 'userSettings');
	}

	public function generalSettings()
	{
		$view = Piwik_View::factory('generalSettings');
		$enableBrowserTriggerArchiving = Piwik_ArchiveProcessing::isBrowserTriggerArchivingEnabled();
		$todayArchiveTimeToLive = Piwik_ArchiveProcessing::getTodayArchiveTimeToLive();
		$showWarningCron = false;
		if(!$enableBrowserTriggerArchiving
			&& $todayArchiveTimeToLive < 3600)
		{
			$showWarningCron = true;
		}
		$view->showWarningCron = $showWarningCron;
		$view->todayArchiveTimeToLive = $todayArchiveTimeToLive;
		$view->enableBrowserTriggerArchiving = $enableBrowserTriggerArchiving;
		$this->setBasicVariablesView($view);
		$view->topMenu = Piwik_GetTopMenu();
		$view->menu = Piwik_GetAdminMenu();
		echo $view->render();
	}
	
	public function setGeneralSettings()
	{
		$response = new Piwik_API_ResponseBuilder(Piwik_Common::getRequestVar('format'));
		try {
    		Piwik::checkUserIsSuperUser();
    		$this->checkTokenInUrl();
    		$enableBrowserTriggerArchiving = Piwik_Common::getRequestVar('enableBrowserTriggerArchiving');
    		$todayArchiveTimeToLive = Piwik_Common::getRequestVar('todayArchiveTimeToLive');

    		Piwik_ArchiveProcessing::setBrowserTriggerArchiving((bool)$enableBrowserTriggerArchiving);
    		Piwik_ArchiveProcessing::setTodayArchiveTimeToLive($todayArchiveTimeToLive);
			$toReturn = $response->getResponse();
		} catch(Exception $e ) {
			$toReturn = $response->getResponseException( $e );
		}
		echo $toReturn;
	}
	
}
