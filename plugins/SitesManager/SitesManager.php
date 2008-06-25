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
			// name must be the className prefix!
			'name' => 'SitesManager',
			'description' => 'Description',
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

