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
class Piwik_SitesManager extends Piwik_Plugin
{	
	public function getInformation()
	{
		$info = array(
			'name' => 'SitesManager',
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
			'template_css_import' => 'css',
			'AdminMenu.add' => 'addMenu',
			'Common.fetchWebsiteAttributes' => 'recordWebsiteDataInCache',
		);
	}
	
	function addMenu()
	{
		Piwik_AddAdminMenu('SitesManager_MenuSites', array('module' => 'SitesManager', 'action' => 'index'));		
	}
	
	function css()
	{
		echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"themes/default/styles.css\" />\n";
	}
	
	/**
	 * Hooks when a website tracker cache is flushed (website updated, cache deleted, or empty cache)
	 * Will record in the tracker config file all data needed for this website in Tracker. 
	 * 
	 * @param $notification
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
	 * @param $idSite
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
			$ipMin = $ipMax = $ip;
			if(substr_count($ip, '*') > 0)
			{
				$ipMin = str_replace('*', '0', $ip); 
				$ipMax = str_replace('*', '255', $ip);
			}
			$ipRange = array( ip2long($ipMin), ip2long($ipMax));
			
			// we can still get invalid IPs at this stage (eg. ip2long(555.1.1.1) would return false)
			if($ipRange[0] === false || $ipRange[1] === false) 
			{
				continue;
			}

			// long data type is signed; convert to stringified unsigned number
			$ipRange[0] = sprintf("%u", $ipRange[0]);
			$ipRange[1] = sprintf("%u", $ipRange[1]);

			$ipRanges[] = $ipRange;
		}
		return $ipRanges;
	}
	
	/**
	 * Returns the array of URL query parameters to exclude from URLs
	 * @param $idSite
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
	 * @param $idSite
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
