<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: ExamplePlugin.php 169 2008-01-14 05:41:15Z matt $
 * 
 * @package Piwik_ExamplePlugin
 */

class Piwik_Dashboard extends Piwik_Plugin
{
	public function getInformation()
	{
		return array(
			// name must be the className prefix!
			'name' => 'Dashboard',
			'description' => '',
			'author' => 'Piwik',
			'homepage' => 'http://piwik.org/',
			'version' => '0.1',
		);
	}
}
