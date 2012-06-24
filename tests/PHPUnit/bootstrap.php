<?php
if(!defined("PIWIK_PATH_TEST_TO_ROOT")) 
{
    define('PIWIK_PATH_TEST_TO_ROOT', realpath(dirname(__FILE__) . '/../..'));
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
    define('PIWIK_INCLUDE_SEARCH_PATH', get_include_path()
        . PATH_SEPARATOR . PIWIK_INCLUDE_PATH . '/core'
        . PATH_SEPARATOR . PIWIK_INCLUDE_PATH . '/libs'
        . PATH_SEPARATOR . PIWIK_INCLUDE_PATH . '/plugins');
}
@ini_set('include_path', PIWIK_INCLUDE_SEARCH_PATH);
@set_include_path(PIWIK_INCLUDE_SEARCH_PATH);
@ini_set('memory_limit', -1);
error_reporting(E_ALL|E_NOTICE);
@date_default_timezone_set('UTC');

require_once PIWIK_INCLUDE_PATH .'/libs/upgradephp/upgrade.php';
require_once PIWIK_INCLUDE_PATH .'/core/testMinimumPhpVersion.php';
require_once PIWIK_INCLUDE_PATH .'/core/Loader.php';
require_once PIWIK_INCLUDE_PATH .'/core/FrontController.php';
require_once PIWIK_INCLUDE_PATH .'/tests/PHPUnit/DatabaseTestCase.php';
#require_once PIWIK_INCLUDE_PATH .'/tests/PHPUnit/IntegrationTestCase.php';
require_once PIWIK_INCLUDE_PATH .'/tests/PHPUnit/FakeAccess.php';

// required to build code coverage for uncovered files
require_once PIWIK_INCLUDE_PATH .'/plugins/SecurityInfo/PhpSecInfo/PhpSecInfo.php';