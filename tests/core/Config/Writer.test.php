<?php
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once dirname(__FILE__)."/../../../tests/config_test.php";
}

class Test_Piwik_Config_Writer extends UnitTestCase
{
	public function test_getInstance()
	{
		$this->assertEqual(get_class(Piwik_Config_Writer::getInstance()), 'Piwik_Config_Writer');
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

			$result = Piwik_Config_Writer::compareElements($a, $b);
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

		$configWriter = Piwik_Config_Writer::getInstance();

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
