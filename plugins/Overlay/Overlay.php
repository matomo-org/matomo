<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 *
 * @category Piwik_Plugins
 * @package Piwik_Overlay
 */

class Piwik_Overlay extends Piwik_Plugin
{
	public function getInformation()
	{
		return array(
			'description' => Piwik_Translate('Overlay_PluginDescription'),
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
		$cssFiles[] = "plugins/Overlay/templates/index.css";
	}
	
	public function getJsFiles($notification)
	{
		$jsFiles = &$notification->getNotificationObject();
		$jsFiles[] = "plugins/Overlay/templates/index.js";
	}

	public function addMenu()
	{
		Piwik_AddMenu('Actions_Actions', 'Overlay_Overlay',
				array('module' => 'Overlay', 'action' => 'index'),
				$display = true, $order = 99);
	}

}
