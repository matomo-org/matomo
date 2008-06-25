<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
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
	 */
	abstract function getInformation();
	
	public function registerTranslation( $langCode )
	{
		// we are certainly in LogStats mode, Zend is not loaded
		if(!class_exists('Zend_Loader'))
		{
			return ;
		}
		
		$infos = $this->getInformation();		
		if(!isset($infos['translationAvailable']))
		{
			$infos['translationAvailable'] = false;
		}
		$translationAvailable = $infos['translationAvailable'];
		
		if(!$translationAvailable)
		{
			return;
		}
		
		$name = $infos['name'];
		$path = "plugins/" . $name ."/lang/%s.php";
		
		$defaultLangPath = sprintf($path, $langCode);
		$defaultEnglishLangPath = sprintf($path, 'en');
		
		$translations = array();
				
		if(Zend_Loader::isReadable($defaultLangPath))
		{
			require $defaultLangPath;
		}
		elseif(Zend_Loader::isReadable($defaultEnglishLangPath))
		{
			require $defaultEnglishLangPath;
		}
		else
		{
			throw new Exception("Language file not found for the plugin '$name'.");
		}
		
		Piwik_Translate::getInstance()->addTranslationArray($translations);
	}
	
	/**
	 * Returns the plugin name
	 */
	public function getName()
	{
		$info = $this->getInformation();
		return $info['name'];
	}
	
	/**
	 * Returns the list of hooks registered with the methods names
	 */
	function getListHooksRegistered()
	{
		return array();
	}
	
	/**
	 * Returns the names of the required plugins
	 */
	public function getListRequiredPlugins()
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
}

