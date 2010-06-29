<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 *
 * @category Piwik_Plugins
 * @package Piwik_Feedback
 */

/**
 *
 * @package Piwik_Feedback
 */
class Piwik_Feedback extends Piwik_Plugin
{
	public function getInformation()
	{
		return array(
			'description' => Piwik_Translate('Feedback_PluginDescription'),
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
		);
	}

	function getCssFiles( $notification )
	{
		$cssFiles = &$notification->getNotificationObject();
		
		$cssFiles[] = "plugins/Feedback/templates/styles.css";
	}

	function getJsFiles( $notification )
	{
		$jsFiles = &$notification->getNotificationObject();
		
		$jsFiles[] = "plugins/Feedback/templates/feedback.js";
	}	
	
}
