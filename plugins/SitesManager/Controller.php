<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @category Piwik_Plugins
 * @package Piwik_SitesManager
 */

/**
 *
 * @package Piwik_SitesManager
 */
class Piwik_SitesManager_Controller extends Piwik_Controller
{
	function index()
	{
		$view = Piwik_View::factory('SitesManager');
		$sites = Piwik_SitesManager_API::getInstance()->getSitesWithAdminAccess();
		foreach($sites as &$site)
		{
			$site['alias_urls'] = Piwik_SitesManager_API::getInstance()->getSiteUrlsFromId($site['idsite']);
			$site['excluded_ips'] = str_replace(',','<br/>', $site['excluded_ips']);
		}
		$view->adminSites = $sites;
		$view->currentIpAddress = Piwik_Common::getIpString();
		$this->setGeneralVariablesView($view);
		$excludedIpsGlobal = Piwik_SitesManager_API::getInstance()->getExcludedIpsGlobal();
		$view->globalExcludedIps = str_replace(',',"\n", $excludedIpsGlobal);
		$view->menu = Piwik_GetAdminMenu();
		echo $view->render();
	}
	
	function displayJavascriptCode()
	{
		$idSite = Piwik_Common::getRequestVar('idsite', 1);
		Piwik::checkUserHasViewAccess($idSite);
		$jsTag = Piwik::getJavascriptCode($idSite, Piwik_Url::getCurrentUrlWithoutFileName());
		$view = Piwik_View::factory('DisplayJavascriptCode');
		$this->setGeneralVariablesView($view);
		$view->menu = Piwik_GetAdminMenu();
		$site = new Piwik_Site($idSite);
		$view->displaySiteName = $site->getName();
		$view->jsTag = $jsTag;
		echo $view->render();
	}
}
