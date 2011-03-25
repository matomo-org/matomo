<?php
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once dirname(__FILE__)."/../../../tests/config_test.php";
}

// require_once 'Referers/API.php';
require_once 'Referers/functions.php';

class Test_Referers extends UnitTestCase
{
	function __construct( $title = '')
	{
		parent::__construct( $title );
	}

	// search engine is defined in DataFiles/SearchEngines.php but there's no favicon
	function test_missingSearchEngineIcons()
	{
		require_once PIWIK_PATH_TEST_TO_ROOT . '/core/DataFiles/SearchEngines.php';

		// Get list of existing favicons
		$favicons = scandir(PIWIK_PATH_TEST_TO_ROOT . '/plugins/Referers/images/searchEngines/');

		// Get list of search engines and first appearing URL
		$searchEngines = array();
		foreach($GLOBALS['Piwik_SearchEngines'] as $url => $searchEngine)
		{
			$name = parse_url('http://'.$url);
			if(!array_key_exists($searchEngine[0], $searchEngines))
			{
				$searchEngines[$searchEngine[0]] = $url;

				$this->assertTrue(in_array($name['host'] . '.png', $favicons), $name['host']);
			}
		}
	}

	// favicon exists but there's no corresponding search engine defined in DataFiles/SearchEngines.php
	function test_obsoleteSearchEngineIcons()
	{
		require_once PIWIK_PATH_TEST_TO_ROOT . '/core/DataFiles/SearchEngines.php';

		// Get list of search engines and first appearing URL
		$searchEngines = array();
		foreach($GLOBALS['Piwik_SearchEngines'] as $url => $searchEngine)
		{
			$name = parse_url('http://'.$url);
			if(!array_key_exists($name['host'], $searchEngines))
			{
				$searchEngines[$name['host']] = true;
			}
		}

		// Get list of existing favicons
		$favicons = scandir(PIWIK_PATH_TEST_TO_ROOT . '/plugins/Referers/images/searchEngines/');
		foreach($favicons as $name)
		{
			if($name[0] == '.' || strpos($name, 'xx.') === 0)
			{
				continue;
			}

			$host = substr($name, 0, -4);
			$this->assertTrue(array_key_exists($host, $searchEngines), $host);
		}
	}

	// get search engine host from url
	function test_getSearchEngineHostFromUrl()
	{
		$data = array(
			'http://www.google.com/cse' => 'www.google.com',
			'http://www.google.com' => 'www.google.com',
		);

		foreach($data as $url => $expected)
		{
			$this->assertEqual(Piwik_getSearchEngineHostFromUrl($url), $expected, $url);
		}
	}

/*
	// get search engine url from name and keyword
	function test_getSearchEngineUrlFromNameAndKeyword()
	{
		$data = array(
		);

		foreach($data as $item)
		{
			$name = array_shift($item);
			$keyword = array_shift($item);
			$expected = array_shift($item);
			$this->assertEqual(Piwik_getSearchEngineUrlFromNameAndKeyword($name, $keyword), $expected, "$name $keyword");
		}
	}
*/
}
