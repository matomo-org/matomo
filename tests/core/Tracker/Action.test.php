<?php
if(!defined("PIWIK_PATH_TEST_TO_ROOT")) {
	define('PIWIK_PATH_TEST_TO_ROOT', getcwd().'/../../..');
}
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once PIWIK_PATH_TEST_TO_ROOT . "/tests/config_test.php";
}

require_once 'Tracker/Action.php';
require_once 'Tracker/Config.php';
require_once "Database.test.php";

class Test_Piwik_TrackerAction extends  Test_Database
{
	function setUp()
	{
		$GLOBALS['PIWIK_TRACKER_MODE'] = true;
		parent::setUp();
		$userFile = PIWIK_PATH_TEST_TO_ROOT . '/tests/resources/Tracker/Action.config.ini.php';
		$config = Piwik_Tracker_Config::getInstance();
		$config->init($userFile);
	}

	function tearDown()
	{
		$GLOBALS['PIWIK_TRACKER_MODE'] = false;
	}

	protected function setUpRootAccess()
	{
    	$pseudoMockAccess = new FakeAccess;
		FakeAccess::$superUser = true;
		Zend_Registry::set('access', $pseudoMockAccess);
	}
	
	protected function getTestUrls()
	{
		$urls = array(
			// a wrongly formatted url (parse_url returns false)
			'http:////wrongurl',
			
			// a URL with all components
			'http://username:password@hostname:80/path?phpSESSID=value#anchor',
			
			// a standard url 
			'http://a.com/index?p1=v1',
		
			// testing with capital parameter
			'http://a.com/index?p1=v1&P2=v2&p3=v3',
		
			// testing with extra &&
			'http://a.com/index?p1=v1&&p2=v2&p3=v3&p4=v4&&',
		);
		
		return $urls;
	}
	
	/*
	 * No excluded query parameters specified, appart from the standard "session" parameters, always excluded
	 */
	function test_excludeQueryParameters_none()
	{
		$excludedQueryParameters = '';
		$this->setUpRootAccess();
		$idsite = Piwik_SitesManager_API::getInstance()->addSite("site1",array('http://example.org'), $excludedIps = '', $excludedQueryParameters);
		$urls = $this->getTestUrls();
		foreach($urls as $url)
		{
			$expectedUrl = $url;
			if($url=='http://username:password@hostname:80/path?phpSESSID=value#anchor')
			{
				$expectedUrl = 'http://username:password@hostname:80/path#anchor';
			}
			
			// the extra & are automatically cleaned up
			if($url=='http://a.com/index?p1=v1&&p2=v2&p3=v3&p4=v4&&')
			{
				$expectedUrl = 'http://a.com/index?p1=v1&p2=v2&p3=v3&p4=v4';
			}
			$this->assertEqual($expectedUrl, Piwik_Tracker_Action::excludeQueryParametersFromUrl($url, $idsite));
		}
	}
	
	/*
	 * Testing with some website specific parameters excluded
	 */
	function test_excludeQueryParameters_siteExcluded()
	{
		$excludedQueryParameters = 'p4, p2';
		$expectedUrls = array(
			'http:////wrongurl',
			'http://username:password@hostname:80/path#anchor',
			'http://a.com/index?p1=v1',
			'http://a.com/index?p1=v1&p3=v3',
			'http://a.com/index?p1=v1&p3=v3',
		);
		$this->setUpRootAccess();
		$idsite = Piwik_SitesManager_API::getInstance()->addSite("site1",array('http://example.org'), $excludedIps = '', $excludedQueryParameters);
		$urls = $this->getTestUrls();
		$filteredUrls = array();
		foreach($urls as $url)
		{
			$filteredUrls[] = Piwik_Tracker_Action::excludeQueryParametersFromUrl($url, $idsite);
		}
		$this->assertEqual($expectedUrls, $filteredUrls);
	}
	
	/*
	 * Testing with some website specific and some global excluded query parameters
	 */
	function test_excludeQueryParameters_siteAndGlobalExcluded()
	{
		// testing also that query parameters are case insensitive 
		$excludedQueryParameters = 'P2';
		$excludedGlobalParameters = 'blabla, P4';
		$expectedUrls = array(
			'http:////wrongurl',
			'http://username:password@hostname:80/path#anchor',
			'http://a.com/index?p1=v1',
			'http://a.com/index?p1=v1&p3=v3',
			'http://a.com/index?p1=v1&p3=v3',
		);
		$this->setUpRootAccess();
		$idsite = Piwik_SitesManager_API::getInstance()->addSite("site1",array('http://example.org'), $excludedIps = '', $excludedQueryParameters);
		Piwik_SitesManager_API::getInstance()->setGlobalExcludedQueryParameters($excludedGlobalParameters);
		$urls = $this->getTestUrls();
		$filteredUrls = array();
		foreach($urls as $url)
		{
			$filteredUrls[] = Piwik_Tracker_Action::excludeQueryParametersFromUrl($url, $idsite);
		}
		$this->assertEqual($expectedUrls, $filteredUrls);
	}
	
	
	function test_extractUrlAndActionNameFromRequest()
	{
		$action = new Test_Piwik_TrackerAction_extractUrlAndActionNameFromRequest();
		
		$tests = array(
			// outlinks
			array(
				'request' => array( 'link' => 'http://example.org'),
				'expected' => array(	'name' => null,
										'url' => 'http://example.org',
										'type' => Piwik_Tracker_Action::TYPE_OUTLINK),
			),
			// outlinks with custom name
			array(
				'request' => array( 'link' => 'http://example.org', 'action_name' => 'Example.org'),
				'expected' => array(	'name' => 'Example.org',
										'url' => 'http://example.org',
										'type' => Piwik_Tracker_Action::TYPE_OUTLINK),
			),
			// keep the case in urls, but trim
			array(
				'request' => array( 'link' => '	http://example.org/Category/Test/  	'),
				'expected' => array(	'name' => null,
										'url' => 'http://example.org/Category/Test/',
										'type' => Piwik_Tracker_Action::TYPE_OUTLINK),
			),

			// trim the custom name
			array(
				'request' => array( 'link' => '	http://example.org/Category/Test/  	', 'action_name' => '  Example dot org '),
				'expected' => array(	'name' => 'Example dot org',
										'url' => 'http://example.org/Category/Test/',
										'type' => Piwik_Tracker_Action::TYPE_OUTLINK),
			),

			// downloads
			array(
				'request' => array( 'download' => 'http://example.org/*$test.zip'),
				'expected' => array(	'name' => null,
										'url' => 'http://example.org/*$test.zip',
										'type' => Piwik_Tracker_Action::TYPE_DOWNLOAD),
			),

			// downloads with custom name
			array(
				'request' => array( 'download' => 'http://example.org/*$test.zip', 'action_name' => 'Download test.zip'),
				'expected' => array(	'name' => 'Download test.zip',
										'url' => 'http://example.org/*$test.zip',
										'type' => Piwik_Tracker_Action::TYPE_DOWNLOAD),
			),
			
			// keep the case and multiple / in urls
			array(
				'request' => array( 'download' => 'http://example.org/CATEGORY/test///test.pdf'),
				'expected' => array(	'name' => null,
										'url' => 'http://example.org/CATEGORY/test///test.pdf',
										'type' => Piwik_Tracker_Action::TYPE_DOWNLOAD),
			),
			
			// page view
			array(
				'request' => array( 'url' => 'http://example.org/'),
				'expected' => array(	'name' => null,
										'url' => 'http://example.org/',
										'type' => Piwik_Tracker_Action::TYPE_ACTION_URL),
			),
			array(
				'request' => array( 'url' => 'http://example.org/', 'action_name' => 'Example.org Website'),
				'expected' => array(	'name' => 'Example.org Website',
										'url' => 'http://example.org/',
										'type' => Piwik_Tracker_Action::TYPE_ACTION_URL),
			),
			array(
				'request' => array( 'url' => 'http://example.org/CATEGORY/'),
				'expected' => array(	'name' => null,
										'url' => 'http://example.org/CATEGORY/',
										'type' => Piwik_Tracker_Action::TYPE_ACTION_URL),
			),
			array(
				'request' => array( 'url' => 'http://example.org/CATEGORY/TEST', 'action_name' => 'Example.org / Category / test /'),
				'expected' => array(	'name' => 'Example.org/Category/test',
										'url' => 'http://example.org/CATEGORY/TEST',
										'type' => Piwik_Tracker_Action::TYPE_ACTION_URL),
			),

			// empty request
			array(
				'request' => array(),
				'expected' => array(	'name' => null,	'url' => '',
										'type' => Piwik_Tracker_Action::TYPE_ACTION_URL),
			),
			array(
				'request' => array( 'url' => 'http://example.org/category/',
									'action_name' => 'custom name with/one delimiter/two delimiters/'),
				'expected' => array(	'name' => 'custom name with/one delimiter/two delimiters',
										'url' => 'http://example.org/category/',
										'type' => Piwik_Tracker_Action::TYPE_ACTION_URL),
			),
			array(
				'request' => array( 'url' => 'http://example.org/category/',
									'action_name' => 'http://custom action name look like url/'),
				'expected' => array(	'name' => 'http:/custom action name look like url',
										'url' => 'http://example.org/category/',
										'type' => Piwik_Tracker_Action::TYPE_ACTION_URL),
			),
			// testing: delete tab, trimmed, not strtolowered
			array( 
				'request' => array( 'url' => "http://example.org/category/test///test  wOw  	"),
				'expected' => array(	'name' => null,
										'url' => 'http://example.org/category/test///test  wOw',
										'type' => Piwik_Tracker_Action::TYPE_ACTION_URL),
			),
			// testing: inclusion of zero values in action name
			array(
				'request' => array( 'url' => "http://example.org/category/1/0/t/test"),
				'expected' => array(	'name' => null,
										'url' => 'http://example.org/category/1/0/t/test',
										'type' => Piwik_Tracker_Action::TYPE_ACTION_URL),
			),
		);
		foreach($tests as $test) {
			$request = $test['request'];
			$expected = $test['expected'];
			$action->setRequest($request);
			$this->assertEqual($action->public_extractUrlAndActionNameFromRequest(), $expected);
		}
	}
}

class Test_Piwik_TrackerAction_extractUrlAndActionNameFromRequest extends Piwik_Tracker_Action{
	public function public_extractUrlAndActionNameFromRequest()
	{
		return $this->extractUrlAndActionNameFromRequest();
	}
}
