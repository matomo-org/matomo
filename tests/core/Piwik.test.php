<?php
if(!defined("PATH_TEST_TO_ROOT")) {
	define('PATH_TEST_TO_ROOT', '..');
}
if(!defined('CONFIG_TEST_INCLUDED'))
{
	require_once PATH_TEST_TO_ROOT ."/../tests/config_test.php";
}

class Test_Piwik extends UnitTestCase
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
	
    
    public function test_isNumericValid()
    {
    	$valid = array(
    			-1, 0 , 1, 1.5, -1.5, 21111, 89898, 99999999999, -4565656,
    			(float)-1, (float)0 , (float)1, (float)1.5, (float)-1.5, (float)21111, (float)89898, (float)99999999999, (float)-4565656,
    			(int)-1, (int)0 , (int)1, (int)1.5, (int)-1.5, (int)21111, (int)89898, (int)99999999999, (int)-4565656,
    			'-1', '0' , '1', '1.5', '-1.5', '21111', '89898', '99999999999', '-4565656',
    		);
    	foreach($valid as $toTest)
    	{
    		$this->assertTrue(Piwik::isNumeric($toTest), $toTest." not valid!");
    	}
    }
    
    public function test_isNumericNotValid()
    {
    	$notvalid = array(
    			'-1.0.0', '1e3','1,2',  '0x123', '--1', '-.',  "-1e-2",  '- 1', '1-', 
    		);
    	foreach($notvalid as $toTest)
    	{
    		$this->assertFalse(Piwik::isNumeric($toTest), $toTest." valid but shouldn't!");
    	}
    }

    public function test_secureDiv()
    {
    	$this->assertTrue( Piwik::secureDiv( 9,3 ) === 3 );
    	$this->assertTrue( Piwik::secureDiv( 9,0 ) === 0 );
    	$this->assertTrue( Piwik::secureDiv( 10,1 ) === 10 );
    	$this->assertTrue( Piwik::secureDiv( 10.0, 1.0 ) === 10.0 );
    	$this->assertTrue( Piwik::secureDiv( 11.0, 2 ) === 5.5 );
    	$this->assertTrue( Piwik::secureDiv( 11.0, 'a' ) === 0 );
    	
    }
}

