<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @category Piwik_Plugins
 * @package Piwik_CoreHome
 */

/**
 *
 * @package Piwik_CoreHome
 */
class Piwik_CoreHome extends Piwik_Plugin
{
	public function getInformation()
	{
		return array(
			'description' => Piwik_Translate('CoreHome_PluginDescription'),
			'author' => 'Piwik',
			'author_homepage' => 'http://piwik.org/',
			'version' => Piwik_Version::VERSION,
		);
	}
	
	function getListHooksRegistered()
	{
		return array(
			'AssetManager.getCssFiles' => 'getCssFiles',
			'AssetManager.getJsFiles' => 'getJsFiles',
			'TopMenu.add' => 'addTopMenu',
		);
	}

	public function addTopMenu()
	{
		Piwik_AddTopMenu('General_Dashboard', array('module' => 'CoreHome', 'action' => 'index'), true, 1);
	}

	function getCssFiles( $notification )
	{		
		$cssFiles = &$notification->getNotificationObject();
		
		$cssFiles[] = "themes/default/common.css";
		$cssFiles[] = "libs/jquery/themes/base/jquery-ui.css";
		$cssFiles[] = "plugins/CoreHome/templates/styles.css";
		$cssFiles[] = "plugins/CoreHome/templates/menu.css";
		$cssFiles[] = "plugins/CoreHome/templates/datatable.css";
		$cssFiles[] = "plugins/CoreHome/templates/cloud.css";
	}

	function getJsFiles( $notification )
	{
		$jsFiles = &$notification->getNotificationObject();
		
		$jsFiles[] = "libs/jquery/jquery.js";
		$jsFiles[] = "libs/jquery/jquery-ui.js";
		$jsFiles[] = "libs/jquery/jquery.bgiframe.js";
		$jsFiles[] = "libs/jquery/jquery.tooltip.js";
		$jsFiles[] = "libs/jquery/jquery.truncate.js";
		$jsFiles[] = "libs/jquery/jquery.scrollTo.js";
		$jsFiles[] = "libs/jquery/jquery.blockUI.js";
		$jsFiles[] = "libs/jquery/fdd2div-modified.js";
		$jsFiles[] = "libs/jquery/superfish_modified.js";
		$jsFiles[] = "libs/jquery/jquery.history.js";
		$jsFiles[] = "libs/swfobject/swfobject.js";
		$jsFiles[] = "libs/javascript/sprintf.js";
		$jsFiles[] = "themes/default/common.js";
		$jsFiles[] = "plugins/CoreHome/templates/datatable.js";
		$jsFiles[] = "plugins/CoreHome/templates/broadcast.js";
		$jsFiles[] = "plugins/CoreHome/templates/menu.js";	
		$jsFiles[] = "plugins/CoreHome/templates/calendar.js";
		$jsFiles[] = "plugins/CoreHome/templates/date.js";
	}
	
}
