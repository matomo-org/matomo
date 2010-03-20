<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @category Piwik_Plugins
 * @package Piwik_ExampleAPI
 */

/** 
 * ExampleAPI plugin
 *
 * @package Piwik_ExampleAPI
 */
class Piwik_ExampleAPI extends Piwik_Plugin
{
	/**
	 * Return information about this plugin.
	 *
	 * @see Piwik_Plugin
	 *
	 * @return array
	 */
	public function getInformation()
	{
		return array(
			'name' => 'ExampleAPI',
			'description' => Piwik_Translate('ExampleAPI_PluginDescription'),
			'homepage' => 'index.php?module=API&action=listAllAPI#ExampleAPI',
			'author' => 'Piwik',
			'author_homepage' => 'http://piwik.org/',
			'version' => '0.1',
		);
	}
}
