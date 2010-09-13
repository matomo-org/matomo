<?php
if(!defined("PIWIK_PATH_TEST_TO_ROOT")) {
	define('PIWIK_PATH_TEST_TO_ROOT', getcwd().'/../../..');
}
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once PIWIK_PATH_TEST_TO_ROOT . "/tests/config_test.php";
}

// require_once 'Referers/API.php';

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
}
