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
		Piwik_Config::getInstance()->setTestEnvironment();	

    	$this->assertEqual(Piwik_Url::getCurrentQueryStringWithParametersModified(array()),Piwik_Url::getCurrentQueryString() );
    	
    	$expectedUrl = parse_url(Piwik_Url::getCurrentUrl());
    	$port = isset($expectedUrl['port']) ? ":{$expectedUrl['port']}" : '';
    	$expectedUrl = $expectedUrl['scheme'].'://'.$expectedUrl['host'].$port.$expectedUrl['path'];
    	$this->assertEqual($expectedUrl, Piwik_Url::getCurrentUrlWithoutQueryString());
    	$this->assertEqual($expectedUrl, Piwik_Url::getCurrentScheme() . '://' . Piwik_Url::getCurrentHost() . Piwik_Url::getCurrentScriptName() );
    	
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
		$GLOBALS['PIWIK_TRACKER_MODE'] = false;
		Piwik::createConfigObject();
		Piwik_Config::getInstance()->setTestEnvironment();
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
			Piwik_Config::getInstance()->General['proxy_host_headers'] = array( $test[2] );
			Piwik_Config::getInstance()->General['proxy_ips'] = array( $test[3] );
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

	public function test_getCurrentUrlWithoutFilename()
	{
		$names = array('PATH_INFO', 'REQUEST_URI', 'SCRIPT_NAME', 'SCRIPT_FILENAME', 'argv', 'HTTPS', 'HTTP_HOST', 'QUERY_STRING', 'HTTP_REFERER');
		$saved = $this->saveGlobals($names);

		foreach($names as $name)
		{
			unset($_SERVER[$name]);
		}

		$tests = array(
			array('http://example.com/', false, 'example.com', '/'),
			array('https://example.org/', true, 'example.org', '/'),
			array('https://example.net/piwik/', 'on', 'example.net', '/piwik/'),
		);

		foreach($tests as $test)
		{
			list($expected, $https, $host, $path) = $test;

			if ($https)
				$_SERVER['HTTPS'] = $https;
			else
				unset($_SERVER['HTTPS']);

			$_SERVER['REQUEST_URI'] = $path;
			$_SERVER['HTTP_HOST'] = $host;

			$url = Piwik_Url::getCurrentUrlWithoutFilename();
			$this->assertEqual($url, $expected);
		}

		$this->restoreGlobals($saved);
	}

	public function test_getCurrentScriptName()
	{
		$names = array('PATH_INFO', 'REQUEST_URI', 'SCRIPT_NAME', 'SCRIPT_FILENAME', 'argv', 'HTTPS', 'HTTP_HOST', 'QUERY_STRING', 'HTTP_REFERER');
		$saved = $this->saveGlobals($names);

		foreach($names as $name)
		{
			unset($_SERVER[$name]);
		}

		$tests = array(
			array('/', 'http://example.com/', null),
			array('/', '/', null),
			array('/index.php', '/index.php', null),
			array('/index.php', '/index.php?module=Foo', null),
			array('/index.php', '/index.php/route/1', '/route/1'),
			array('/index.php', '/index.php/route/2?module=Bar', '/route/2'),
			array('/path/index.php', '/path/index.php/route/3/?module=Fu&action=Bar#Hash', '/route/3/'),
		);

		foreach($tests as $test)
		{
			list($expected, $uri, $pathInfo) = $test;

			$_SERVER['REQUEST_URI'] = $uri;
			$_SERVER['PATH_INFO'] = $pathInfo;

			$scriptPathName = Piwik_Url::getCurrentScriptName();
			$this->assertEqual($scriptPathName, $expected);
		}

		$this->restoreGlobals($saved);
	}

	public function testIsValidHost()
	{
		$testData = array(
			// $expected, $host, $trustedHosts, $description
			array(true, 'example.com', array('example.com'), 'Naked domain'),
			array(true, 'example.net', array('example.com', 'example.net'), 'Multiple domains'),
			array(true, 'piwik.example.com', array('piwik.example.com'), 'Fully qualified domain name'),
			array(true, 'piwik.example.com', array('example.com'), 'Valid subdomain'),
			array(false, 'example.net', array('example.com'), 'Invalid domain'),
			array(false, '.example.com', array('piwik.example.com'), 'Invalid subdomain'),
			array(false, 'example-com', array('example.com'), 'Regex should match . literally'),
			array(false, 'www.attacker.com?example.com', array('example.com'), 'Spoofed host'),
			array(false, 'example.com.attacker.com', array('example.com'), 'Spoofed subdomain'),
			array(true, 'example.com.', array('example.com'), 'Trailing . on host is actually valid'),
			array(true, 'www-dev.example.com', array('example.com'), 'host with dashes is valid'),
			array(true, 'www.example.com:8080', array('example.com'), 'host:port is valid'),
		);
 
		foreach ($testData as $test)
		{
			list($expected, $host, $trustedHosts, $description) = $test;
 
			$this->assertEqual(Piwik_Url::isValidHost($host, $trustedHosts), $expected, $description);
		}
	}
}
