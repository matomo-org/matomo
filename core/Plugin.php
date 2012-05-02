<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
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
	 * - 'description' => string        // 1-2 sentence description of the plugin
	 * - 'author' => string             // plugin author 
	 * - 'author_homepage' => string    // author homepage URL (or email "mailto:youremail@example.org")
	 * - 'homepage' => string           // plugin homepage URL
	 * - 'license' => string            // plugin license
	 * - 'license_homepage' => string   // license homepage URL
	 * - 'version' => string            // plugin version number; examples and 3rd party plugins must not use Piwik_Version::VERSION; 3rd party plugins must increment the version number with each plugin release
	 * - 'translationAvailable' => bool // is there a translation file in plugins/your-plugin/lang/* ?
	 * - 'TrackerPlugin' => bool        // should we load this plugin during the stats logging process?
	 */
	abstract function getInformation();

	/**
	 * Returns the list of hooks registered with the methods names
	 * @return array
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
	 * Returns the plugin's base class name without the "Piwik_" prefix,
	 * e.g., "UserCountry" when the plugin class is "Piwik_UserCountry"
	 *
	 * @return string
	 */
	final public function getPluginName()
	{
		return Piwik::unprefixClass(get_class($this));
	}

	/**
	 * Returns the plugin's base class name without the "Piwik_" prefix,
	 * e.g., "UserCountry" when the plugin class is "Piwik_UserCountry"
	 *
	 * @deprecated since 1.2 - for backward compatibility
	 *
	 * @return string
	 */
	final public function getClassName()
	{
		return $this->getPluginName();
	}
}
