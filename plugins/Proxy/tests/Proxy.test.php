<?php
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once dirname(__FILE__)."/../../../tests/config_test.php";
}

class Test_Piwik_Proxy extends UnitTestCase
{
	public function test_isAcceptableRemoteUrl()
	{
		Piwik::createConfigObject();
		Piwik_Config::getInstance()->setTestEnvironment();	

		$data = array(
			// piwik white list (and used in homepage)
			'http://piwik.org/' => array(true),

			'http://piwik.org' => array(true),
			'http://qa.piwik.org/' => array(true),
			'http://forum.piwik.org/' => array(true),
			'http://dev.piwik.org/' => array(true),
			'http://demo.piwik.org/' => array(true),

			// not in the piwik white list
			'http://www.piwik.org/' => array(false),
			'https://piwik.org/' => array(false),
			'http://example.org/' => array(false),
		);

		foreach($data as $url => $expected)
		{
			$this->assertEqual(Piwik_Proxy_Controller::isPiwikUrl($url), $expected[0], $url);
		}
	}
}

