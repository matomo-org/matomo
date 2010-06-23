<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @category Piwik
 * @package Piwik
 */

/**
 * Abstract class to define a Piwik_Plugin.
 * Any plugin has to at least implement the abstract methods of this class.
 * 
 * @package Piwik
 */
abstract class Piwik_Plugin
{
	/**
	 * Returns the plugin details
	 * 	'name' => string             // plugin name
	 * 	'description' => string      // 1/2 sentences description of the plugin 
	 * 	'author' => string           // plugin author 
	 * 	'author_homepage' => string  // author homepage (or email "mailto:youremail@example.org")
	 * 	'homepage' => string         // plugin homepage
	 * 	'version' => string          // plugin version number
	 *  'translationAvailable' => bool // is there a translation file in plugins/your-plugin/lang/* ?
	 * 	'TrackerPlugin' => bool     // should we load this plugin during the stats logging process?
	 */
	abstract function getInformation();

	/**
	 * Returns the list of hooks registered with the methods names
	 * @var array
	 */
	function getListHooksRegistered()
	{
		return array();
	}

	/**
	 * Executed after loading plugin and registering translations
	 * Useful for code that uses translated strings from the plugin.
	 */
	public function postLoad()
	{
		return;
	}
	
	/**
	 * Install the plugin
	 * - create tables
	 * - update existing tables
	 * - etc.
	 */
	public function install()
	{
		return;
	}
	  
	/**
	 * Remove the created resources during the install
	 */
	public function uninstall()
	{
		return;
	}
	
	/**
	 * Returns the plugin name
	 *
	 * @return string
	 */
	public function getName()
	{
		$info = $this->getInformation();
		return $info['name'];
	}

	/**
	 * Returns the plugin version number
	 *
	 * @return string
	 */
	public function getVersion()
	{
		$info = $this->getInformation();
		return $info['version'];
	}
	
	/**
	 * Returns the UserCountry part when the plugin class is Piwik_UserCountry
	 *
	 * @return string
	 */
	public function getClassName()
	{
		return substr(get_class($this), strlen("Piwik_"));
	}
}
