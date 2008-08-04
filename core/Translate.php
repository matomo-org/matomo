<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: Translate.php 526 2008-06-25 23:57:04Z matt $
 * 
 * @package Piwik
 */

/**
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
		$translations = array();
		
		$language = $this->getFallbackLanguageToLoad();
		require_once "lang/" . $language .".php";
		$this->addTranslationArray($translations);
		
		$language = $this->getLanguageToLoad();
		require_once "lang/" . $language .".php";
		$this->addTranslationArray($translations);
		
		setlocale(LC_ALL, $GLOBALS['Piwik_translations']['General_Locale']);
	}
	
	public function addTranslationArray($translation)
	{
		if(!isset($GLOBALS['Piwik_translations']))
		{
			$GLOBALS['Piwik_translations'] = array();
		}
		// we could check that no string overlap here
		$GLOBALS['Piwik_translations'] = array_merge($GLOBALS['Piwik_translations'], $translation);
	}
	
	/**
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
	
	protected function getFallbackLanguageToLoad()
	{
		return Zend_Registry::get('config')->Language->fallback;
	}
	
	/**
	 * Generate javascript translations array
	 * 
	 * @return string containing javascript code with translations array (including <script> tag)
	 *
	 */
	public function getJavascriptTranslations($moduleList)
	{
		if( !$moduleList )
		{
			return '';
		}
		
		$js = 'var translations = {';
					
		$moduleRegex = '#^(';
		foreach($moduleList as $module)
		{
			$moduleRegex .= $module.'|'; 
		}
		$moduleRegex = substr($moduleRegex, 0, -1);
		$moduleRegex .= ')_([^_]+)_js$#i';
		
		foreach($GLOBALS['Piwik_translations'] as $key => $value)
		{
			$matches = array();
			
			if( preg_match($moduleRegex,$key,$matches) ) {
				$varName = $matches[1].'_'.$matches[2];
				$varValue = $value;
				
				$js .= "".$varName.": '".str_replace("'","\\'",$varValue)."',";
			}
			
			$matches = null;
		}
		$js = substr($js,0,-1);
		$js .= '};';
		$js .= 'function _pk_translate(tvar, str) { '.
			'var s = str; if( typeof(translations[tvar]) != \'undefined\' ) s = translations[tvar];'.
			'return s;}';
		
		return $js;
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


/**
 * Returns translated string or given message if translation is not found.
 * This function does not throw any exception. Use it to translate exceptions.
 *
 * @param string Translation string index
 * @return string
 */
function Piwik_TranslateException($message)
{
	try {
		return Piwik_Translate($message);		
	}
	catch(Exception $e) {
		return $message;
	}
}


