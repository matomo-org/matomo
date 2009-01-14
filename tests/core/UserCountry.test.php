<?php
if(!defined("PATH_TEST_TO_ROOT")) {
	define('PATH_TEST_TO_ROOT', getcwd().'/../..');
}
if(!defined('CONFIG_TEST_INCLUDED'))
{
	require_once PATH_TEST_TO_ROOT . "/tests/config_test.php";
}

require 'UserCountry/functions.php';

//Zend_Loader::loadClass('Piwik_');

class Test_Piwik_UserCountry extends UnitTestCase
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
	
	public function test_getFlagFromCode()
	{
		$flag = Piwik_getFlagFromCode("us");
		$this->assertEqual( basename($flag), "us.png" );
	}

	public function test_getFlagFromCodeElsewhere()
	{
		$olddir = getcwd();
		chdir("/");
		$flag = Piwik_getFlagFromCode("us");
		chdir($olddir);
		$this->assertEqual( basename($flag), "us.png" );
	}

	public function test_getFlagFromInvalidCode()
	{
		$flag = Piwik_getFlagFromCode("foo");
		$this->assertEqual( basename($flag), "xx.png" );
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

