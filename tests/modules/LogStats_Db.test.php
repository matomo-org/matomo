<?php
if(!defined("PATH_TEST_TO_ROOT")) {
	define('PATH_TEST_TO_ROOT', '..');
}
if(!defined('CONFIG_TEST_INCLUDED'))
{
	require_once PATH_TEST_TO_ROOT ."/../tests/config_test.php";
}

require 'LogStats/Db.php';
//Zend_Loader::loadClass('Piwik_');

class Test_Piwik_LogStats_Db extends UnitTestCase
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
     * test that the profiler is disabled (mandatory on a production server)
     */
    public function test_profilingDisabledProduction()
    {
    	$this->assertTrue(Piwik_LogStats_Db::isProfilingEnabled() === false, 'PROFILER SHOULD BE DISABLED IN PRODUCTION!! See Piwik_LogStats_Db::$profiling');
    }
}

