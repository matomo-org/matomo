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
	private $englishLanguageLoaded = false;
	
	/**
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

	public function loadEnglishTranslation()
	{
		require "lang/en.php";
		$this->mergeTranslationArray($translations);
		$this->setLocale();
		$this->englishLanguageLoaded = true;
	}

	public function loadUserTranslation()
	{
		$language = $this->getLanguageToLoad();
		if($language === 'en' 
			&& $this->englishLanguageLoaded)
		{
			return;
		}
		
		require "lang/" . $language . ".php";
		$this->mergeTranslationArray($translations);
		$this->setLocale();
	}
	
	public function mergeTranslationArray($translation)
	{
		if(!isset($GLOBALS['Piwik_translations']))
		{
			$GLOBALS['Piwik_translations'] = array();
		}
		// we could check that no string overlap here
		$GLOBALS['Piwik_translations'] = array_merge($GLOBALS['Piwik_translations'], $translation);
	}
	
	/**
	 * @return string the language filename prefix, eg 'en' for english
	 * @throws exception if the language set is not a valid filename
	 */
	public function getLanguageToLoad()
	{
		static $language = null;
		if(!is_null($language))
		{
			return $language;
		}
		Piwik_PostEvent('Translate.getLanguageToLoad', $language);
		
		if(is_null($language) || empty($language))
		{
			$language = Zend_Registry::get('config')->General->default_language;
		}
		if( Piwik_Common::isValidFilename($language))
		{
			return $language;
		}
		else
		{
			throw new Exception("The language selected ('$language') is not a valid language file ");
		}
	}
	
	/**
	 * Generate javascript translations array
	 * 
	 * @return string containing javascript code with translations array (including <script> tag)
	 */
	public function getJavascriptTranslations(array $moduleList)
	{
		if( empty($moduleList) )
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
		$js .=	'if(typeof(piwik_translations) == \'undefined\') { var piwik_translations = new Object; }'.
				'for(var i in translations) { piwik_translations[i] = translations[i];} ';
		$js .= 'function _pk_translate(tvar, str) { '.
			'var s = str; if( typeof(piwik_translations[tvar]) != \'undefined\' ){  s = piwik_translations[tvar]; }'.
			'return s;}';
		
		return $js;
	}

	private function setLocale()
	{
		setlocale(LC_ALL, $GLOBALS['Piwik_translations']['General_Locale']);
	}
}

function Piwik_Translate($index, $args = array())
{
	if(!is_array($args))
	{
		$args = array($args);
	}
	if(isset($GLOBALS['Piwik_translations'][$index]))
	{
		$string = $GLOBALS['Piwik_translations'][$index];
		if(count($args) == 0) 
		{
			return $string;
		}
		else
		{
			return vsprintf($string, $args);
		}
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
function Piwik_TranslateException($message, $args = array())
{
	try {
		return Piwik_Translate($message, $args);		
	} 
	catch(Exception $e) {
		return $message;
	}
}


