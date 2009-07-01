<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @package Piwik_CorePluginsAdmin
 */

class Piwik_API extends Piwik_Plugin
{
	public function getInformation()
	{
		return array(
			'name' => 'API',
			'description' => 'All the data in Piwik is available through simple APIs. This plugin is the web service entry point, that you can call to get your Web Analytics data in xml, json, php, csv, etc. Discover the <a href=http://dev.piwik.org/trac/wiki/API/Reference>Piwik APIs</a>.',
			'author' => 'Piwik',
			'homepage' => 'http://piwik.org/',
			'version' => '0.1',
		);
	}
}
