<?php
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once dirname(__FILE__)."/../../tests/config_test.php";
}

class Test_Piwik_Blank extends UnitTestCase
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
    
    /**
     * template function
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
