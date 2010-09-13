<?php
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once dirname(__FILE__)."/../../../tests/config_test.php";
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

