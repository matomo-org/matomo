<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @package Piwik_UsersManager
 */
	
/**
 * 
 * @package Piwik_UsersManager
 */
class Piwik_UsersManager extends Piwik_Plugin
{		
	public function getInformation()
	{
		$info = array(
			// name must be the className prefix!
			'name' => 'UsersManager',
			'description' => 'Users Management in Piwik: add a new User, edit an existing one, update the permissions. All the actions are also available through the API.',
			'author' => 'Piwik',
			'homepage' => 'http://piwik.org/',
			'version' => '0.1',
		);
		
		return $info;
	}
	
	function postLoad()
	{
		Piwik_AddAdminMenu(Piwik_Translate('UsersManager_MenuUsers'), array('module' => 'UsersManager'));		
	}
}

