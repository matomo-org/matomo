<?php
if(!defined("PATH_TEST_TO_ROOT")) {
	define('PATH_TEST_TO_ROOT', '..');
}
if(!defined('CONFIG_TEST_INCLUDED'))
{
	require_once PATH_TEST_TO_ROOT ."/../tests/config_test.php";
}


require_once 'Date.php';

class Test_Piwik_Date extends UnitTestCase
{
	function __construct( $title = '')
	{
		parent::__construct( $title );
	}
	
	public function setUp()
	{
	}
	
	public function tearDown()
	{
	}
	
	//create today object check that timestamp is correct (midnight)
	function test_today()
	{
		$date = Piwik_Date::today();
		$this->assertEqual( strtotime(date("Y-m-d "). " 00:00:00"), $date->get());
	}
	//create today object check that timestamp is correct (midnight)
	function test_yesterday()
	{
		$date = Piwik_Date::yesterday();
		$this->assertEqual( strtotime(date("Y-m-d",time()-86400). " 00:00:00"), $date->get());
	}
}

