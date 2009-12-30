<?php	
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @package Piwik
 */

if(file_exists('bootstrap.php'))
{
	require_once 'bootstrap.php';
}

$GLOBALS['PIWIK_TRACKER_DEBUG'] = false;

define('PIWIK_TRACKER_MODE', true);
error_reporting(E_ALL|E_NOTICE);

define('PIWIK_DOCUMENT_ROOT', dirname(__FILE__)=='/'?'':dirname(__FILE__));
if(!defined('PIWIK_USER_PATH'))
{
	define('PIWIK_USER_PATH', PIWIK_DOCUMENT_ROOT);
}
if(!defined('PIWIK_INCLUDE_PATH'))
{
	define('PIWIK_INCLUDE_PATH', PIWIK_DOCUMENT_ROOT);
}

@ignore_user_abort(true);

if(!defined('PIWIK_INCLUDE_SEARCH_PATH'))
{
	define('PIWIK_INCLUDE_SEARCH_PATH', PIWIK_INCLUDE_PATH . '/core'
		. PATH_SEPARATOR . PIWIK_INCLUDE_PATH . '/libs'
		. PATH_SEPARATOR . PIWIK_INCLUDE_PATH . '/plugins');
	@ini_set('include_path', PIWIK_INCLUDE_SEARCH_PATH);
	@set_include_path(PIWIK_INCLUDE_SEARCH_PATH);
}

require_once PIWIK_INCLUDE_PATH .'/libs/Event/Dispatcher.php';
require_once PIWIK_INCLUDE_PATH .'/libs/Event/Notification.php';
require_once PIWIK_INCLUDE_PATH .'/core/PluginsManager.php';
require_once PIWIK_INCLUDE_PATH .'/core/Plugin.php';
require_once PIWIK_INCLUDE_PATH .'/core/Common.php';
require_once PIWIK_INCLUDE_PATH .'/core/Tracker.php';
require_once PIWIK_INCLUDE_PATH .'/core/Tracker/Config.php';
require_once PIWIK_INCLUDE_PATH .'/core/Tracker/Db.php';
require_once PIWIK_INCLUDE_PATH .'/core/Tracker/Visit.php';
require_once PIWIK_INCLUDE_PATH .'/core/Tracker/GoalManager.php';
require_once PIWIK_INCLUDE_PATH .'/core/Tracker/Action.php';
require_once PIWIK_INCLUDE_PATH .'/core/CacheFile.php';
require_once PIWIK_INCLUDE_PATH .'/core/Cookie.php';

session_cache_limiter('nocache');
ob_start();
if($GLOBALS['PIWIK_TRACKER_DEBUG'] === true)
{	
	@date_default_timezone_set(date_default_timezone_get());
	require_once PIWIK_INCLUDE_PATH .'/core/Loader.php';
	require_once PIWIK_INCLUDE_PATH .'/core/ErrorHandler.php';
	require_once PIWIK_INCLUDE_PATH .'/core/ExceptionHandler.php';
	set_error_handler('Piwik_ErrorHandler');
	set_exception_handler('Piwik_ExceptionHandler');
	printDebug($_GET);
	Piwik_Tracker_Db::enableProfiling();
	Piwik::createConfigObject();
	Piwik::createLogObject();
}

if(!defined('PIWIK_ENABLE_TRACKING') || PIWIK_ENABLE_TRACKING)
{
	$process = new Piwik_Tracker();
	$process->main();
	ob_end_flush();
	printDebug($_COOKIE);
}
