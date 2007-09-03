<?php

/**
 * Abstract class to define a Piwik_Plugin.
 * Any plugin has to at least implement the abstract methods of this class.
 * 
 * @package Piwik
 */
abstract class Piwik_Plugin
{
	function __construct()
	{
	}
	
	public function registerTranslation( $langCode )
	{
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
		$path = PIWIK_INCLUDE_PATH . "/plugins/" . $name ."/lang/%s.php";
		
		$defaultLangPath = sprintf($path, $langCode);
		$defaultEnglishLangPath = sprintf($path, 'en');
		
		$translations = array();
		if(is_readable($defaultLangPath))
		{
			require $defaultLangPath;
		}
		elseif(is_readable($defaultEnglishLangPath))
		{
			require $defaultEnglishLangPath;
		}
		else
		{
			throw new Exception("The language file couldn't be find for this plugin '$name'.");
		}
		
		Piwik_Translate::getInstance()->addTranslationArray($translations);
	}
	
	/**
	 * Returns the plugin details
	 */
	abstract function getInformation();
	
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

