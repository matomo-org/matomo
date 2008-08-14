<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: ExamplePlugin.php 169 2008-01-14 05:41:15Z matt $
 * 
 * @package Piwik_LanguageManager
 */

class Piwik_LanguagesManager extends Piwik_Plugin
{
	static protected $languagesAvailable = null;
	
	public function getInformation()
	{
		return array(
			'name' => 'Languages Management',
			'description' => 'This plugin will display a list of the available languages for the Piwik interface. The language selected will be saved in the preferences for each user.',
			'author' => 'Piwik',
			'homepage' => 'http://piwik.org/',
			'version' => '0.1',
		);
	}

	public function getListHooksRegistered()
	{
		return array( 
			'template_js_import' => 'js',
			'template_css_import' => 'css',
			'template_topBar' => 'showLanguagesSelector',
			'Translate.getLanguageToLoad' => 'getLanguageToLoad',
		);
	}

	function js()
	{
		echo '<script type="text/javascript" src="plugins/LanguagesManager/templates/fdd2div-modified.js"></script>';
	}

	function css()
	{
		echo '<link rel="stylesheet" type="text/css" href="plugins/LanguagesManager/templates/styles.css" />';
	}
	
	function showLanguagesSelector()
	{
		$view = new Piwik_View("LanguagesManager/templates/languages.tpl");
		$view->languages = self::getListAvailableLanguages();
		$view->currentLanguage = self::getLanguageForCurrentUser();
		echo $view ->render();
	}
	
	function getLanguageToLoad($notification)
	{
		$language =& $notification->getNotificationObject();
		$language = self::getLanguageForCurrentUser();
	}
	
	/**
	 * @return string $language
	 */
	static public function getLanguageForCurrentUser()
	{
		$languageCode = self::getLanguageFromPreferences();
		if(!self::isLanguageAvailable($languageCode))
		{
			$languageCode = Piwik_Common::extractLanguageCodeFromBrowserLanguage(Piwik_Common::getBrowserLanguage(), array_keys(self::getListAvailableLanguages()));
		}
		if(!self::isLanguageAvailable($languageCode))
		{
			$languageCode = 'en';
		}
		return $languageCode;
	}

	static public function isLanguageAvailable($languageCode)
	{
		return $languageCode !== false
			&& in_array($languageCode, array_keys(self::getListAvailableLanguages()));
	}
	
	static public function getListAvailableLanguages()
	{
		if(!is_null(self::$languagesAvailable))
		{
			return self::$languagesAvailable;
		}
		
		$pathToLang = 'lang/';
		$languages = glob( $pathToLang . "*");
		$languagesInfo = array();
		foreach($languages as $language) 
		{
			require $language;
			$languagePrefix = substr($language, strlen($pathToLang), -strlen('.php'));
			$languagesInfo[$languagePrefix] = $translations['General_OriginalLanguageName'];
		}
		asort($languagesInfo);
		self::$languagesAvailable = $languagesInfo;
		return self::$languagesAvailable;
	}
	
	static protected function getLanguageFromPreferences()
	{
		$currentUser = Piwik::getCurrentUserLogin();
		if($currentUser == 'anonymous')
		{
			if(!isset($_SESSION['language']))
			{
				return false;
			}
			return $_SESSION['language'];
		}
		else
		{
			return self::getLanguageForUser($currentUser);
		}
	}
	
	/**
	 * @param string $login
	 * @param string|false $layout
	 */
	static protected function getLanguageForUser( $login )
	{
		return Piwik_FetchOne('SELECT language FROM '.Piwik::prefixTable('user_language') .
					' WHERE login = ? ', array($login ));
	}
	
	public function install()
	{
		// we catch the exception
		try{
			$sql = "CREATE TABLE ". Piwik::prefixTable('user_language')." (
					login VARCHAR( 20 ) NOT NULL ,
					language VARCHAR( 10 ) NOT NULL ,
					PRIMARY KEY ( login )
					) " ;
			Piwik_Query($sql);
		} catch(Zend_Db_Statement_Exception $e){
			// mysql code error 1050:table already exists
			// see bug #153 http://dev.piwik.org/trac/ticket/153
			if(ereg('1050',$e->getMessage()))
			{
				return;
			}
			else
			{
				throw $e;
			}
		}
	}
	
	public function uninstall()
	{
		$sql = "DROP TABLE ". Piwik::prefixTable('user_language') ;
		Piwik_Query($sql);		
	}
	
}

