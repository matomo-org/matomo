<?php
if(!defined("PATH_TEST_TO_ROOT")) 
{
	define('PATH_TEST_TO_ROOT', '..');
}
if(!defined("PATH_TEST_TO_ROOT2")) 
{
	define('PATH_TEST_TO_ROOT2', '../..');
}

if(!defined('PIWIK_INCLUDE_PATH')) 
{
	define('PIWIK_INCLUDE_PATH', PATH_TEST_TO_ROOT);
}

set_include_path(	  PATH_TEST_TO_ROOT .'/' 
					. PATH_SEPARATOR . PATH_TEST_TO_ROOT .'/../' 
					. PATH_SEPARATOR . '../' . PATH_TEST_TO_ROOT
					. PATH_SEPARATOR . getcwd()
					. PATH_SEPARATOR . getcwd() . '/../'
					. PATH_SEPARATOR . PATH_TEST_TO_ROOT2
					. PATH_SEPARATOR . PATH_TEST_TO_ROOT
					. PATH_SEPARATOR . PATH_TEST_TO_ROOT . '/libs/'
					. PATH_SEPARATOR . getcwd() . '/../../libs/'
					. PATH_SEPARATOR . getcwd() . '/../libs/'
					. PATH_SEPARATOR . getcwd() . '/../../config/'
					. PATH_SEPARATOR . getcwd() . '/../config/'
					. PATH_SEPARATOR . getcwd() . '/../../modules/'
					. PATH_SEPARATOR . getcwd() . '/../modules/'
					. PATH_SEPARATOR . getcwd() . '/../../tests/'
					. PATH_SEPARATOR . getcwd() . '/../tests/'
					. PATH_SEPARATOR . getcwd() . '/../'
					. PATH_SEPARATOR . PATH_TEST_TO_ROOT . '/plugins/'
					. PATH_SEPARATOR . PATH_TEST_TO_ROOT . '/config/'
					. PATH_SEPARATOR . PATH_TEST_TO_ROOT . '/modules/'
					. PATH_SEPARATOR . PATH_TEST_TO_ROOT . '/tests/'
					. PATH_SEPARATOR . PATH_TEST_TO_ROOT . '/'
					. PATH_SEPARATOR . PATH_TEST_TO_ROOT2 . '/libs/'
					. PATH_SEPARATOR . PATH_TEST_TO_ROOT2 . '/config/'
					. PATH_SEPARATOR . PATH_TEST_TO_ROOT2 . '/plugins/'
					. PATH_SEPARATOR . PATH_TEST_TO_ROOT2 . '/modules/'
					. PATH_SEPARATOR . PATH_TEST_TO_ROOT2 . '/tests/'
					. PATH_SEPARATOR . PATH_TEST_TO_ROOT2 . '/'
					. PATH_SEPARATOR . get_include_path() 
					. PATH_SEPARATOR . get_include_path() . '../'
			);
					
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
require_once 'API/APIable.php';
require_once 'Access.php';
require_once 'Log.php';
require_once 'modules/Piwik.php';

assert_options(ASSERT_ACTIVE, 	1);
assert_options(ASSERT_WARNING, 	1);
assert_options(ASSERT_BAIL, 	0);

define('CONFIG_TEST_INCLUDED', true);


