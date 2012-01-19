<?php
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once dirname(__FILE__)."/../../../tests/config_test.php";
}

class Test_Piwik_ExamplePlugin extends UnitTestCase
{
	function setUp()
	{
		$path = dirname(__FILE__).'/../config/local.config.php';
		if (file_exists($path))
		{
			@unlink($path);
		}

		$this->assertFalse(file_exists($path), 'unable to remove local.config.php');
	}

	function test_load_with_no_config()
	{
		$objectUnderTest = new Piwik_Plugin_Config('ExamplePlugin');

		$this->assertFalse($objectUnderTest->load(), 'load() with no config should fail');
	}

	function test_load_alternate_path()
	{
		$objectUnderTest = new Piwik_Plugin_Config('ExamplePlugin', 'local.config.sample.php');
		$config = $objectUnderTest->load();

		$this->assertTrue($config !== false);
		$this->assertTrue($config['id'] === 'Example');
		$this->assertTrue($config['name'] === 'ExamplePlugin');
		$this->assertTrue($config['description'] === 'This is an example');
	}

	function test_load()
	{
		$dir = dirname(__FILE__).'/../config';
		@copy($dir . '/local.config.sample.php', $dir . '/local.config.php');

		$objectUnderTest = new Piwik_Plugin_Config('ExamplePlugin');
		$config = $objectUnderTest->load();

		$this->assertTrue($config !== false);
		$this->assertTrue($config['id'] === 'Example');
		$this->assertTrue($config['name'] === 'ExamplePlugin');
		$this->assertTrue($config['description'] === 'This is an example');
	}

	function test_store()
	{
		$config = array(
			1, 'mixed', array('a'), 'b' => 'c'
		);

		$objectUnderTest = new Piwik_Plugin_Config('ExamplePlugin');
		$objectUnderTest->store($config);

		$path = dirname(__FILE__).'/../config/local.config.php';
		$this->assertTrue(file_exists($path));

		$objectUnderTest = new Piwik_Plugin_Config('ExamplePlugin');
		$newConfig = $objectUnderTest->load();

		$this->assertTrue($config !== false);
		$this->assertTrue($config[0] === 1);
		$this->assertTrue($config[1] === 'mixed');
		$this->assertTrue(is_array($config[2]) && $config[2][0] === 'a');
		$this->assertTrue($config['b'] === 'c');
	}
}
