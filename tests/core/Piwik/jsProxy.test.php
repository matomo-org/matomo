<?php
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once dirname(__FILE__)."/../../../tests/config_test.php";
}

require_once 'Database.test.php';

class Test_Piwik_jsProxy extends Test_Database
{
	function test_piwik_js()
	{
		$curlHandle = curl_init();
		curl_setopt($curlHandle, CURLOPT_URL, $this->getStaticSrvUrl() . '/js/');
		curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
		$fullResponse = curl_exec($curlHandle);
		$responseInfo = curl_getinfo($curlHandle);
		curl_close($curlHandle);

		$this->assertEqual($responseInfo["http_code"], 200, 'Ok response');

		$piwik_js = file_get_contents(PIWIK_PATH_TEST_TO_ROOT . '/piwik.js');
		$this->assertEqual($fullResponse, $piwik_js, 'script content');
	}

	function test_piwik_php()
	{
		$curlHandle = curl_init();
		$url = $this->getStaticSrvUrl() . '/js/?idsite=1';
		var_dump($url);
		curl_setopt($curlHandle, CURLOPT_URL, $url);
		curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
		$fullResponse = curl_exec($curlHandle);
		$responseInfo = curl_getinfo($curlHandle);
		curl_close($curlHandle);

		$this->assertEqual($responseInfo["http_code"], 200, 'Ok response');
		$ok = $fullResponse == base64_decode("R0lGODlhAQABAIAAAAAAAAAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==");
		$this->assertTrue($ok, 'image content');
		if(!$ok) {
			var_dump( $fullResponse );
		}
	}

	/**
	 * Helper methods
	 */
	private function getStaticSrvUrl()
	{
		$path = Piwik_Url::getCurrentScriptPath();
		if(substr($path, -7) == '/tests/')
		{
			$path = substr($path, 0, -7);
		}
		else if(substr($path, -18) == '/tests/core/Piwik/')
		{
			$path = substr($path, 0, -18);
		}
		else
		{
			throw new Exception('unsupported test path: ' . $path);
		}

		return "http://" . $_SERVER['HTTP_HOST'] .  $path;
	}
}
