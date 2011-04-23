<?php
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once dirname(__FILE__)."/../../tests/config_test.php";
}

require_once "Url.php";

class Test_Piwik_Url extends UnitTestCase
{
    /**
     * display output of all methods
     */
    public function test_allMethods()
    {
		Piwik::createConfigObject();
		Zend_Registry::get('config')->setTestEnvironment();	

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

	public function test_getCurrentHost()
	{
		Piwik::createConfigObject();
		Zend_Registry::get('config')->setTestEnvironment();
		$saveHttpHost = $_SERVER['HTTP_HOST'];
		$saveXFwdHost = isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : null;

		$tests = array(
			'localhost IPv4' => array('127.0.0.1', null, null, null, '127.0.0.1'),
			'localhost IPv6' => array('[::1]', null, null, null, '[::1]'),
			'localhost name' => array('localhost', null, null, null, 'localhost'),

			'IPv4 without proxy' => array('128.1.2.3', null, null, null, '128.1.2.3'),
			'IPv6 without proxy' => array('[2001::b0b]', null, null, null, '[2001::b0b]'),
			'name without proxy' => array('example.com', null, null, null, 'example.com'),

			'IPv4 with one proxy' => array('127.0.0.1', '128.1.2.3', 'HTTP_X_FORWARDED_HOST', null, '128.1.2.3'),
			'IPv6 with one proxy' => array('[::1]', '[2001::b0b]', 'HTTP_X_FORWARDED_HOST', null, '[2001::b0b]'),
			'name with one IPv4 proxy' => array('192.168.1.10', 'example.com', 'HTTP_X_FORWARDED_HOST', null, 'example.com'),
			'name with one IPv6 proxy' => array('[::10]', 'www.example.com', 'HTTP_X_FORWARDED_HOST', null, 'www.example.com'),
			'name with one named proxy' => array('dmz.example.com', 'www.example.com', 'HTTP_X_FORWARDED_HOST', null, 'www.example.com'),

			'IPv4 with multiple proxies' => array('127.0.0.1', '128.1.2.3, 192.168.1.10', 'HTTP_X_FORWARDED_HOST', '192.168.1.*', '128.1.2.3'),
			'IPv6 with multiple proxies' => array('[::1]', '[2001::b0b], [::ffff:192.168.1.10]', 'HTTP_X_FORWARDED_HOST', '::ffff:192.168.1.0/124', '[2001::b0b]'),
			'name with multiple proxies' => array('dmz.example.com', 'www.example.com, dmz.example.com', 'HTTP_X_FORWARDED_HOST', 'dmz.example.com', 'www.example.com'),
		);

		foreach($tests as $description => $test)
		{
			$_SERVER['HTTP_HOST'] = $test[0];
			$_SERVER['HTTP_X_FORWARDED_HOST'] = $test[1];
			Zend_Registry::get('config')->General->proxy_host_headers = array( $test[2] );
			Zend_Registry::get('config')->General->proxy_ips = array( $test[3] );
			$this->assertEqual( Piwik_Url::getCurrentHost(), $test[4], $description );
		}

		$_SERVER['HTTP_HOST'] = $saveHttpHost;
		if($saveXFwdHost)
		{
			$_SERVER['HTTP_X_FORWARDED_HOST'] = $saveXFwdHost;
		}
	}
}
