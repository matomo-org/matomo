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
	/*
	 * Main view showing listing of websites and settings 
	 */
	function index()
	{
		$view = Piwik_View::factory('SitesManager');
		$sites = Piwik_SitesManager_API::getInstance()->getSitesWithAdminAccess();
		foreach($sites as &$site)
		{
			$site['alias_urls'] = Piwik_SitesManager_API::getInstance()->getSiteUrlsFromId($site['idsite']);
			$site['excluded_ips'] = str_replace(',','<br/>', $site['excluded_ips']);
			$site['excluded_parameters'] = str_replace(',','<br/>', $site['excluded_parameters']);
		}
		$view->adminSites = $sites;
		
		$timezones = Piwik_SitesManager_API::getInstance()->getTimezonesList();
		$view->timezoneSupported = Piwik::isTimezoneSupportEnabled();
		$view->timezones = json_encode($timezones);
		$view->defaultTimezone = Piwik_SitesManager_API::getInstance()->getDefaultTimezone();

		$view->currencies = json_encode(Piwik_SitesManager_API::getInstance()->getCurrencyList());
		$view->defaultCurrency = Piwik_SitesManager_API::getInstance()->getDefaultCurrency();
		
		$view->utcTime = Piwik_Date::now()->getDatetime();
		$excludedIpsGlobal = Piwik_SitesManager_API::getInstance()->getExcludedIpsGlobal();
		$view->globalExcludedIps = str_replace(',',"\n", $excludedIpsGlobal);
		$excludedQueryParametersGlobal = Piwik_SitesManager_API::getInstance()->getExcludedQueryParametersGlobal();
		$view->globalExcludedQueryParameters = str_replace(',',"\n", $excludedQueryParametersGlobal);
		$view->currentIpAddress = Piwik_Common::getIpString();

		$this->setGeneralVariablesView($view);
		$view->menu = Piwik_GetAdminMenu();
		echo $view->render();
	}
	
	/*
	 * Records Global settings when user submit changes
	 */
	function setGlobalSettings()
	{
		$response = new Piwik_API_ResponseBuilder(Piwik_Common::getRequestVar('format'));
		
		try {
    		$this->checkTokenInUrl();
    		$timezone = Piwik_Common::getRequestVar('timezone', false);
    		$excludedIps = Piwik_Common::getRequestVar('excludedIps', false);
    		$excludedQueryParameters = Piwik_Common::getRequestVar('excludedQueryParameters', false);
    		$currency = Piwik_Common::getRequestVar('currency', false);
    		Piwik_SitesManager_API::getInstance()->setDefaultTimezone($timezone);
    		Piwik_SitesManager_API::getInstance()->setDefaultCurrency($currency);
    		Piwik_SitesManager_API::getInstance()->setGlobalExcludedQueryParameters($excludedQueryParameters);
    		Piwik_SitesManager_API::getInstance()->setGlobalExcludedIps($excludedIps);
			$toReturn = $response->getResponse();
		} catch(Exception $e ) {
			$toReturn = $response->getResponseException( $e );
		}
		echo $toReturn;
	}
	
	/**
	 * Displays the admin UI page showing all tracking tags
	 * @return unknown_type
	 */
	function displayJavascriptCode()
	{
		$idSite = Piwik_Common::getRequestVar('idSite', 1);
		Piwik::checkUserHasViewAccess($idSite);
		$jsTag = Piwik::getJavascriptCode($idSite, Piwik_Url::getCurrentUrlWithoutFileName());
		$view = Piwik_View::factory('Tracking');
		$this->setGeneralVariablesView($view);
		$view->menu = Piwik_GetAdminMenu();
		$site = new Piwik_Site($idSite);
		$view->displaySiteName = $site->getName();
		$view->jsTag = $jsTag;
		$view->currentUrlWithoutFilename = Piwik_Url::getCurrentUrlWithoutFileName();
		echo $view->render();
	}
	
	/*
	 *  User will download a file called PiwikTracker.php that is the content of the actual script
	 */ 
	function downloadPiwikTracker()
	{
		$path = PIWIK_INCLUDE_PATH . '/core/Tracker/';
		$filename = 'PiwikTracker.php';
        header('Content-type: text/php');
        header('Content-Disposition: attachment; filename="'.$filename.'"');
        echo file_get_contents( $path . $filename);
	}
	
	/**
	 * Used to generate the doc at http://piwik.org/docs/tracking-api/
	 */
	function displayAlternativeTagsHelp()
	{
		$view = Piwik_View::factory('DisplayAlternativeTags');
		$view->idSite = Piwik_Common::getRequestVar('idSite');
		$view->piwikUrl = Piwik_Common::getRequestVar('piwikUrl');
		
		// Links are prefixed, need to be absolute for this page as it is externally loaded
		$view->currentUrlWithoutFilename = Piwik_Url::getCurrentUrlWithoutFileName();
		echo $view->render();
	}
}
