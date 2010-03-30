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
		
		try {
			$timezones = Piwik_SitesManager_API::getInstance()->getTimezonesList();
		} catch(Exception $e) {
			$timezones = array();
		}
		$view->timezoneSupported = Piwik::isTimezoneSupportEnabled();
		$view->timezones = json_encode($timezones);
		$view->defaultTimezone = Piwik_SitesManager_API::getInstance()->getDefaultTimezone();

		$view->currencies = json_encode(Piwik_SitesManager_API::getInstance()->getCurrencyList());
		$view->defaultCurrency = Piwik_SitesManager_API::getInstance()->getDefaultCurrency();

		$view->utcTime = Piwik_Date::now()->getDatetime();
		$excludedIpsGlobal = Piwik_SitesManager_API::getInstance()->getExcludedIpsGlobal();
		$view->globalExcludedIps = str_replace(',',"\n", $excludedIpsGlobal);
		$view->currentIpAddress = Piwik_Common::getIpString();

		$this->setGeneralVariablesView($view);
		$view->menu = Piwik_GetAdminMenu();
		echo $view->render();
	}
	
	function setGlobalSettings()
	{
		$response = new Piwik_API_ResponseBuilder(Piwik_Common::getRequestVar('format'));
		
		try {
    		$this->checkTokenInUrl();
    		$timezone = Piwik_Common::getRequestVar('timezone', false);
    		$excludedIps = Piwik_Common::getRequestVar('excludedIps', false);
    		$currency = Piwik_Common::getRequestVar('currency', false);
    		Piwik_SitesManager_API::getInstance()->setDefaultTimezone($timezone);
    		Piwik_SitesManager_API::getInstance()->setDefaultCurrency($currency);
    		Piwik_SitesManager_API::getInstance()->setGlobalExcludedIps($excludedIps);
			$toReturn = $response->getResponse();
		} catch(Exception $e ) {
			$toReturn = $response->getResponseException( $e );
		}
		echo $toReturn;
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
