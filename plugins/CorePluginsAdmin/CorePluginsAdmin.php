<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @category Piwik_Plugins
 * @package Piwik_CorePluginsAdmin
 */

/**
 *
 * @package Piwik_CorePluginsAdmin
 */
class Piwik_CorePluginsAdmin extends Piwik_Plugin
{
	public function getInformation()
	{
		return array(
			'name' => 'Plugins Management',
			'description' => 'Plugins Administration Interface.',
			'author' => 'Piwik',
			'homepage' => 'http://piwik.org/',
			'version' => '0.1',
		);
	}
	
	function getListHooksRegistered()
	{
		return array('AdminMenu.add' => 'addMenu');
	}
	
	function addMenu()
	{
		Piwik_AddAdminMenu(Piwik_Translate('CorePluginsAdmin_MenuPlugins'), array('module' => 'CorePluginsAdmin', 'action' => 'index'));		
	}
}
