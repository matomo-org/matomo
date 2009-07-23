<?php	
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 */

$GLOBALS['PIWIK_TRACKER_DEBUG'] = false; 
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
	ini_set('include_path', PIWIK_INCLUDE_PATH . '/core'
	     . PATH_SEPARATOR . PIWIK_INCLUDE_PATH . '/libs'
	     . PATH_SEPARATOR . PIWIK_INCLUDE_PATH . '/plugins');
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
	require_once PIWIK_INCLUDE_PATH .'/core/ErrorHandler.php';
	require_once PIWIK_INCLUDE_PATH .'/core/ExceptionHandler.php';
	set_error_handler('Piwik_ErrorHandler');
	set_exception_handler('Piwik_ExceptionHandler');
	printDebug($_GET);
	Piwik_Tracker_Db::enableProfiling();
	Piwik::createConfigObject();
	Piwik::createLogObject();
}

$process = new Piwik_Tracker();
$process->main();
ob_end_flush();
printDebug($_COOKIE);
