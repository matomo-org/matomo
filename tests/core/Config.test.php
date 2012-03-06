<?php
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once dirname(__FILE__)."/../../tests/config_test.php";
}

class Test_Piwik_Config extends UnitTestCase
{
	public function testUserConfigOverwritesSectionGlobalConfigValue()
	{
		$userFile = PIWIK_INCLUDE_PATH . '/tests/resources/Config/config.ini.php';
		$globalFile = PIWIK_INCLUDE_PATH . '/tests/resources/Config/global.ini.php';

		$config = Piwik_Config::getInstance();
		$config->setTestEnvironment($userFile, $globalFile);
		$config->init();

		$this->assertEqual($config->Category['key1'], "value_overwritten");
		$this->assertEqual($config->Category['key2'], "value2");
		$this->assertEqual($config->GeneralSection['login'], 'tes"t');
		$this->assertEqual($config->CategoryOnlyInGlobalFile['key3'], "value3");
		$this->assertEqual($config->CategoryOnlyInGlobalFile['key4'], "value4");

		$expectedArray = array('plugin"1', 'plugin2', 'plugin3');
		$array = $config->TestArray;
		$this->assertEqual($array['installed'], $expectedArray);

		$expectedArray = array('value1', 'value2');
		$array = $config->TestArrayOnlyInGlobalFile;
		$this->assertEqual($array['my_array'], $expectedArray);
	}

	public function testWritingConfigWithSpecialCharacters()
	{
		$userFile = PIWIK_INCLUDE_PATH . '/tests/resources/Config/config.written.ini.php';
		$globalFile = PIWIK_INCLUDE_PATH . '/tests/resources/Config/global.ini.php';

		$config = Piwik_Config::getInstance();
		$config->setTestEnvironment($userFile, $globalFile);
		$config->init();

		$stringWritten = '&6^ geagea\'\'\'";;&';
		$config->Category = array('test' => $stringWritten);
		$this->assertEqual($config->Category['test'], $stringWritten);
		unset($config);

		$config = Piwik_Config::getInstance();
		$config->setTestEnvironment($userFile, $globalFile);
		$config->init();

		$this->assertEqual($config->Category['test'], $stringWritten);
		$config->Category = array(
			'test' => $config->Category['test'],
			'test2' => $stringWritten,
		);
		$this->assertEqual($config->Category['test'], $stringWritten);
		$this->assertEqual($config->Category['test2'], $stringWritten);
	}

	public function test_UserConfigOverwritesGlobalConfig()
	{
		$userFile = PIWIK_PATH_TEST_TO_ROOT . '/tests/resources/Config/config.ini.php';
		$globalFile = PIWIK_PATH_TEST_TO_ROOT . '/tests/resources/Config/global.ini.php';

		$config = Piwik_Config::getInstance();
		$config->setTestEnvironment($userFile, $globalFile);

		$this->assertEqual($config->Category['key1'], "value_overwritten");
		$this->assertEqual($config->Category['key2'], "value2");
		$this->assertEqual($config->GeneralSection['login'], "tes\"t");
		$this->assertEqual($config->CategoryOnlyInGlobalFile['key3'], "value3");
		$this->assertEqual($config->CategoryOnlyInGlobalFile['key4'], "value4");

		$expectedArray = array('plugin"1', 'plugin2', 'plugin3');
		$array = $config->TestArray;
		$this->assertEqual($array['installed'], $expectedArray);

		$expectedArray = array('value1', 'value2');
		$array = $config->TestArrayOnlyInGlobalFile;
		$this->assertEqual($array['my_array'], $expectedArray);

		Piwik_Config::getInstance()->clear();
	}

	public function test_compareElements()
	{
		$tests = array(
			'string = string' => array(
				'a', 'a', 0,
			),
			'string > string' => array(
				'b', 'a', 1,
			),
			'string < string' => array(
				'a', 'b', -1,
			),
			'string vs array' => array(
				'a', array('a'), -1,
			),
			'array vs string' => array(
				array('a'), 'a', 1,
			),
			'array = array' => array(
				array('a'), array('a'), 0,
			),
			'array > array' => array(
				array('b'), array('a'), 1,
			),
			'array < array' => array(
				array('a'), array('b'), -1,
			),
		);

		foreach ($tests as $description => $test)
		{
			list($a, $b, $expected) = $test;

			$result = Piwik_Config::compareElements($a, $b);
			$this->assertEqual($result, $expected, $description);
		}
	}

	public function test_array_unmerge()
	{
		$tests = array(
			'description of test' => array(
				array(),
				array(),
			),
			'override with empty' => array(
				array('login' => 'root', 'password' => 'b33r'),
				array('password' => ''),
			),
			'override with non-empty' => array(
				array('login' => 'root', 'password' => ''),
				array('password' => 'b33r'),
			),
			'add element' => array(
				array('login' => 'root', 'password' => ''),
				array('auth' => 'Login'),
			),
			'override with empty array' => array(
				array('headers' => ''),
				array('headers' => array()),
			),
			'override with array' => array(
				array('headers' => ''),
				array('headers' => array('Content-Length', 'Content-Type')),
			),
			'override an array' => array(
				array('headers' => array()),
				array('headers' => array('Content-Length', 'Content-Type')),
			),
			'override similar arrays' => array(
				array('headers' => array('Content-Length', 'Set-Cookie')),
				array('headers' => array('Content-Length', 'Content-Type')),
			),
			'override dyslexic arrays' => array(
				array('headers' => array('Content-Type', 'Content-Length')),
				array('headers' => array('Content-Length', 'Content-Type')),
			),
		);

		$configWriter = Piwik_Config::getInstance();

		foreach ($tests as $description => $test)
		{
			list($a, $b) = $test;

			$combined = array_merge($a, $b);

			$diff = $configWriter->array_unmerge($a, $combined);

			// expect $b == $diff
			$this->assertEqual(serialize($b), serialize($diff), $description);
		}
	}
}
