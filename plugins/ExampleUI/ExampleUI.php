<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @package Piwik_ExampleUI
 */


// TODO this plugin should be used to test all piwik UI features before release 
/*
- prepare a page with all use cases
- test the actions datatable in this page?
- test datatable with search disabled
- test datatable with low population disabled
- without footer
- without all columns icon
+ update http://dev.piwik.org/trac/wiki/HowToTestUI
*/
class Piwik_ExampleUI extends Piwik_Plugin
{
	public function getInformation()
	{
		return array(
			'name' => 'Example User Interface',
			'description' => 'Example Plugin: This plugin shows how to work with the Piwik UI: create tables, graphs, etc.',
			'author' => 'Piwik',
			'homepage' => 'http://piwik.org/',
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
		foreach($menus as $subMenu => $action) 
		{
			Piwik_AddMenu('UI Framework', $subMenu, array('module' => 'ExampleUI', 'action' => $action));
		}
	}
	
}