<?php
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once dirname(__FILE__)."/../../../tests/config_test.php";
}

require_once 'UserCountry/functions.php';

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

	public function test_flagsAndContinents()
	{
		require_once PIWIK_PATH_TEST_TO_ROOT . '/core/DataFiles/Countries.php';

		$continents = $GLOBALS['Piwik_ContinentList'];
		$countries = array_merge($GLOBALS['Piwik_CountryList'], $GLOBALS['Piwik_CountryList_Extras']);

		// Get list of existing flag icons
		$flags = scandir(PIWIK_PATH_TEST_TO_ROOT . '/plugins/UserCountry/flags/');

		// Get list of countries
		foreach($countries as $country => $continent)
		{
			// test continent
			$this->assertTrue(in_array($continent, $continents), "$country => $continent");

			// test flag
			$this->assertTrue(in_array($country . '.png', $flags), $country);
		}

		foreach($flags as $filename)
		{
			if($filename == '.' || $filename == '..' || $filename == '.svn')
			{
				continue;
			}

			$country = substr($filename, 0, strpos($filename, '.png'));

			// test country
			$this->assertTrue(array_key_exists($country, $countries), $filename);
		}
	}
}

