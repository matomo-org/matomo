<?php
if(!defined("PATH_TEST_TO_ROOT")) {
	define('PATH_TEST_TO_ROOT', '..');
}
if(!defined('CONFIG_TEST_INCLUDED'))
{
	require_once PATH_TEST_TO_ROOT ."/../tests/config_test.php";
}

//Zend_Loader::loadClass('Piwik_');

class Test_Piwik_Blank extends UnitTestCase
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

