<?php
if(!defined("PATH_TEST_TO_ROOT")) 
{
	define('PATH_TEST_TO_ROOT', '..');
}

if(!defined('PIWIK_INCLUDE_PATH')) 
{
	define('PIWIK_INCLUDE_PATH', PATH_TEST_TO_ROOT);
}

if (! defined('SIMPLE_TEST')) 
{
	define('SIMPLE_TEST', PATH_TEST_TO_ROOT . '/tests/simpletest/');
}

require_once SIMPLE_TEST . 'autorun.php';
require_once SIMPLE_TEST . 'mock_objects.php';
SimpleTest :: prefer(new HtmlReporter());

error_reporting(E_ALL|E_NOTICE);
date_default_timezone_set('Europe/London');

set_include_path(PATH_TEST_TO_ROOT 
					. PATH_SEPARATOR . PATH_TEST_TO_ROOT . '/libs/'
					. PATH_SEPARATOR . PATH_TEST_TO_ROOT . '/core/'
					. PATH_SEPARATOR . PATH_TEST_TO_ROOT . '/modules'
					. PATH_SEPARATOR . PATH_TEST_TO_ROOT . '/core/models'
					. PATH_SEPARATOR . get_include_path());


require_once PIWIK_INCLUDE_PATH . "/modules/ErrorHandler.php";
//set_error_handler('Piwik_ErrorHandler');

require_once "Zend/Exception.php";
require_once "Zend/Loader.php";

Zend_Loader::loadClass('Zend_Registry');
Zend_Loader::loadClass('Zend_Config_Ini');
Zend_Loader::loadClass('Zend_Db');
Zend_Loader::loadClass('Zend_Db_Table');
Zend_Loader::loadClass('Zend_Debug');
Zend_Loader::loadClass('Piwik_Config');
Zend_Loader::loadClass('Piwik_Access');
Zend_Loader::loadClass('Piwik_Log');
Zend_Loader::loadClass('Piwik');

assert_options(ASSERT_ACTIVE, 	1);
assert_options(ASSERT_WARNING, 	1);
assert_options(ASSERT_BAIL, 	0);
?>
