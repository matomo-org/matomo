<?php
if(!defined("PIWIK_PATH_TEST_TO_ROOT")) {
	define('PIWIK_PATH_TEST_TO_ROOT', getcwd().'/../..');
}
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once PIWIK_PATH_TEST_TO_ROOT . "/tests/config_test.php";
}

require_once 'Database.test.php';

class Test_Piwik_TablePartitioning extends Test_Database
{
    public function setUp()
	{
		parent::setUp();
		Piwik_TablePartitioning::$tablesAlreadyInstalled = null;
	}
	
    // test no timestamp  => exception
    function test_noTimestamp()
    {
    	$p = new Piwik_TablePartitioning_Monthly('testtable');
    	
    	try {
    		$p->getTableName();
        	$this->fail("Exception not raised.");
    	}
    	catch (Exception $expected) {
            return;
        }
    }
	
	// test table absent  => create
    function test_noTable()
    {
    	$tableName ='archive_numeric';
    	$p = new Piwik_TablePartitioning_Monthly($tableName);
    	$timestamp = strtotime("10 September 2000");
    	$suffixShouldBe = "_2000_09";
		$config = Zend_Registry::get('config');
		$prefixTables = $config->database->tables_prefix;
		$tablename = $prefixTables.$tableName.$suffixShouldBe;
		
    	$p->setTimestamp( $timestamp );
    	
    	$allTablesInstalled = Piwik::getTablesInstalled($forceReload = true);
    	
    	$this->assertTrue( in_array($tablename, $allTablesInstalled), "$tablename !==".var_export($allTablesInstalled,true));
    	$this->assertTrue( $tablename, $p->getTableName());
    	$this->assertEqual( $tablename, (string)$p->__toString());
    }
	
	// test monthly
    function test_monthlyPartition()
    {
    	
    	$tableName ='archive_numeric';
    	$p = new Piwik_TablePartitioning_Monthly($tableName);
    	$timestamp = strtotime("10 September 2000");
    	$suffixShouldBe = "_2000_09";
		$config = Zend_Registry::get('config');
		$prefixTables = $config->database->tables_prefix;
		$tablename = $prefixTables.$tableName.$suffixShouldBe;
		
    	$p->setTimestamp( $timestamp );
    	
    	$allTablesInstalled = Piwik::getTablesInstalled( $forceReload = true );
    	$this->assertTrue( in_array($tablename, $allTablesInstalled));
    	$this->assertTrue( $tablename, $p->getTableName());
    	$this->assertEqual( $tablename, (string)$p->__toString());
    }
        
	// test daily
    function test_dailyPartition()
    {
    	
    	$tableName ='archive_numeric';
    	$p = new Piwik_TablePartitioning_Daily($tableName);
    	$timestamp = strtotime("10 September 2000");
    	$suffixShouldBe = "_2000_09_10";
		$config = Zend_Registry::get('config');
		$prefixTables = $config->database->tables_prefix;
		$tablename = $prefixTables.$tableName.$suffixShouldBe;
		
    	$p->setTimestamp( $timestamp );
    	
    	$allTablesInstalled = Piwik::getTablesInstalled();
    	$this->assertTrue( in_array($tablename, $allTablesInstalled));
    	$this->assertTrue( $tablename, $p->getTableName());
    	$this->assertEqual( $tablename, (string)$p->__toString());
    }
    
}

