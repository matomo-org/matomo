<?php
if(!defined("PATH_TEST_TO_ROOT")) {
	define('PATH_TEST_TO_ROOT', '..');
}
if(!defined('CONFIG_TEST_INCLUDED'))
{
	require_once PATH_TEST_TO_ROOT ."/../tests/config_test.php";
}

require_once "modules/Url.php";

class Test_Piwik_Url extends UnitTestCase
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
     * display output of all methods
     */
    public function test_allMethods()
    {
    	print("<br>\nPiwik_Url::getCurrentQueryStringWithParametersModified() "
    				. Piwik_Url::getCurrentQueryStringWithParametersModified(array()));
    	print("<br>\nPiwik_Url::getCurrentUrl() "
    				. Piwik_Url::getCurrentUrl());
    	print("<br>\nPiwik_Url::getCurrentUrlWithoutQueryString() "
    				. Piwik_Url::getCurrentUrlWithoutQueryString());
    	print("<br>\nPiwik_Url::getCurrentUrlWithoutFileName() "
    				. Piwik_Url::getCurrentUrlWithoutFileName());
    	print("<br>\nPiwik_Url::getCurrentScriptName() "
    				. Piwik_Url::getCurrentScriptName());
    	print("<br>\nPiwik_Url::getCurrentHost() "
    				. Piwik_Url::getCurrentHost());
    	print("<br>\nPiwik_Url::getCurrentQueryString() "
    				. Piwik_Url::getCurrentQueryString());
    }
}

