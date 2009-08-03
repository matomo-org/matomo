<?php
if(!defined("PIWIK_PATH_TEST_TO_ROOT")) {
	define('PIWIK_PATH_TEST_TO_ROOT', getcwd().'/../..');
}
if(!defined('PIWIK_CONFIG_TEST_INCLUDED'))
{
	require_once PIWIK_PATH_TEST_TO_ROOT . "/tests/config_test.php";
}

require_once 'Database.test.php';

class Test_Piwik_Updater extends Test_Database 
{
    public function test_updaterChecksCoreVersion_andDetectsUpdateFile()
    {
    	$updater = new Piwik_Updater();
    	$updater->pathUpdateFileCore = PIWIK_INCLUDE_PATH . '/tests/resources/Updater/core/';
    	$updater->recordComponentSuccessfullyUpdated('core', '0.1');
    	$updater->addComponentToCheck('core', '0.3');
    	$componentsWithUpdateFile = $updater->getComponentsWithUpdateFile();
    	$this->assertTrue(count($componentsWithUpdateFile) == 1);
    }
    

    public function test_updaterChecksGivenPluginVersion_andDetectsMultipleUpdateFile_inOrder()
    {
    	$updater = new Piwik_Updater();
    	$updater->pathUpdateFilePlugins = PIWIK_INCLUDE_PATH . '/tests/resources/Updater/%s/';
    	$updater->recordComponentSuccessfullyUpdated('testpluginUpdates', '0.1beta');
    	$updater->addComponentToCheck('testpluginUpdates', '0.1');
    	$componentsWithUpdateFile = $updater->getComponentsWithUpdateFile();

    	$this->assertTrue(count($componentsWithUpdateFile) == 1);
    	$updateFiles = $componentsWithUpdateFile['testpluginUpdates'];
    	$this->assertTrue(count($updateFiles) == 2);
    	
	$path = PIWIK_INCLUDE_PATH . '/tests/resources/Updater/testpluginUpdates/';
    	$expectedInOrder = array(
		$path . '0.1beta2.php' => '0.1beta2',
		$path . '0.1.php' => '0.1'
	);
    	$this->assertEqual(array_map("basename", $updateFiles), $expectedInOrder);
    	
    }
    
    public function test_updaterChecksCoreAndPlugin_checkThatCoreIsRanFirst()
    {
    	$updater = new Piwik_Updater();
    	$updater->pathUpdateFilePlugins = PIWIK_INCLUDE_PATH . '/tests/resources/Updater/%s/';
    	$updater->pathUpdateFileCore = PIWIK_INCLUDE_PATH . '/tests/resources/Updater/core/';
    	
    	$updater->recordComponentSuccessfullyUpdated('testpluginUpdates', '0.1beta');
    	$updater->addComponentToCheck('testpluginUpdates', '0.1');
    	
    	$updater->recordComponentSuccessfullyUpdated('core', '0.1');
    	$updater->addComponentToCheck('core', '0.3');
    	
    	$componentsWithUpdateFile = $updater->getComponentsWithUpdateFile();
    	$this->assertTrue(count($componentsWithUpdateFile) == 2);
	   	reset($componentsWithUpdateFile);
    	$this->assertTrue(key($componentsWithUpdateFile) == 'core');
    }
    
}

