<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @package Piwik_SitesManager
 */


/**
 * @package Piwik_SitesManager
 */
class Piwik_SitesManager_Controller extends Piwik_Controller
{
	function index()
	{
		$view = new Piwik_View('SitesManager/templates/SitesManager.tpl');
		$sites = Piwik_SitesManager_API::getSitesWithAdminAccess();
		foreach($sites as &$site)
		{
			$site['alias_urls'] = Piwik_SitesManager_API::getSiteUrlsFromId($site['idsite']);
		}
		$view->adminSites = $sites;
		$this->setGeneralVariablesView($view);
		$view->menu = Piwik_GetAdminMenu();
		echo $view->render();
	}
	
	function displayJavascriptCode()
	{
		$idSite = Piwik_Common::getRequestVar('idsite', 1);
		Piwik::checkUserHasViewAccess($idSite);
		$jsTag = Piwik::getJavascriptCode($idSite, Piwik_Url::getCurrentUrlWithoutFileName());
		$view = new Piwik_View('SitesManager/templates/DisplayJavascriptCode.tpl');
		$view->menu = Piwik_GetAdminMenu();
		$view->jsTag = $jsTag;
		echo $view->render();
	}
}
