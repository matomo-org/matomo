<?php
if(!defined("PATH_TEST_TO_ROOT")) {
	define('PATH_TEST_TO_ROOT', getcwd().'/../..');
}
if(!defined('CONFIG_TEST_INCLUDED'))
{
	require_once PATH_TEST_TO_ROOT . "/tests/config_test.php";
}

require_once "Url.php";

class Test_Piwik_Url extends UnitTestCase
{
    /**
     * display output of all methods
     */
    public function test_allMethods()
    {
    	$this->assertEqual(Piwik_Url::getCurrentQueryStringWithParametersModified(array()),Piwik_Url::getCurrentQueryString() );
    	$this->assertEqual(Piwik_Url::getCurrentUrl(), Piwik_Url::getCurrentUrlWithoutQueryString());
    	$this->assertEqual(Piwik_Url::getCurrentUrl(), Piwik_Url::getCurrentHost() . Piwik_Url::getCurrentScriptName() );
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
    	print("<br>\nPiwik_Url::getArrayFromCurrentQueryString() ");
    	var_dump(Piwik_Url::getArrayFromCurrentQueryString());
    }
}

