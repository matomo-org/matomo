<?php
if(!defined("PIWIK_PATH_TEST_TO_ROOT")) {
	define('PIWIK_PATH_TEST_TO_ROOT', getcwd().'/../../..');
}
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once PIWIK_PATH_TEST_TO_ROOT . "/tests/config_test.php";
}

require_once PIWIK_INCLUDE_PATH . '/tests/integration/Integration.php';

/**
 * This test is a simple example of how to use the Integration test Base class 
 * in order to easily build an integration test of a given plugin.
 * 
 * You can for example easily generate Tracking pages and visits, and then query all API
 * to compare the output XML.
 * 
 * Check out this example and more in tests/integration/*
 */
class Test_Piwik_Integration_ExampleAPI extends Test_Integration
{
	public function getPathToTestDirectory()
	{
		return PIWIK_INCLUDE_PATH . '/plugins/ExampleAPI/tests';
	}
	
	function test_allGetMethods()
	{
		// Executes all API methods get* and check for output
		// In this plugin, output is static and manually set in the API.php, but in other scripts,
		// one could generate fake inputs, and check that ouputs are processed as expected
		// @see tests/integration/ for more info
		$this->setApiToCall( 'ExampleAPI' );
		// Ignore the getPiwikVersion call which would otherwise fail at every new release
		$this->setApiNotToCall( 'ExampleAPI.getPiwikVersion');
		$renderers = Piwik_DataTable_Renderer::getRenderers();
        $this->callGetApiCompareOutput(__FUNCTION__, $renderers);
	}
}