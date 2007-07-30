<?php
if(!defined("PATH_TEST_TO_ROOT")) {
	define('PATH_TEST_TO_ROOT', '../..');
}
require_once PATH_TEST_TO_ROOT ."/tests/config_test.php";

class Test_Piwik_Common extends UnitTestCase
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
	
	// sanitize with magic quotes on
	// sanitize with magic quotes off
	// sanitize an array OK
	// sanitize an array with bad value level1
	// sanitize an array with bad value level2
	// sanitize a bad string
	// sanitize a bad integer
	public function test_sanitizeInputValues()
	{
	}
}
?>