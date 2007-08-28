<?php
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
		//TODO check that no string overlap?
		$GLOBALS['Piwik_translations'] = array_merge($GLOBALS['Piwik_translations'], $translation);
	}
	
	public function getLanguageToLoad()
	{
		$language = Zend_Registry::get('config')->Language->current;
		
		//TODO checker that it is safe
		
		return $language;
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

