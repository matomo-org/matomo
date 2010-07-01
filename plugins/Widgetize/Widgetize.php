<?php

/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @category Piwik_Plugins
 * @package Piwik_Widgetize
 */

/**
 * 
 * @package Piwik_Widgetize
 */
class Piwik_Widgetize extends Piwik_Plugin 
{

	public function getInformation() 
	{
		return array(
			'description' => Piwik_Translate('Widgetize_PluginDescription'),
			'author' => 'Piwik',
			'author_homepage' => 'http://piwik.org/',
			'version' => Piwik_Version::VERSION,
		);
	}
	
	public function getListHooksRegistered() 
	{
		return array( 
			'AssetManager.getJsFiles' => 'getJsFiles',
			'AssetManager.getCssFiles' => 'getCssFiles',
			'TopMenu.add' => 'addTopMenu',
		);
	}	
	
	public function addTopMenu() 
	{
		Piwik_AddTopMenu('General_Widgets', array('module' => 'Widgetize', 'action' => 'index'), true, 5);
	}

	function getJsFiles($notification) 
	{
		$jsFiles = &$notification->getNotificationObject();

		$jsFiles[] = "libs/swfobject/swfobject.js";
		$jsFiles[] = "libs/jquery/jquery.tooltip.js";
		$jsFiles[] = "libs/jquery/jquery.truncate.js";
		$jsFiles[] = "libs/jquery/jquery.scrollTo.js";
		$jsFiles[] = "themes/default/common.js";
		$jsFiles[] = "plugins/CoreHome/templates/datatable.js";
		$jsFiles[] = "plugins/Dashboard/templates/widgetMenu.js";
		$jsFiles[] = "plugins/Widgetize/templates/widgetize.js";
	}	
	
	function getCssFiles($notification) 
	{
		$cssFiles = &$notification->getNotificationObject();
		
		$cssFiles[] = "plugins/CoreHome/templates/styles.css";
		$cssFiles[] = "plugins/CoreHome/templates/datatable.css";
		$cssFiles[] = "plugins/CoreHome/templates/cloud.css";
		$cssFiles[] = "plugins/Dashboard/templates/dashboard.css";
	}	
}
