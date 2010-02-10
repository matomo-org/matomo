<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @category Piwik_Plugins
 * @package Piwik_LanguagesManager
 * 
 */

/**
 *
 * @package Piwik_LanguagesManager
 */
class Piwik_LanguagesManager_API 
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

	protected $availableLanguageNames = null;
	protected $languageNames = null;
	
	/**
	 * Returns true if specified language is available
	 *
	 * @param string $languageCode
	 * @return bool true if language available; false otherwise
	 */
	public function isLanguageAvailable($languageCode)
	{
		return $languageCode !== false
			&& in_array($languageCode, $this->getAvailableLanguages());
	}
	
	/**
	 * Return array of available languages
	 *
	 * @return array Arry of strings, each containing its ISO language code
	 */
	public function getAvailableLanguages()
	{
		if(!is_null($this->languageNames))
		{
			return $this->languageNames;
		}
		$path = PIWIK_INCLUDE_PATH . "/lang/";
		$languages = glob($path . "*.php");
		$pathLength = strlen($path);
		$languageNames = array();
		foreach($languages as $language) 
		{
			$languageNames[] = substr($language, $pathLength, -strlen('.php'));
		}
		$this->languageNames = $languageNames;
		return $languageNames;
	}

	/**
	 * Return information on translations (code, language, % translated, etc)
	 *
	 * @return array Array of arrays
	 */
	public function getAvailableLanguagesInfo()
	{
		require PIWIK_INCLUDE_PATH . '/lang/en.php';
		$englishTranslation = $translations;
		$filenames = $this->getAvailableLanguages();
		$languagesInfo = array();
		foreach($filenames as $filename) 
		{
			require PIWIK_INCLUDE_PATH . "/lang/$filename.php";
			$translationStringsDone = array_intersect_key($englishTranslation, array_filter($translations, 'strlen'));
			$percentageComplete = count($translationStringsDone) / count($englishTranslation);
			$percentageComplete = round(100 * $percentageComplete, 0);  
			$languageInfo = array( 	'code' => $filename, 
										'name' => $translations['General_OriginalLanguageName'],
										'english_name' => $translations['General_EnglishLanguageName'],
										'translators' => $translations['General_TranslatorName'],
										'translators_email' => $translations['General_TranslatorEmail'],
										'percentage_complete' => $percentageComplete . '%',
							);
			$languagesInfo[] = $languageInfo;
		}
		return $languagesInfo;
	}
	
	/**
	 * Return array of available languages
	 *
	 * @return array Arry of array, each containing its ISO language code and name of the language
	 */ 
	public function getAvailableLanguageNames()
	{
		if(!is_null($this->availableLanguageNames))
		{
			return $this->availableLanguageNames;
		}
		
		$filenames = $this->getAvailableLanguages();
		$languagesInfo = array();
		foreach($filenames as $filename) 
		{
			require PIWIK_INCLUDE_PATH . "/lang/$filename.php";
			$languagesInfo[] = array( 'code' => $filename, 'name' => $translations['General_OriginalLanguageName']);
		}
		$this->availableLanguageNames = $languagesInfo;
		return $this->availableLanguageNames;
	}
	
	/**
	 * Returns translation strings by language
	 *
	 * @param string $languageCode ISO language code
	 * @return array|false Array of arrays, each containing 'label' (translation index)  and 'value' (translated string); false if language unavailable
	 */
	public function getTranslationsForLanguage($languageCode)
	{
		if(!$this->isLanguageAvailable($languageCode))
		{
			return false;
		}
		require PIWIK_INCLUDE_PATH . "/lang/$languageCode.php";
		$languageInfo = array();
		foreach($translations as $key => $value)
		{
			$languageInfo[] = array('label' => $key, 'value' => $value);
		}
		return $languageInfo;
	}
	
	/**
	 * Returns the language for the user
	 *
	 * @param string $login
	 * @param string|false $layout
	 */
	public function getLanguageForUser( $login )
	{
		Piwik::checkUserIsSuperUserOrTheUser($login);
		return Piwik_FetchOne('SELECT language FROM '.Piwik::prefixTable('user_language') .
					' WHERE login = ? ', array($login ));
	}
	
	/**
	 * Sets the language for the user
	 *
	 * @param string $login
	 * @param string $languageCode
	 */
	public function setLanguageForUser($login, $languageCode)
	{
		Piwik::checkUserIsSuperUserOrTheUser($login);
		$paramsBind = array($login, $languageCode, $languageCode);
		Piwik_Query('INSERT INTO '.Piwik::prefixTable('user_language') .
					' (login, language)
						VALUES (?,?)
					ON DUPLICATE KEY UPDATE language=?',
					$paramsBind);
	}

	/**
	 * Returns the langage for the session
	 *
	 * @return string|null
	 */
	public function getLanguageForSession()
	{
		$session = new Zend_Session_Namespace("Piwik_LanguagesManager");
		if(isset($session->language))
		{
			return $session->language;
		}
		return null;
	}

	/**
	 * Set the language for the session
	 *
	 * @param string $languageCode ISO language code
	 */
	public function setLanguageForSession($languageCode)
	{
		$session = new Zend_Session_Namespace("Piwik_LanguagesManager");
		$session->language = $languageCode;
	}
}
