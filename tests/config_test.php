<?php
if(!defined("PIWIK_PATH_TEST_TO_ROOT")) 
{
	define('PIWIK_PATH_TEST_TO_ROOT', '..');
}
if(!defined('PIWIK_USER_PATH'))
{
	define('PIWIK_USER_PATH', PIWIK_PATH_TEST_TO_ROOT);
}
if(!defined('PIWIK_INCLUDE_PATH'))
{
	define('PIWIK_INCLUDE_PATH', PIWIK_PATH_TEST_TO_ROOT);
}
if(!defined('PIWIK_INCLUDE_SEARCH_PATH'))
{
	define('PIWIK_INCLUDE_SEARCH_PATH', PIWIK_INCLUDE_PATH . '/core'
		. PATH_SEPARATOR . PIWIK_INCLUDE_PATH . '/libs'
		. PATH_SEPARATOR . PIWIK_INCLUDE_PATH . '/plugins'
		. PATH_SEPARATOR . PIWIK_INCLUDE_PATH . '/tests/core'
		. PATH_SEPARATOR . PIWIK_INCLUDE_PATH . '/tests/integration');
}
@ini_set('include_path', PIWIK_INCLUDE_SEARCH_PATH);
@set_include_path(PIWIK_INCLUDE_SEARCH_PATH);
@ini_set('memory_limit', -1);
error_reporting(E_ALL|E_NOTICE);
@date_default_timezone_set('UTC');
					
require_once 'simpletest/autorun.php';
require_once 'simpletest/mock_objects.php';

require_once PIWIK_INCLUDE_PATH . '/core/testMinimumPhpVersion.php';
$_COOKIE = array();

function dump($var)
{
	print("<pre>");
	var_export($var);
	print("</pre>");
}

function printDebug($text)
{
	return;
}

require_once PIWIK_INCLUDE_PATH .'/libs/upgradephp/upgrade.php';
require_once PIWIK_INCLUDE_PATH .'/core/Loader.php';
require_once PIWIK_INCLUDE_PATH .'/libs/Zend/Exception.php';
require_once PIWIK_INCLUDE_PATH .'/libs/Zend/Loader.php';
require_once PIWIK_INCLUDE_PATH .'/core/ErrorHandler.php';
require_once PIWIK_INCLUDE_PATH .'/libs/Zend/Registry.php';
require_once PIWIK_INCLUDE_PATH .'/libs/Zend/Config/Ini.php';
require_once PIWIK_INCLUDE_PATH .'/libs/Zend/Config.php';
require_once PIWIK_INCLUDE_PATH .'/libs/Zend/Db.php';
require_once PIWIK_INCLUDE_PATH .'/libs/Zend/Db/Table.php';
require_once PIWIK_INCLUDE_PATH .'/core/FrontController.php';
require_once PIWIK_INCLUDE_PATH .'/core/Config.php';
require_once PIWIK_INCLUDE_PATH .'/core/Timer.php';
require_once PIWIK_INCLUDE_PATH .'/core/Access.php';
require_once PIWIK_INCLUDE_PATH .'/core/Log.php';
require_once PIWIK_INCLUDE_PATH .'/core/Piwik.php';

assert_options(ASSERT_ACTIVE, 	1);
assert_options(ASSERT_WARNING, 	1);
assert_options(ASSERT_BAIL, 	0);

define('PIWIK_CONFIG_TEST_INCLUDED', true);

require_once 'reporter.php';
SimpleTest::prefer(new HtmlTimerReporter());
