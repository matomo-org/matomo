<?php
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once dirname(__FILE__)."/../../../tests/config_test.php";
}

require_once 'Actions/Actions.php';
require_once 'Tracker/Action.php';

class Test_Piwik_Actions extends UnitTestCase
{
	
	function setUp()
	{
		$userFile = PIWIK_INCLUDE_PATH . '/tests/resources/plugins/Actions/Actions.config.ini.php';
		Piwik::createConfigObject($userFile);
		Piwik_Translate::getInstance()->loadEnglishTranslation();
		Piwik_Config::getInstance()->setTestEnvironment();	
	}
	
	function tearDown()
	{
		Piwik::createConfigObject();
		Piwik_Config::getInstance()->setTestEnvironment();	
	}
	
	function test_getActionExplodedNames()
	{
		$action = new Test_Piwik_Actions_getActionExplodedNames();

		$tests = array(
			array(
				'params' =>	array( 'name' => 'http://example.org/', 'type' => Piwik_Tracker_Action::TYPE_ACTION_URL),
				'expected' => array('/index' ),
			),
			array(
				'params' =>	array( 'name' => 'http://example.org/path/', 'type' => Piwik_Tracker_Action::TYPE_ACTION_URL),
				'expected' => array( 'path', '/index' ),
			),
			array(
				'params' =>	array( 'name' => 'http://example.org/test/path', 'type' => Piwik_Tracker_Action::TYPE_ACTION_URL),
				'expected' => array( 'test', '/path' ),
			),
			array(
				'params' =>	array( 'name' => 'Test / Path', 'type' => Piwik_Tracker_Action::TYPE_ACTION_URL),
				'expected' => array( 'Test', '/Path' ),
			),
			array(
				'params' =>	array( 'name' => '    Test trim   ', 'type' => Piwik_Tracker_Action::TYPE_ACTION_URL),
				'expected' => array( '/Test trim' ),
			),
			array(
				'params' =>	array( 'name' => 'Category / Subcategory', 'type' => Piwik_Tracker_Action::TYPE_ACTION_NAME),
				'expected' => array( 'Category', ' Subcategory' ),
			),
			array(
				'params' =>	array( 'name' => '/path/index.php?var=test', 'type' => Piwik_Tracker_Action::TYPE_ACTION_NAME),
				'expected' => array( 'path', ' index.php?var=test' ),
			),
			array(
				'params' =>	array( 'name' => 'http://example.org/path/Default.aspx#anchor', 'type' => Piwik_Tracker_Action::TYPE_ACTION_NAME),
				'expected' => array( 'path', ' Default.aspx' ),
			),
			array(
				'params' =>	array( 'name' => '', 'type' => Piwik_Tracker_Action::TYPE_ACTION_NAME),
				'expected' => array( 'Page Name not defined' ),
			),
			array(
				'params' =>	array( 'name' => '', 'type' => Piwik_Tracker_Action::TYPE_ACTION_URL),
				'expected' => array( 'Page URL not defined' ),
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
			$processed = $action->public_getActionExplodedNames($params['name'],$params['type']);
			$this->assertEqual($processed, $expected, "Processed: ".var_export($processed, true) . " | Expected: ". var_export($expected, true));
		}
	}
}

class Test_Piwik_Actions_getActionExplodedNames extends Piwik_Actions {
	public function public_getActionExplodedNames($name, $type)
	{
		return self::getActionExplodedNames($name, $type);
	}
}
