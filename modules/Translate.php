<?php
/**
 * 
 * @package Piwik
 */
class Piwik_Translate
{
	static private $instance = null;
	
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
	 * @return unknown
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

