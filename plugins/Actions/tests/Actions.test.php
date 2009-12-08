<?php
if(!defined("PIWIK_PATH_TEST_TO_ROOT")) {
	define('PIWIK_PATH_TEST_TO_ROOT', getcwd().'/../../..');
}
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once PIWIK_PATH_TEST_TO_ROOT . "/tests/config_test.php";
}

require_once 'Actions/Actions.php';
require_once 'Tracker/Action.php';
require_once 'Tracker/Config.php';

class Test_Piwik_Actions extends UnitTestCase
{
	function test_getActionExplodedNames()
	{
		$userFile = PIWIK_INCLUDE_PATH . '/tests/resources/plugins/Actions/Actions.config.ini.php';

		Piwik::createConfigObject($userFile);

		$action = new Test_Piwik_Actions_getActionExplodedNames();

		$tests = array(
			array(
				'params' =>	array( 'name' => 'http://example.org/', 'type' => Piwik_Tracker_Action::TYPE_ACTION_URL),
				'expected' => array('index' ),
			),
			array(
				'params' =>	array( 'name' => 'http://example.org/path/', 'type' => Piwik_Tracker_Action::TYPE_ACTION_URL),
				'expected' => array( 'path', 'index' ),
			),
			array(
				'params' =>	array( 'name' => 'http://example.org/test/path', 'type' => Piwik_Tracker_Action::TYPE_ACTION_URL),
				'expected' => array( 'test', 'path' ),
			),
			array(
				'params' =>	array( 'name' => 'Test / Path', 'type' => Piwik_Tracker_Action::TYPE_ACTION_URL),
				'expected' => array( 'Test', 'Path' ),
			),
			array(
				'params' =>	array( 'name' => '    Test trim   ', 'type' => Piwik_Tracker_Action::TYPE_ACTION_URL),
				'expected' => array( 'Test trim' ),
			),
			array(
				'params' =>	array( 'name' => 'Category / Subcategory', 'type' => Piwik_Tracker_Action::TYPE_ACTION_NAME),
				'expected' => array( 'Category', 'Subcategory' ),
			),
			array(
				'params' =>	array( 'name' => '/path/index.php?var=test', 'type' => Piwik_Tracker_Action::TYPE_ACTION_NAME),
				'expected' => array( 'path', 'index.php?var=test' ),
			),
			array(
				'params' =>	array( 'name' => 'http://example.org/path/Default.aspx#anchor', 'type' => Piwik_Tracker_Action::TYPE_ACTION_NAME),
				'expected' => array( 'path', 'Default.aspx' ),
			),
			array(
				'params' =>	array( 'name' => '', 'type' => Piwik_Tracker_Action::TYPE_ACTION_NAME),
				'expected' => array( Zend_Registry::get('config')->General->action_default_name_when_not_defined ),
			),
			array(
				'params' =>	array( 'name' => '', 'type' => Piwik_Tracker_Action::TYPE_ACTION_URL),
				'expected' => array( Zend_Registry::get('config')->General->action_default_url_when_not_defined ),
			),
			array(
				'params' =>	array( 'name' => 'http://example.org/download.zip', 'type' => Piwik_Tracker_Action::TYPE_DOWNLOAD),
				'expected' => array( 'example.org', '/download.zip' ),
			),
			array(
				'params' =>	array( 'name' => 'http://example.org/download/1/', 'type' => Piwik_Tracker_Action::TYPE_DOWNLOAD),
				'expected' => array( 'example.org', '/download/1/' ),
			),
			array(
				'params' =>	array( 'name' => 'http://example.org/link', 'type' => Piwik_Tracker_Action::TYPE_OUTLINK),
				'expected' => array( 'example.org', '/link' ),
			),
			array(
				'params' =>	array( 'name' => 'http://example.org/some/path/', 'type' => Piwik_Tracker_Action::TYPE_OUTLINK),
				'expected' => array( 'example.org', '/some/path/' ),
			),

		);
		foreach($tests as $test) {
			$params = $test['params'];
			$expected = $test['expected'];
			$this->assertEqual($action->public_getActionExplodedNames($params['name'],$params['type']), $expected);
		}
	}
}

class Test_Piwik_Actions_getActionExplodedNames extends Piwik_Actions {
	public function public_getActionExplodedNames($name, $type)
	{
		return self::getActionExplodedNames($name, $type);
	}
}