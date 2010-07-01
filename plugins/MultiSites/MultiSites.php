<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 *
 * @category Piwik_Plugins
 * @package Piwik_MultiSites
 */

/**
 *
 * @package Piwik_MultiSites
 */
class Piwik_MultiSites extends Piwik_Plugin
{
	public function getInformation()
	{
		return array(
			'description' => Piwik_Translate('MultiSites_PluginDescription'),
			'author' => 'ClearCode.cc',
			'author_homepage' => "http://clearcode.cc/",
			'version' => Piwik_Version::VERSION,
		);
	}

	public function getListHooksRegistered()
	{
		return array(
			'AssetManager.getCssFiles' => 'getCssFiles',
			'AssetManager.getJsFiles' => 'getJsFiles',
			'TopMenu.add' => 'addTopMenu',
		);
	}

	public function addTopMenu()
	{
		Piwik_AddTopMenu('General_MultiSitesSummary', array('module' => 'MultiSites', 'action' => 'index'), true, 3);
	}

	function getJsFiles( $notification )
	{
		$jsFiles = &$notification->getNotificationObject();
		
		$jsFiles[] = "plugins/MultiSites/templates/common.js";
	}
	
	function getCssFiles( $notification )
	{
		$cssFiles = &$notification->getNotificationObject();
		
		$cssFiles[] = "plugins/MultiSites/templates/styles.css";
	}
}
