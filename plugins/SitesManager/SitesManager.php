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
 * 
 * @package Piwik_SitesManager
 */
class Piwik_SitesManager extends Piwik_Plugin
{	
	public function getInformation()
	{
		$info = array(
			'name' => 'Sites Management',
			'description' => 'Websites Management in Piwik: Add a new Website, Edit an existing one, Show the Javascript code to include on your pages. All the actions are also available through the API.',
			'author' => 'Piwik',
			'homepage' => 'http://piwik.org/',
			'version' => '0.1',
		);
		return $info;
	}
	
	function getListHooksRegistered()
	{
		return array(
			'template_css_import' => 'css',
			'AdminMenu.add' => 'addMenu',
			'Common.fetchWebsiteAttributes' => 'recordWebsiteHostsInCache',
		);
	}
	
	function css()
	{
		echo '<link rel="stylesheet" type="text/css" href="themes/default/styles.css" />';
	}
	
	function recordWebsiteHostsInCache($notification)
	{
		$idsite = $notification->getNotificationInfo();
		// add the 'hosts' entry in the website array
		$array =& $notification->getNotificationObject();
		$urls = Piwik_SitesManager_API::getSiteUrlsFromId($idsite);
		$hosts = array();
		foreach($urls as $url)
		{
			$url = parse_url($url);
			if(isset($url['host'])) 
			{
				$hosts[] = $url['host'];
			}
		}
		$array['hosts'] = $hosts;
	}
	
	function addMenu()
	{
		Piwik_AddAdminMenu(Piwik_Translate('SitesManager_MenuSites'), array('module' => 'SitesManager', 'action' => 'index'));		
	}
}

