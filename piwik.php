<?php	
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 */

$GLOBALS['PIWIK_TRACKER_DEBUG'] = true; 
if(defined('PIWIK_ENABLE_TRACKING') && !PIWIK_ENABLE_TRACKING)
{
	return;
}

define('PIWIK_TRACKER_MODE', true);
error_reporting(E_ALL|E_NOTICE);
define('PIWIK_INCLUDE_PATH', dirname(__FILE__));
@ignore_user_abort(true);

if((@include "Version.php") === false || !class_exists('Piwik_Version', false))
{
	set_include_path(PIWIK_INCLUDE_PATH . '/core'
		. PATH_SEPARATOR . PIWIK_INCLUDE_PATH . '/libs'
		. PATH_SEPARATOR . PIWIK_INCLUDE_PATH . '/plugins');
}

require_once "Common.php";
require_once "PluginsManager.php";
require_once "Tracker.php";
require_once "Tracker/Config.php";
require_once "Tracker/Action.php";
require_once "Cookie.php";
require_once "Tracker/Db.php";
require_once "Tracker/Visit.php";
require_once "Tracker/GoalManager.php";

session_cache_limiter('nocache');
ob_start();
if($GLOBALS['PIWIK_TRACKER_DEBUG'] === true)
{	
	require_once "core/Loader.php";
	date_default_timezone_set(date_default_timezone_get());
	require_once "core/ErrorHandler.php";
	require_once "core/ExceptionHandler.php";
	set_error_handler('Piwik_ErrorHandler');
	set_exception_handler('Piwik_ExceptionHandler');
	printDebug($_GET);
	Piwik_Tracker_Db::enableProfiling();
	Piwik::createConfigObject();
	Piwik::createLogObject();
}

$process = new Piwik_Tracker;
$process->main();
ob_end_flush();
printDebug($_COOKIE);
