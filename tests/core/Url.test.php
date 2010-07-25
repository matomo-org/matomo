<?php
if(!defined("PIWIK_PATH_TEST_TO_ROOT")) {
	define('PIWIK_PATH_TEST_TO_ROOT', getcwd().'/../..');
}
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once PIWIK_PATH_TEST_TO_ROOT . "/tests/config_test.php";
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
    	$this->assertEqual(Piwik_Url::getCurrentUrl(), Piwik_Url::getCurrentScheme() . '://' . Piwik_Url::getCurrentHost() . Piwik_Url::getCurrentScriptName() );
    	
    	print("<br/>\nPiwik_Url::getCurrentUrl() -> "
    				. Piwik_Url::getCurrentUrl());
    	print("<br/>\nPiwik_Url::getCurrentUrlWithoutQueryString() -> "
    				. Piwik_Url::getCurrentUrlWithoutQueryString());
    	print("<br/>\nPiwik_Url::getCurrentUrlWithoutFileName() -> "
    				. Piwik_Url::getCurrentUrlWithoutFileName());
    	print("<br/>\nPiwik_Url::getCurrentScriptPath() -> "
    				. Piwik_Url::getCurrentScriptPath());
    	print("<br/>\nPiwik_Url::getCurrentHost() -> "
    				. Piwik_Url::getCurrentHost());
    	print("<br/>\nPiwik_Url::getCurrentScriptName() -> "
    				. Piwik_Url::getCurrentScriptName());
    	print("<br/>\nPiwik_Url::getCurrentQueryString() -> "
    				. Piwik_Url::getCurrentQueryString());
    	print("<br/>\nPiwik_Url::getArrayFromCurrentQueryString() -> ");
    	var_dump(Piwik_Url::getArrayFromCurrentQueryString());
    	print("<br/>\nPiwik_Url::getCurrentQueryStringWithParametersModified() -> "
    				. Piwik_Url::getCurrentQueryStringWithParametersModified(array()));
    	echo "<br/>\n\n";
    	
        // setting parameter to null should remove it from url
        // test on Url.test.php?test=value
    	$parameters = array_keys(Piwik_Url::getArrayFromCurrentQueryString());
    	$parametersNameToValue = array();
    	foreach($parameters as $name)
    	{
    		$parametersNameToValue[$name] = null;
    	}
    	$this->assertEqual(Piwik_Url::getCurrentQueryStringWithParametersModified($parametersNameToValue), '');
    	
    }
}

