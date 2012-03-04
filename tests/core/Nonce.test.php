<?php
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once dirname(__FILE__)."/../../tests/config_test.php";
}

require_once "Nonce.php";

class Test_Piwik_Nonce extends UnitTestCase
{
	public function setUp()
	{
		parent::setUp();
		$this->host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
	}
	
	public function tearDown()
	{
		parent::tearDown();
		$_SERVER['HTTP_HOST'] = $this->host;
	}

	public function test_getAcceptableOrigins()
	{
		Piwik::createConfigObject();
		Piwik_Config::getInstance()->setTestEnvironment();	

		$tests = array(
			// HTTP_HOST => expected
			'example.com' => array( 'http://example.com', 'https://example.com' ),
			'example.com:80' => array( 'http://example.com', 'https://example.com' ),
			'example.com:443' => array( 'http://example.com', 'https://example.com' ),
			'example.com:8080' => array( 'http://example.com', 'https://example.com', 'http://example.com:8080', 'https://example.com:8080' ),
		);

		foreach($tests as $host => $expected)
		{
			$_SERVER['HTTP_HOST'] = $host;
			$this->assertEqual( Piwik_Nonce::getAcceptableOrigins(), $expected, $host );
		}
	}
}
