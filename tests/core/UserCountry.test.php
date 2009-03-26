<?php
if(!defined("PATH_TEST_TO_ROOT")) {
	define('PATH_TEST_TO_ROOT', getcwd().'/../..');
}
if(!defined('CONFIG_TEST_INCLUDED'))
{
	require_once PATH_TEST_TO_ROOT . "/tests/config_test.php";
}

require 'UserCountry/functions.php';

class Test_Piwik_UserCountry extends UnitTestCase
{
	public function test_getFlagFromCode()
	{
		$flag = Piwik_getFlagFromCode("us");
		$this->assertEqual( basename($flag), "us.png" );
	}

	public function test_getFlagFromInvalidCode()
	{
		$flag = Piwik_getFlagFromCode("foo");
		$this->assertEqual( basename($flag), "xx.png" );
	}
}

