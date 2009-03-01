<?php
if(!defined("PATH_TEST_TO_ROOT")) {
	define('PATH_TEST_TO_ROOT', getcwd().'/../..');
}
if(!defined('CONFIG_TEST_INCLUDED'))
{
	require_once PATH_TEST_TO_ROOT . "/tests/config_test.php";
}

class Test_Piwik_Config extends UnitTestCase
{
    public function testUserConfigOverwritesSectionGlobalConfigValue()
    {
    	$userFile = 'tests/resources/Config/config.ini.php';
    	$globalFile = 'tests/resources/Config/global.ini.php';
    	
    	$config = new Piwik_Config($userFile, $globalFile);
    	
    	$this->assertEqual($config->Category->key1, "value_overwritten");
    	$this->assertEqual($config->Category->key2, "value2");
    	$this->assertEqual($config->General->login, "test");
    	$this->assertEqual($config->CategoryOnlyInGlobalFile->key3, "value3");
    }
}

