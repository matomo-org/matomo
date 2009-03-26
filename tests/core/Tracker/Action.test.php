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
class Test_Piwik_TrackerAction extends UnitTestCase
{
	function test_extractUrlAndActionNameFromRequest()
	{
    	$userFile = PIWIK_PATH_TEST_TO_ROOT . '/tests/resources/Tracker/Action.config.ini.php';
    	$config = Piwik_Tracker_Config::getInstance();
    	$config->init($userFile);
    	
		$action = new Test_Piwik_TrackerAction_extractUrlAndActionNameFromRequest();
		
		$tests = array(
			// outlinks
			array(
				'request' => array( 'link' => 'http://example.org'),
				'expected' => array(	'name' => 'http://example.org',
										'url' => 'http://example.org',
										'type' => Piwik_Tracker_Action::TYPE_OUTLINK),
			),
			// keep the case in urls, but trim
			array(
				'request' => array( 'link' => '	http://example.org/Category/Test/  	'),
				'expected' => array(	'name' => 'http://example.org/Category/Test/',
										'url' => 'http://example.org/Category/Test/',
										'type' => Piwik_Tracker_Action::TYPE_OUTLINK),
			),
			
			// downloads
			array(
				'request' => array( 'download' => 'http://example.org/*$test.zip'),
				'expected' => array(	'name' => 'http://example.org/*$test.zip',
										'url' => 'http://example.org/*$test.zip',
										'type' => Piwik_Tracker_Action::TYPE_DOWNLOAD),
			),
			// keep the case and multiple / in urls
			array(
				'request' => array( 'download' => 'http://example.org/CATEGORY/test///test.pdf'),
				'expected' => array(	'name' => 'http://example.org/CATEGORY/test///test.pdf',
										'url' => 'http://example.org/CATEGORY/test///test.pdf',
										'type' => Piwik_Tracker_Action::TYPE_DOWNLOAD),
			),
			
			// page view
			array(
				'request' => array( 'url' => 'http://example.org/'),
				'expected' => array(	'name' => 'index',
										'url' => 'http://example.org/',
										'type' => Piwik_Tracker_Action::TYPE_ACTION),
			),
			array(
				'request' => array( 'url' => 'http://example.org/CATEGORY/'),
				'expected' => array(	'name' => 'CATEGORY/index',
										'url' => 'http://example.org/CATEGORY/',
										'type' => Piwik_Tracker_Action::TYPE_ACTION),
			),
			array(
				'request' => array( 'url' => 'http://example.org/category/',
									'action_name' => 'custom name with/one delimiter/two delimiters/'),
				'expected' => array(	'name' => 'custom name with/one delimiter/two delimiters',
										'url' => 'http://example.org/category/',
										'type' => Piwik_Tracker_Action::TYPE_ACTION),
			),
			array(
				'request' => array( 'url' => 'http://example.org/category/',
									'action_name' => 'http://custom action name look like url/'),
				'expected' => array(	'name' => 'http:/custom action name look like url',
										'url' => 'http://example.org/category/',
										'type' => Piwik_Tracker_Action::TYPE_ACTION),
			),
			// testing: delete tab, trimmed, not strtolowered
			array( 
				'request' => array( 'url' => "http://example.org/category/test///test  wOw  	"),
				'expected' => array(	'name' => 'category/test/test  wOw',
										'url' => 'http://example.org/category/test///test  wOw',
										'type' => Piwik_Tracker_Action::TYPE_ACTION),
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