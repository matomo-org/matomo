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

	public function test_dumpConfig()
	{
		$header = <<<END_OF_HEADER
; <?php exit; ?> DO NOT REMOVE THIS LINE
; file automatically generated or modified by Piwik; you can manually override the default values in global.ini.php by redefining them in this file.

END_OF_HEADER;

		$tests = array(
			'global only, not cached' => array(
				array(),
				array('General' => array('debug' => '1')),
				array(),
				false,
			),

			'global only, cached get' => array(
				array(),
				array('General' => array('debug' => '1')),
				array('General' => array('debug' => '1')),
				false,
			),

			'global only, cached set' => array(
				array(),
				array('General' => array('debug' => '1')),
				array('General' => array('debug' => '2')),
				$header . "[General]\ndebug = 2\n\n",
			),

			'local copy (same), not cached' => array(
				array('General' => array('debug' => '1')),
				array('General' => array('debug' => '1')),
				array(),
				false,
			),

			'local copy (same), cached get' => array(
				array('General' => array('debug' => '1')),
				array('General' => array('debug' => '1')),
				array('General' => array('debug' => '1')),
				false,
			),

			'local copy (same), cached set' => array(
				array('General' => array('debug' => '1')),
				array('General' => array('debug' => '1')),
				array('General' => array('debug' => '2')),
				$header . "[General]\ndebug = 2\n\n",
			),

			'local copy (different), not cached' => array(
				array('General' => array('debug' => '2')),
				array('General' => array('debug' => '1')),
				array(),
				false,
			),

			'local copy (different), cached get' => array(
				array('General' => array('debug' => '2')),
				array('General' => array('debug' => '1')),
				array('General' => array('debug' => '2')),
				false,
			),

			'local copy (different), cached set' => array(
				array('General' => array('debug' => '2')),
				array('General' => array('debug' => '1')),
				array('General' => array('debug' => '3')),
				$header . "[General]\ndebug = 3\n\n",
			),


			'local copy, not cached, new section' => array(
				array('Tracker' => array('anonymize' => '1')),
				array('General' => array('debug' => '1')),
				array(),
				false,
			),

			'local copy, cached get, new section' => array(
				array('Tracker' => array('anonymize' => '1')),
				array('General' => array('debug' => '1')),
				array('Tracker' => array('anonymize' => '1')),
				false,
			),

			'local copy, cached set local, new section' => array(
				array('Tracker' => array('anonymize' => '1')),
				array('General' => array('debug' => '1')),
				array('Tracker' => array('anonymize' => '2')),
				$header . "[Tracker]\nanonymize = 2\n\n",
			),

			'local copy, cached set global, new section' => array(
				array('Tracker' => array('anonymize' => '1')),
				array('General' => array('debug' => '1')),
				array('General' => array('debug' => '2')),
				$header . "[General]\ndebug = 2\n\n[Tracker]\nanonymize = 1\n\n",
			),

			'sort, common sections' => array(
				array('Tracker' => array('anonymize' => '1'),
					  'General' => array('debug' => '1')),
				array('General' => array('debug' => '0'),
					  'Tracker' => array('anonymize' => '0')),
				array('Tracker' => array('anonymize' => '2')),
				$header . "[General]\ndebug = 1\n\n[Tracker]\nanonymize = 2\n\n",
			),

			'sort, common sections before new section' => array(
				array('Tracker' => array('anonymize' => '1'),
					  'General' => array('debug' => '1')),
				array('General' => array('debug' => '0'),
					  'Tracker' => array('anonymize' => '0')),
				array('Segment' => array('dimension' => 'foo')),
				$header . "[General]\ndebug = 1\n\n[Tracker]\nanonymize = 1\n\n[Segment]\ndimension = \"foo\"\n\n",
			),
			
			'change back to default' => array(
				array('Tracker' => array('anonymize' => '1')),
				array('Tracker' => array('anonymize' => '0'),
					  'General' => array('debug' => '1')),
				array('Tracker' => array('anonymize' => '0')),
				$header
			),
		);

		$config = Piwik_Config::getInstance();

		foreach ($tests as $description => $test)
		{
			list($configLocal, $configGlobal, $configCache, $expected) = $test;

			$output = $config->dumpConfig($configLocal, $configGlobal, $configCache);

			$this->assertEqual($output, $expected, $description);
			if ($output !== $expected)
			{
				var_dump($expected, $output);
			}
		}
	}
}
