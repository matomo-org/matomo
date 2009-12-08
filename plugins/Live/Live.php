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
	TODO Live! Plugin
	====
	- api propre
	- html
	- jquery spy
	- make sure only one query is launched at once or what if requests takes more than 10s to succeed?
	- simple stats above in TEXT
	- Security review
	- blog post, push version
	
//TODO add api to get actions name/count/first/last/etc
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
			'name' => 'Live Visitors',
			'description' => 'Live Visitors!',
			'author' => 'Piwik',
			'homepage' => 'http://piwik.org/',
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
		Piwik_AddMenu('General_Visitors', 'VisitorLog', array('module' => 'Live', 'action' => 'getLastVisitsDetails'));
	}
	
	public function addWidget() {
		Piwik_AddWidget('Live!', 'Live Visitors!', 'Live', 'widget');
	}
	
}
