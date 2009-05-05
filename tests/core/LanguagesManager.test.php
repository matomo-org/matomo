<?php
if(!defined("PIWIK_PATH_TEST_TO_ROOT")) {
	define('PIWIK_PATH_TEST_TO_ROOT', getcwd().'/../..');
}
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once PIWIK_PATH_TEST_TO_ROOT . "/tests/config_test.php";
}

require_once 'plugins/LanguagesManager/API.php';

class Test_Languages_Manager extends UnitTestCase
{
	function __construct( $title = '')
	{
		parent::__construct( $title );
	}
	
	// test all languages
	function test_getTranslationsForLanguages()
	{
		// we also test that none of the language php files outputs any character on the screen (eg. space before the <?php)
		$languages = Piwik_LanguagesManager_API::getAvailableLanguages();
		foreach($languages as $language)
		{
			ob_start(); 
			$strings = Piwik_LanguagesManager_API::getTranslationsForLanguage($language);
			$content = ob_get_flush();
			$this->assertTrue(count($strings) > 100); // at least 100 translations in the language file
			$this->assertTrue(strlen($content) == 0, "buffer was ".strlen($content)." long but should be zero. Translation file for '$language' must be buggy.");
		}
		$this->pass();
	}
	
	//test language when it's not defined
	function test_getTranslationsForLanguages_not()
	{
		$this->assertFalse(Piwik_LanguagesManager_API::getTranslationsForLanguage("../no-language"));
	}
}