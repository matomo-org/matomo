<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 * 
 * @category Piwik_Plugins
 * @package Piwik_OptOut
 */

/**
 * Plugin to allow visitors to opt-out of tracking.
 *
 * @package Piwik_OptOut
 */
class Piwik_OptOut extends Piwik_Plugin
{
	public function getInformation()
	{
		$info = array(
			'description' => Piwik_Translate('OptOut_PluginDescription'),
			'author' => 'Piwik',
			'author_homepage' => 'http://piwik.org/',
			'version' => Piwik_Version::VERSION
		);

		return $info;
	}
}
