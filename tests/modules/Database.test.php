<?php
if(!defined("PATH_TEST_TO_ROOT")) {
	define('PATH_TEST_TO_ROOT', '../..');
}
require_once PATH_TEST_TO_ROOT ."/tests/config_test.php";

Mock::generate('Piwik_Access');

class Test_Database extends UnitTestCase
{
	function __construct( $title = '')
	{
		parent::__construct( $title );
	}
	
	public function setUp()
	{
		print("Setup database...");
		Piwik::createConfigObject();
		
		// setup database	
		Piwik::createDatabaseObject();
		
		Zend_Registry::get('config')->setTestEnvironment();	
		Piwik::createDatabase();
		Piwik::createDatabaseObject();
		
		Piwik::createTables();
		
	}
	
	public function tearDown()
	{
		print("TearDown database...");
		Piwik::dropDatabase();
	}
}

