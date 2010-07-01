<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @category Piwik_Plugins
 * @package Piwik_ExampleUI
 */

/*
- prepare a page with all use cases
- test the actions datatable in this page?
- test datatable with search disabled
- test datatable with low population disabled
- without footer
- without all columns icon
+ update http://dev.piwik.org/trac/wiki/HowToTestUI
*/

/**
 *
 * @package Piwik_ExampleUI
 */
class Piwik_ExampleUI extends Piwik_Plugin
{
	/**
	 * Return information about this plugin.
	 *
	 * @see Piwik_Plugin
	 *
	 * @return array
	 */
	public function getInformation()
	{
		return array(
			'description' => Piwik_Translate('ExampleUI_PluginDescription'),
			'author' => 'Piwik',
			'author_homepage' => 'http://piwik.org/',
			'version' => '0.1',
		);
	}
	
	function getListHooksRegistered()
	{
		$hooks = array(
			'Menu.add' => 'addMenus',
		);
		return $hooks;
	}

	function addMenus()
	{
		$menus = array(
			'Data tables' => 'dataTables',
			'Evolution graph' => 'evolutionGraph',
			'Bar graph' => 'barGraph',
			'Pie graph' => 'pieGraph',
			'Tag clouds' => 'tagClouds',
			'Sparklines' => 'sparklines',
			'Misc' => 'misc',
		);

		Piwik_AddMenu('UI Framework', '', array('module' => 'ExampleUI', 'action' => 'dataTables'), true, 30);
		$order = 1;
		foreach($menus as $subMenu => $action) 
		{
			Piwik_AddMenu('UI Framework', $subMenu, array('module' => 'ExampleUI', 'action' => $action), true, $order++);
		}
	}	
}
