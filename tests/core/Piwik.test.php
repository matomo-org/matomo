<?php
if(!defined("PIWIK_PATH_TEST_TO_ROOT")) {
	define('PIWIK_PATH_TEST_TO_ROOT', getcwd().'/../..');
}
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once PIWIK_PATH_TEST_TO_ROOT . "/tests/config_test.php";
}
class Test_Piwik extends UnitTestCase
{
    public function test_isNumericValid()
    {
    	$valid = array(
    			-1, 0 , 1, 1.5, -1.5, 21111, 89898, 99999999999, -4565656,
    			(float)-1, (float)0 , (float)1, (float)1.5, (float)-1.5, (float)21111, (float)89898, (float)99999999999, (float)-4565656,
    			(int)-1, (int)0 , (int)1, (int)1.5, (int)-1.5, (int)21111, (int)89898, (int)99999999999, (int)-4565656,
    			'-1', '0' , '1', '1.5', '-1.5', '21111', '89898', '99999999999', '-4565656',
    			'1e3','0x123', "-1e-2",
    		);
    	foreach($valid as $toTest)
    	{
    		$this->assertTrue(is_numeric($toTest), $toTest." not valid but should!");
    	}
    }
    
    public function test_isNumericNotValid()
    {
    	$notvalid = array(
    			'-1.0.0', '1,2',   '--1', '-.',   '- 1', '1-', 
    		);
    	foreach($notvalid as $toTest)
    	{
    		$this->assertFalse(is_numeric($toTest), $toTest." valid but shouldn't!");
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
    
    public function test_getPrettyTimeFromSeconds()
    {
    	Piwik_Translate::getInstance()->loadEnglishTranslation();
    	$tests = array(
    		30 => '30s',
    		60 => '1 min 0s',
    		100 => '1 min 40s',
    		3600 => '1 hours 0 min',
    		3700 => '1 hours 1 min',
    		86400 + 3600 * 10 => '1 days 10 hours',
    		86400 * 365 => '365 days 0 hours',
    		(86400 * (365.25 + 10)) => '1 years 10 days',
    		
    	);
    	foreach($tests as $seconds => $expected)
    	{
    		$this->assertEqual( Piwik::getPrettyTimeFromSeconds($seconds), str_replace(' ','&nbsp;', $expected));
    	}
    	Piwik_Translate::getInstance()->unloadEnglishTranslation();
    }
}
