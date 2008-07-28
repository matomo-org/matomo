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
			'name' => 'SitesManager',
			'description' => 'Websites Management in Piwik: Add a new Website, Edit an existing one, Show the Javascript code to include on your pages. All the actions are also available through the API.',
			'author' => 'Piwik',
			'homepage' => 'http://piwik.org/',
			'version' => '0.1',
		);
		return $info;
	}
	
	function postLoad()
	{
		Piwik_AddAdminMenu(Piwik_Translate('SitesManager_MenuSites'), array('module' => 'SitesManager'));		
	}
}

