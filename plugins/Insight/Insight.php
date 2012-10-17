<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 *
 * @category Piwik_Plugins
 * @package Piwik_Insight
 */

class Piwik_Insight extends Piwik_Plugin
{
	public function getInformation()
	{
		return array(
			'description' => Piwik_Translate('Insight_PluginDescription'),
			'author' => 'Piwik',
			'author_homepage' => 'http://piwik.org/',
			'version' => Piwik_Version::VERSION,
		);
	}

	public function getListHooksRegistered()
	{
		return array(
			'AssetManager.getCssFiles' => 'getCssFiles',
			'AssetManager.getJsFiles' => 'getJsFiles',
			'Menu.add' => 'addMenu',
		);
	}
	
	public function getCssFiles($notification)
	{
		$cssFiles = &$notification->getNotificationObject();
		$cssFiles[] = "plugins/Insight/templates/index.css";
	}
	
	public function getJsFiles($notification)
	{
		$jsFiles = &$notification->getNotificationObject();
		$jsFiles[] = "plugins/Insight/templates/insight.js";
	}

	public function addMenu()
	{
		Piwik_AddMenu('General_Visitors', 'Insight_Insight',
				array('module' => 'Insight', 'action' => 'index'),
				$display = true, $order = 60);
	}

}
