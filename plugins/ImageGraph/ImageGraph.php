<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version 0.2.6_a
 * 
 * @category Piwik_Plugins
 * @package Piwik_ImageGraph
 */

class Piwik_ImageGraph extends Piwik_Plugin
{

	public function getInformation()
	{
		return array(
			'name' => 'Image Graph',
			'description' => Piwik_Translate('ImageGraph_PluginDescription'),
			'author' => 'Piwik',
			'author_homepage' => 'http://piwik.org/',
			'version' => Piwik_Version::VERSION
		);
	}
	
}