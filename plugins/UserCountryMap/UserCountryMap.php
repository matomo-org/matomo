<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id: $
 *
 * @category Piwik_Plugins
 * @package Piwik_UserCountryMap
 */

/**
 *
 * @package Piwik_UserCountryMap
 */
class Piwik_UserCountryMap extends Piwik_Plugin
{
	public function getInformation()
	{
		return array(
			'name' => 'User Country Map',
			'description' => 'This plugin shows a zoomable world map of your visitors location.',
			'author' => 'Piwik',
			'author_homepage' => 'http://piwik.org/',
			'version' => Piwik_Version::VERSION
		);
	}

	function postLoad()
	{
		Piwik_AddWidget('General_Visitors', Piwik_Translate('UserCountry_WidgetLocation').' ('.Piwik_Translate('UserCountryMap_worldMap').')', 'UserCountryMap', 'worldMap');
	}
}
