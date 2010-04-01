<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @category Piwik_Plugins
 * @package Piwik_CoreAdminHome
 */

/**
 *
 * @package Piwik_CoreAdminHome
 */
class Piwik_CoreAdminHome extends Piwik_Plugin
{
	public function getInformation()
	{
		return array(
			'name' => 'CoreAdminHome',
			'description' => Piwik_Translate('CoreAdminHome_PluginDescription'),
			'author' => 'Piwik',
			'author_homepage' => 'http://piwik.org/',
			'version' => Piwik_Version::VERSION,
		);
	}

	public function getListHooksRegistered()
	{
		return array( 
			'template_css_import' => 'css',
			'AdminMenu.add' => 'addMenu'
		);
	}

	function css()
	{
		echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"plugins/CoreAdminHome/templates/menu.css\" />\n";
	}
	
	function addMenu()
	{
		Piwik_AddAdminMenu('CoreAdminHome_MenuGeneralSettings', 
							array('module' => 'CoreAdminHome', 'action' => 'generalSettings'),
							Piwik::isUserIsSuperUser(),
							$order = 6);
	}
}
