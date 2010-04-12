<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 *
 * @category Piwik_Plugins
 * @package Piwik_Live
 */

/**
 *
 * @package Piwik_Live
 */
class Piwik_Live extends Piwik_Plugin
{
	public function getInformation()
	{
		return array(
			'name' => 'Live',
			'description' => Piwik_Translate('Live_PluginDescription'),
			'author' => 'Piwik',
			'author_homepage' => 'http://piwik.org/',
			'version' => Piwik_Version::VERSION,
		);
	}

	function getListHooksRegistered()
	{
		return array(
			'template_css_import' => 'css',
			'WidgetsList.add' => 'addWidget',
			'Menu.add' => 'addMenu',
		);
	}

	function css()
	{
		echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"themes/default/styles.css\" />\n";
	}

	function addMenu()
	{
		Piwik_AddMenu('General_Visitors', 'Live_VisitorLog', array('module' => 'Live', 'action' => 'getLastVisitsDetails'));
	}

	public function addWidget() {
		Piwik_AddWidget('Live!', 'Live Visitors!', 'Live', 'widget');
	}

}
