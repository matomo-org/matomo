<?php
if(!defined("PIWIK_PATH_TEST_TO_ROOT")) 
{
	define('PIWIK_PATH_TEST_TO_ROOT', '..');
}
if(!defined('PIWIK_INCLUDE_PATH')) 
{
	define('PIWIK_INCLUDE_PATH', PIWIK_PATH_TEST_TO_ROOT);
}

set_include_path(PIWIK_INCLUDE_PATH . '/core/'
	. PATH_SEPARATOR . PIWIK_INCLUDE_PATH . '/libs/'
	. PATH_SEPARATOR . PIWIK_INCLUDE_PATH . '/plugins/');
					
require_once 'simpletest/autorun.php';
require_once 'simpletest/mock_objects.php';
SimpleTest :: prefer(new HtmlReporter());

error_reporting(E_ALL|E_NOTICE);
date_default_timezone_set('Europe/London');

require_once "Zend/Exception.php";
require_once "Zend/Loader.php";

require_once  "ErrorHandler.php";
//set_error_handler('Piwik_ErrorHandler');

function dump($var)
{
	print("<pre>");
	var_export($var);
	print("</pre>");
}

require_once 'Zend/Registry.php';
require_once 'Zend/Config/Ini.php';
require_once 'Zend/Config.php';
require_once 'Zend/Db.php';
require_once 'Zend/Db/Table.php';
require_once 'FrontController.php';
require_once 'Config.php';
require_once 'Timer.php';
require_once 'Access.php';
require_once 'Log.php';
require_once 'Piwik.php';

assert_options(ASSERT_ACTIVE, 	1);
assert_options(ASSERT_WARNING, 	1);
assert_options(ASSERT_BAIL, 	0);

define('PIWIK_CONFIG_TEST_INCLUDED', true);


