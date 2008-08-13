<?php
if(!defined("PATH_TEST_TO_ROOT")) {
	define('PATH_TEST_TO_ROOT', '..');
}
if(!defined('CONFIG_TEST_INCLUDED'))
{
	require_once PATH_TEST_TO_ROOT ."/../tests/config_test.php";
}

require 'LogStats/Db.php';

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
    public function test_profilingDisabledInProduction()
    {
    	$this->assertTrue(Piwik_LogStats_Db::isProfilingEnabled() === false, 'SQL profiler should be disabled in production! See Piwik_LogStats_Db::$profiling');
    }
}

