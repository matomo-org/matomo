<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @category Piwik_Plugins
 * @package Piwik_API
 */

/**
 * 
 * @package Piwik_API
 */
class Piwik_API extends Piwik_Plugin
{
	public function getInformation()
	{
		return array(
			'name' => 'API',
			'description' => 'All the data in Piwik is available through simple APIs. This plugin is the web service entry point, that you can call to get your Web Analytics data in xml, json, php, csv, etc.</a>.',
			'homepage' => 'misc/redirectToUrl.php?url=http://dev.piwik.org/trac/wiki/API/Reference',
			'author' => 'Piwik',
			'author_homepage' => 'http://piwik.org/',
			'version' => Piwik_Version::VERSION,
		);
	}
}
