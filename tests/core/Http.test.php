<?php
if(!defined("PIWIK_PATH_TEST_TO_ROOT")) {
	define('PIWIK_PATH_TEST_TO_ROOT', getcwd().'/../..');
}
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once PIWIK_PATH_TEST_TO_ROOT . "/tests/config_test.php";
}
class Test_Piwik_Http extends UnitTestCase
{
	public function test_fetchRemoteFile()
	{
		$methods = array(
			'curl', 'stream', 'socket'
		);

		$this->assertTrue( in_array(Piwik_Http::getTransportMethod(), $methods) );

		foreach($methods as $method)
		{
			$version = '';
			try {
				$version = Piwik_Http::sendHttpRequestBy($method, 'http://api.piwik.org/1.0/getLatestVersion/', 5);
			}
			catch(Exception $e) {
				var_dump($e->getMessage());
			}

			$this->assertTrue( preg_match('/^([0-9.]+)$/', $version) );
		}

		$destinationPath = PIWIK_USER_PATH . '/tmp/latest/LATEST';
		try {
			Piwik_Http::fetchRemoteFile('http://api.piwik.org/1.0/getLatestVersion/', $destinationPath, 3);
		}
		catch(Exception $e) {
			var_dump($e->getMessage());
		}

		$this->assertTrue( filesize($destinationPath) > 0 );

		$destinationPath = PIWIK_USER_PATH . '/tmp/latest/latest.zip';
		try {
			Piwik_Http::fetchRemoteFile('http://piwik.org/latest.zip', $destinationPath, 3);
		}
		catch(Exception $e) {
			var_dump($e->getMessage());
		}

		$this->assertTrue( filesize($destinationPath) > 0 );
	}
}
