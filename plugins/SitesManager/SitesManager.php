<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 *
 * @category Piwik_Plugins
 * @package Piwik_SitesManager
 */

/**
 *
 * @package Piwik_SitesManager
 */
class Piwik_SitesManager extends Piwik_Plugin
{
	public function getInformation()
	{
		$info = array(
			'description' => Piwik_Translate('SitesManager_PluginDescription'),
			'author' => 'Piwik',
			'author_homepage' => 'http://piwik.org/',
			'version' => Piwik_Version::VERSION,
		);
		return $info;
	}

	function getListHooksRegistered()
	{
		return array(
			'AssetManager.getJsFiles' => 'getJsFiles',
			'AssetManager.getCssFiles' => 'getCssFiles',
			'AdminMenu.add' => 'addMenu',
			'Common.fetchWebsiteAttributes' => 'recordWebsiteDataInCache',
		);
	}

	function addMenu()
	{
		Piwik_AddAdminMenu('SitesManager_MenuSites',
							array('module' => 'SitesManager', 'action' => 'index'),
							Piwik::isUserHasSomeAdminAccess(),
							$order = 5);
	}

	/**
	 * Get CSS files
	 *
	 * @param Piwik_Event_Notification $notification  notification object
	 */
	function getCssFiles( $notification )
	{
		$cssFiles = &$notification->getNotificationObject();

		$cssFiles[] = "themes/default/styles.css";
	}

	/**
	 * Get JavaScript files
	 *
	 * @param Piwik_Event_Notification $notification  notification object
	 */
	function getJsFiles( $notification )
	{
		$jsFiles = &$notification->getNotificationObject();

		$jsFiles[] = "plugins/SitesManager/templates/SitesManager.js";
	}

	/**
	 * Hooks when a website tracker cache is flushed (website updated, cache deleted, or empty cache)
	 * Will record in the tracker config file all data needed for this website in Tracker.
	 *
	 * @param Piwik_Event_Notification $notification  notification object
	 * @return void
	 */
	function recordWebsiteDataInCache($notification)
	{
		$idSite = $notification->getNotificationInfo();
		// add the 'hosts' entry in the website array
		$array =& $notification->getNotificationObject();
		$array['hosts'] = $this->getTrackerHosts($idSite);
		$array['excluded_ips'] = $this->getTrackerExcludedIps($idSite);
		$array['excluded_parameters'] = $this->getTrackerExcludedQueryParameters($idSite);
	}

	/**
	 * Returns the array of excluded IPs to save in the config file
	 *
	 * @param int $idSite
	 * @return array
	 */
	private function getTrackerExcludedIps($idSite)
	{
		$website = Piwik_SitesManager_API::getInstance()->getSiteFromId($idSite);
		$excludedIps = $website['excluded_ips'];
		$globalExcludedIps = Piwik_SitesManager_API::getInstance()->getExcludedIpsGlobal();

		$excludedIps .= ',' . $globalExcludedIps;

		$ipRanges = array();
		foreach(explode(',', $excludedIps) as $ip)
		{
			$ipRange = Piwik_SitesManager_API::getInstance()->getIpsForRange($ip);
			if($ipRange !== false)
			{
				$ipRanges[] = $ipRange;
			}
		}
		return $ipRanges;
	}

	/**
	 * Returns the array of URL query parameters to exclude from URLs
	 *
	 * @param int $idSite
	 * @return array
	 */
	private function getTrackerExcludedQueryParameters($idSite)
	{
		$website = Piwik_SitesManager_API::getInstance()->getSiteFromId($idSite);
		$excludedQueryParameters = $website['excluded_parameters'];
		$globalExcludedQueryParameters = Piwik_SitesManager_API::getInstance()->getExcludedQueryParametersGlobal();

		$excludedQueryParameters .= ',' . $globalExcludedQueryParameters;
		$parameters = explode(',', $excludedQueryParameters);
		$parameters = array_filter($parameters, 'strlen');
		$parameters = array_unique($parameters);
		return $parameters;
	}

	/**
	 * Returns the hosts alias URLs
	 * @param int $idSite
	 * @return array
	 */
	private function getTrackerHosts($idSite)
	{
		$urls = Piwik_SitesManager_API::getInstance()->getSiteUrlsFromId($idSite);
		$hosts = array();
		foreach($urls as $url)
		{
			$url = parse_url($url);
			if(isset($url['host']))
			{
				$hosts[] = $url['host'];
			}
		}
		return $hosts;
	}
}
