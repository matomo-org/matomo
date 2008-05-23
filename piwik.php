<?php	
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 */

/**
 * Misc Thoughts about optimization
 * 
 * - after a day is archived, we delete all the useless information from the log table, keeping only the useful data for weeks/month
 *   maybe we create a new table containing only these aggregate and we can delete the rows of the day in the log table
 */
 
/*
 * Some benchmarks
 * 
 * - with the config parsing + db connection
 * Requests per second:    471.91 [#/sec] (mean)
 * 
 * - with the main algorithm working + one visitor requesting 5000 times
 * Requests per second:    155.00 [#/sec] (mean)
 * 
 * - august 28th, main algo + files in place + one visitor requesting 5000 times
 * Requests per second:    118.55 [#/sec] (mean)
 */
error_reporting(E_ALL|E_NOTICE);
define('PIWIK_INCLUDE_PATH', '.');
@ignore_user_abort(true);

set_include_path(PIWIK_INCLUDE_PATH 
					. PATH_SEPARATOR . PIWIK_INCLUDE_PATH . '/libs/'
					. PATH_SEPARATOR . PIWIK_INCLUDE_PATH . '/plugins/'
					. PATH_SEPARATOR . PIWIK_INCLUDE_PATH . '/modules'
					. PATH_SEPARATOR . get_include_path() );

require_once "Common.php";
require_once "PluginsManager.php";
require_once "LogStats.php";
require_once "LogStats/Config.php";
require_once "LogStats/Action.php";
require_once "Cookie.php";
require_once "LogStats/Db.php";
require_once "LogStats/Visit.php";

$GLOBALS['DEBUGPIWIK'] = false;

if($GLOBALS['DEBUGPIWIK'] === true)
{	
	date_default_timezone_set(date_default_timezone_get());
	require_once "modules/ErrorHandler.php";
	require_once "modules/ExceptionHandler.php";
	set_error_handler('Piwik_ErrorHandler');
	set_exception_handler('Piwik_ExceptionHandler');
	printDebug($_GET);
	Piwik_LogStats_Db::enableProfiling();
	Piwik::createConfigObject();
	Piwik::createLogObject();
}

ob_start();
$process = new Piwik_LogStats;
$process->main();
ob_end_flush();

printDebug($_COOKIE);

