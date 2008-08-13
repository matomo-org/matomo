<?php
// we def have problems with INCLUDE_PATH on these tests....
if(!defined("PATH_TEST_TO_ROOT")) {
	define('PATH_TEST_TO_ROOT', '..');
	$content = file_get_contents('../../piwik.php');
}
else
{
	$content = file_get_contents('../piwik.php');
}
$GLOBALS['content'] = $content;

if(!defined('CONFIG_TEST_INCLUDED'))
{
	require_once PATH_TEST_TO_ROOT ."/../tests/config_test.php";
	
}

class Test_PiwikPhp extends UnitTestCase
{
	function __construct( $title = '')
	{
		parent::__construct( $title );
	}
	
	function testDebugOff()
	{
		// catch that the string GLOBALS['DEBUGPIWIK'] = false
		$ereg = "(GLOBALS\['DEBUGPIWIK'\])([ ])*=([ ])*(false;)";
	
		// first we test the ereg :)
		
		$good = array(
			'$GLOBALS[\'DEBUGPIWIK\'] = false;',
			'$GLOBALS[\'DEBUGPIWIK\']   =    false;',
			' $GLOBALS[\'DEBUGPIWIK\']   =    false;',
		);
		
		foreach($good as $test)
		{
			$this->assertTrue( ereg($ereg,$test) !== false); 
		}
		$bad = array(
			'$GLOBALS[\'DEBUGPIWIK\'] = true;',
			'$GLOBALS[\'DEBUGPIWIK\']   =    1;',
			' $GLOBALS[\'DEBUGPIWIK\']=\'false\';',
		);
		
		foreach($bad as $test)
		{
			$this->assertTrue( ereg($ereg,$test) === false); 
		}
		
		
		// then we check that the piwik.php content does have the DEBUG variable set to off
		$this->assertTrue( ereg($ereg, $GLOBALS['content']) !== false,
			'The $GLOBALS[\'DEBUGPIWIK\'] MUST BE SET TO false IN A PRODUCTION ENVIRONMENT !!!');
	}
}