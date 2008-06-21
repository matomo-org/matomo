<?php	
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
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

