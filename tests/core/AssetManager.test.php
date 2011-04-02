<?php
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once dirname(__FILE__)."/../../tests/config_test.php";
}

class Test_Piwik_AssetManager extends UnitTestCase
{
	function __construct( $title = '')
	{
		parent::__construct( $title );
	}
	
	public function setUp()
	{
		parent::setUp();
	}
	
	public function tearDown()
	{
		parent::tearDown();
	}
    
    public function test_prioritySort()
    {
		$buckets = array(
			'themes/base.css',
			'themes/',
			'libs/base.css',
			'libs/',
			'plugins/',
		);

		$data = array(
			'plugins/xyz',
			'plugins/abc',
			'themes/base.css',
			'libs/xyz',
			'libs/base.css',
			'libs/abc',
			'plugins/xyz',
			'themes/test',
			'libs/xyz',
		);

		$expected = array(
			'themes/base.css',
			'themes/test',
			'libs/base.css',
			'libs/xyz',
			'libs/abc',
			'plugins/xyz',
			'plugins/abc',
		);

		$this->assertTrue(Piwik_AssetManager::prioritySort($buckets, $data) == $expected);
    }
}
