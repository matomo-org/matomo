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

	private function saveGlobals($names)
	{
		$saved = array();
		foreach($names as $name)
		{
			$saved[$name] = isset($_SERVER[$name]) ? $_SERVER[$name] : null;
		}
		return $saved;
	}

	private function restoreGlobals($saved)
	{
		foreach($saved as $name => $value)
		{
			if(is_null($value))
			{
				unset($_SERVER[$name]);
			}
			else
			{
				$_SERVER[$name] = $value;
			}
		}
	}

	public function test_getCurrentHost()
	{
		Piwik::createConfigObject();
		Zend_Registry::get('config')->setTestEnvironment();
		$saved = $this->saveGlobals(array('HTTP_HOST', 'HTTP_X_FORWARDED_HOST'));

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

		$this->restoreGlobals($saved);
	}

	public function test_isLocalUrl()
	{
		$saved = $this->saveGlobals(array('HTTP_HOST', 'SCRIPT_URI', 'REQUEST_URI'));

		$tests = array(
			// simple cases
			array('www.example.com', 'http://www.example.com/path/index.php', '/path/index.php', 'http://www.example.com/path/index.php', true),
			array('www.example.com', 'http://www.example.com/path/index.php?module=X', '/path/index.php', 'http://www.example.com/path/', true),
			array('www.example.com', 'http://www.example.com/path/', '/path/index.php', 'http://www.example.com/path/index.php?module=Y', true),
			array('www.example.com', 'http://www.example.com/path/#anchor', '/path/index.php', 'http://www.example.com/path/?query', true),
			array('localhost:8080', 'http://localhost:8080/path/index.php', '/path/index.php', 'http://localhost:8080/path/index.php', true),
			array('www.example.com', 'http://www.example.com/path/', '/path/', 'http://www.example.com/path2/', true),

			// ignore port
			array('www.example.com', 'http://www.example.com:80/path/index.php', '/path/index.php', 'http://www.example.com/path/index.php', true),
			array('www.example.com', 'http://www.example.com/path/index.php', '/path/index.php', 'http://www.example.com:80/path/index.php', true),

			array('localhost', 'http://localhost:8080/path/index.php', '/path/index.php', 'http://localhost:8080/path/index.php', true),
			array('localhost', 'http://localhost/path/index.php', '/path/index.php', 'http://localhost:8080/path/index.php', true),
			array('localhost', 'http://localhost:8080/path/index.php', '/path/index.php', 'http://localhost/path/index.php', true),

			array('localhost:8080', 'http://localhost/path/index.php', '/path/index.php', 'http://localhost:8080/path/index.php', true),
			array('localhost:8080', 'http://localhost:8080/path/index.php', '/path/index.php', 'http://localhost/path/index.php', true),
			array('localhost:8080', 'http://localhost/path/index.php', '/path/index.php', 'http://localhost/path/index.php', true),
			array('localhost:8080', 'http://localhost:8080/path/index.php', '/path/index.php', 'http://localhost:8080/path/index.php', true),

			// IPv6
			array('[::1]', 'http://[::1]/path/index.php', '/path/index.php', 'http://[::1]/path/index.php', true),
			array('[::1]:8080', 'http://[::1]:8080/path/index.php', '/path/index.php', 'http://[::1]/path/index.php', true),
			array('[::1]:8080', 'http://[::1]/path/index.php', '/path/index.php', 'http://[::1]:8080/path/index.php', true),

			// undefined SCRIPT URI
			array('www.example.com', null, '/path/index.php', 'http://www.example.com/path/index.php', true),
			array('localhost:8080', null, '/path/index.php', 'http://localhost:8080/path/index.php', true),
			array('127.0.0.1:8080', null, '/path/index.php', 'http://127.0.0.1:8080/path/index.php', true),
			array('[::1]', null, '/path/index.php', 'http://[::1]/path/index.php', true),
			array('[::1]:8080', null, '/path/index.php', 'http://[::1]:8080/path/index.php', true),

			// Apache+Rails anomaly in SCRIPT_URI
			array('www.example.com', 'http://www.example.com/path/#anchor', 'http://www.example.com/path/index.php', 'http://www.example.com/path/?query', true),

			// mangled HTTP_HOST
			array('www.example.com', 'http://example.com/path/#anchor', '/path/index.php', 'http://example.com/path/referrer', true),

			// suppressed Referer
			array('www.example.com', 'http://www.example.com/path/#anchor', '/path/index.php', null, true),
			array('www.example.com', 'http://www.example.com/path/#anchor', '/path/index.php', '', true),

			// mismatched scheme or host
			array('www.example.com', 'http://www.example.com/path/?module=X', '/path/index.php', 'ftp://www.example.com/path/index.php', false),
			array('www.example.com', 'http://www.example.com/path/?module=X', '/path/index.php', 'http://example.com/path/index.php', false),
			array('www.example.com', 'http://www.example.com/path/', '/path/', 'http://crsf.example.com/path/', false),
		);

		foreach($tests as $i => $test)
		{
			$_SERVER['HTTP_HOST'] = $test[0];
			$_SERVER['SCRIPT_URI'] = $test[1];
			$_SERVER['REQUEST_URI'] = $test[2];
			$urlToTest = $test[3];
			$this->assertEqual( Piwik_Url::isLocalUrl($urlToTest), $test[4], $i );
		}

		$this->restoreGlobals($saved);
	}
}
