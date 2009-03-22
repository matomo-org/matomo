<?php
if(!defined("PATH_TEST_TO_ROOT")) {
	define('PATH_TEST_TO_ROOT', getcwd().'/../..');
}
if(!defined('CONFIG_TEST_INCLUDED'))
{
	require_once PATH_TEST_TO_ROOT . "/tests/config_test.php";
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
		$languages = Piwik_LanguagesManager_API::getAvailableLanguages();
		foreach($languages as $language)
		{
			$strings = Piwik_LanguagesManager_API::getTranslationsForLanguage($language);
		}
		$this->pass();
	}
	
	//test language when it's not defined
	function test_getTranslationsForLanguages_not()
	{
		$this->assertFalse(Piwik_LanguagesManager_API::getTranslationsForLanguage("../no-language"));
	}
}