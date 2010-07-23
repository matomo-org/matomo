<?php
if(!defined("PIWIK_PATH_TEST_TO_ROOT")) {
	define('PIWIK_PATH_TEST_TO_ROOT', getcwd().'/../..');
}
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once PIWIK_PATH_TEST_TO_ROOT . "/tests/config_test.php";
}

require_once 'ScheduledTask.php';

class Test_Piwik_ScheduledTask extends UnitTestCase
{
	public function test_getClassName()
	{
		$scheduledTask = new Piwik_ScheduledTask ( "className", null, null );
		$className = $scheduledTask->getClassName();
		$this->assertTrue( is_string($className) && !empty($className) );
	}
	
	public function test_getMethodName()
	{
		$scheduledTask = new Piwik_ScheduledTask ( null, "methodname", null );
		$methodName = $scheduledTask->getMethodName();
		$this->assertTrue( is_string($methodName) && !empty($methodName) );
	}
	
	public function test_getScheduledTime()
	{
		$scheduledTask = new Piwik_ScheduledTask ( null, null, new Piwik_ScheduledTime_Hourly() );
		$scheduledTime = $scheduledTask->getScheduledTime();
		$this->assertTrue( get_class($scheduledTime) == "Piwik_ScheduledTime_Hourly" );
	}	
}