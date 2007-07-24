<?php
if(!defined("PATH_TEST_TO_ROOT")) {
	define('PATH_TEST_TO_ROOT', '../..');
}
require_once PATH_TEST_TO_ROOT ."/tests/config_test.php";

class Test_Database extends UnitTestCase
{
	function __construct( $title = '')
	{
		parent::__construct( $title );
	}
	
	function setUp()
	{
		Piwik::log("Setup database...");
		Piwik::createConfigObject();
		
		// setup database
		Piwik::createDatabaseObject();
		Zend_Registry::get('config')->setTestEnvironment();		
		
		Piwik::createDatabase();
		Piwik::createTables();
	}
	
	function tearDown()
	{
		Piwik::log("TearDown database...");
		Piwik::dropDatabase();
	}
}

