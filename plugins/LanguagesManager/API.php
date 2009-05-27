<?php
/**
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
	static protected $availableLanguageNames = null;
	static protected $languageNames = null;
	
	static public function isLanguageAvailable($languageCode)
	{
		return $languageCode !== false
			&& in_array($languageCode, self::getAvailableLanguages());
	}
	
	static public function getAvailableLanguages()
	{
		if(!is_null(self::$languageNames))
		{
			return self::$languageNames;
		}
		$path = PIWIK_INCLUDE_PATH . "/lang/";
		$languages = glob($path . "*.php");
		$pathLength = strlen($path);
		$languageNames = array();
		foreach($languages as $language) 
		{
			$languageNames[] = substr($language, $pathLength, -strlen('.php'));
		}
		self::$languageNames = $languageNames;
		return $languageNames;
	}

	static public function getAvailableLanguagesInfo()
	{
		require PIWIK_INCLUDE_PATH . "/lang/en.php";
		$englishTranslation = $translations;
		$filenames = self::getAvailableLanguages();
		$languagesInfo = array();
		foreach($filenames as $filename) 
		{
			require PIWIK_INCLUDE_PATH . "/lang/$filename.php";
			$translationStringsDone = array_intersect_key($englishTranslation, $translations);
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
	
	static public function getAvailableLanguageNames()
	{
		if(!is_null(self::$availableLanguageNames))
		{
			return self::$availableLanguageNames;
		}
		
		$filenames = self::getAvailableLanguages();
		$languagesInfo = array();
		foreach($filenames as $filename) 
		{
			require PIWIK_INCLUDE_PATH . "/lang/$filename.php";
			$languagesInfo[] = array( 'code' => $filename, 'name' => $translations['General_OriginalLanguageName']);
		}
		self::$availableLanguageNames = $languagesInfo;
		return self::$availableLanguageNames;
	}
	
	static public function getTranslationsForLanguage($languageCode)
	{
		if(!self::isLanguageAvailable($languageCode))
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
	 * @param string $login
	 * @param string|false $layout
	 */
	static public function getLanguageForUser( $login )
	{
		Piwik::checkUserIsSuperUserOrTheUser($login);
		return Piwik_FetchOne('SELECT language FROM '.Piwik::prefixTable('user_language') .
					' WHERE login = ? ', array($login ));
	}
	
	static public function setLanguageForUser($login, $language)
	{
		Piwik::checkUserIsSuperUserOrTheUser($login);
		$paramsBind = array($login, $language, $language);
		Piwik_Query('INSERT INTO '.Piwik::prefixTable('user_language') .
					' (login, language)
						VALUES (?,?)
					ON DUPLICATE KEY UPDATE language=?',
					$paramsBind);
	}
}
