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
}
