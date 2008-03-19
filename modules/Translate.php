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
 * 
 * @package Piwik
 */
class Piwik_Translate
{
	static private $instance = null;
	
	/**
	 * Returns singleton
	 *
	 * @return Piwik_Translate
	 */
	static public function getInstance()
	{
		if (self::$instance == null)
		{			
			$c = __CLASS__;
			self::$instance = new $c();
		}
		return self::$instance;
	}
	
	private function __construct()
	{
		$GLOBALS['Piwik_translations'] = array();
		
		$language = $this->getLanguageToLoad();
		
		$translations = array();
		require_once PIWIK_INCLUDE_PATH . "/lang/" . $language .".php";
		
		$this->addTranslationArray($translations);
	}
	
	public function addTranslationArray($translation)
	{
		// we could check that no string overlap here
		$GLOBALS['Piwik_translations'] = array_merge($GLOBALS['Piwik_translations'], $translation);
	}
	
	/**
	 * Enter description here...
	 *
	 * @return string the language filename prefix, eg "en" for english
	 * @throws exception if the language set in the config file is not a valid filename
	 */
	public function getLanguageToLoad()
	{
		$language = Zend_Registry::get('config')->Language->current;
		
		if( Piwik_Common::isValidFilename($language))
		{
			return $language;
		}
		else
		{
			throw new Exception("The language selected ('$language') is not a valid language file ");
		}
	}
}

function Piwik_Translate($index)
{
	if(isset($GLOBALS['Piwik_translations'][$index]))
	{
		return $GLOBALS['Piwik_translations'][$index];
	}
	throw new Exception("Translation string '$index' not available.");
}



