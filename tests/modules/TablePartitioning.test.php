<?php
if(!defined("PATH_TEST_TO_ROOT")) {
	define('PATH_TEST_TO_ROOT', '..');
}
if(!defined('CONFIG_TEST_INCLUDED'))
{
	require_once PATH_TEST_TO_ROOT ."/../tests/config_test.php";
}
require_once "Database.test.php";

Zend_Loader::loadClass('Piwik_TablePartitioning');
class Test_Piwik_TablePartitioning extends Test_Database
{
    function __construct() 
    {
        parent::__construct('');
    }
    public function setUp()
	{
		parent::setUp();
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
    	$tableName ='log_visit';
    	$p = new Piwik_TablePartitioning_Monthly($tableName);
    	$timestamp = strtotime("10 September 2000");
    	$suffixShouldBe = "_2000_09";
		$config = Zend_Registry::get('config');
		$prefixTables = $config->database->tables_prefix;
		$tablename = $prefixTables.$tableName.$suffixShouldBe;
		
    	$p->setDate( $timestamp );
    	
    	$allTablesInstalled = Piwik::getTablesInstalled();
    	$this->assertTrue( !in_array($tablename, $allTablesInstalled));
    	$this->assertTrue( $tablename, $p->getTableName());
    	
    	$allTablesInstalled = Piwik::getTablesInstalled();
    	$this->assertTrue( in_array($tablename, $allTablesInstalled));
    	$this->assertEqual( $tablename, (string)$p);
    }
	
	// test table present => nothing
    function test_tablePresent()
    {
    	$tableName ='log_visit';
    	$p = new Piwik_TablePartitioning_Monthly($tableName);
    	$timestamp = strtotime("10 September 2000");
    	$suffixShouldBe = "_2000_09";
		$config = Zend_Registry::get('config');
		$prefixTables = $config->database->tables_prefix;
		$tablename = $prefixTables.$tableName.$suffixShouldBe;
		
		Zend_Registry::get('db')->query("CREATE TABLE $tablename (`test` VARCHAR( 255 ) NOT NULL)");

		$p->setDate( $timestamp );
    	
    	$allTablesInstalled = Piwik::getTablesInstalled();
    	$this->assertTrue( in_array($tablename, $allTablesInstalled));
    	$this->assertTrue( $tablename, $p->getTableName());
    }
    
	// test monthly
    function test_monthlyPartition()
    {
    	
    	$tableName ='log_visit';
    	$p = new Piwik_TablePartitioning_Monthly($tableName);
    	$timestamp = strtotime("10 September 2000");
    	$suffixShouldBe = "_2000_09";
		$config = Zend_Registry::get('config');
		$prefixTables = $config->database->tables_prefix;
		$tablename = $prefixTables.$tableName.$suffixShouldBe;
		
    	$p->setDate( $timestamp );
    	
    	$allTablesInstalled = Piwik::getTablesInstalled();
    	$this->assertTrue( !in_array($tablename, $allTablesInstalled));
    	$this->assertTrue( $tablename, $p->getTableName());
    	
    	$allTablesInstalled = Piwik::getTablesInstalled();
    	$this->assertTrue( in_array($tablename, $allTablesInstalled));
    	$this->assertEqual( $tablename, (string)$p);
    }
        
	// test daily
    function test_dailyPartition()
    {
    	
    	$tableName ='log_visit';
    	$p = new Piwik_TablePartitioning_Daily($tableName);
    	$timestamp = strtotime("10 September 2000");
    	$suffixShouldBe = "_2000_09_10";
		$config = Zend_Registry::get('config');
		$prefixTables = $config->database->tables_prefix;
		$tablename = $prefixTables.$tableName.$suffixShouldBe;
		
    	$p->setDate( $timestamp );
    	
    	$allTablesInstalled = Piwik::getTablesInstalled();
    	$this->assertTrue( !in_array($tablename, $allTablesInstalled));
    	$this->assertTrue( $tablename, $p->getTableName());
    	
    	$allTablesInstalled = Piwik::getTablesInstalled();
    	$this->assertTrue( in_array($tablename, $allTablesInstalled));
    	$this->assertEqual( $tablename, (string)$p);
    }
    
    
    /**
     * -> exception
     */
    public function _test_()
    {
    	try {
    		test();
        	$this->fail("Exception not raised.");
    	}
    	catch (Exception $expected) {
    		$this->assertPattern("()", $expected->getMessage());
            return;
        }
    }
}
?>
