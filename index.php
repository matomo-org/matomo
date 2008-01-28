<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 */

/**
 * PHP Configuration init
 */

error_reporting(E_ALL|E_NOTICE);
@ini_set('display_errors', 1);
@ini_set('magic_quotes_runtime', 0);
if(!defined('PIWIK_INCLUDE_PATH'))
{
	define('PIWIK_INCLUDE_PATH', '.');
}

require_once PIWIK_INCLUDE_PATH . "/modules/testMinimumPhpVersion.php";


date_default_timezone_set(date_default_timezone_get());

if(!defined('ENABLE_DISPATCH'))
{
	define('ENABLE_DISPATCH', true);	
}

define('PIWIK_PLUGINS_PATH', PIWIK_INCLUDE_PATH . '/plugins');
define('PIWIK_DATAFILES_INCLUDE_PATH', PIWIK_INCLUDE_PATH . "/modules/DataFiles");

set_include_path(PIWIK_INCLUDE_PATH 
					. PATH_SEPARATOR . PIWIK_INCLUDE_PATH . '/libs/'
					. PATH_SEPARATOR . PIWIK_INCLUDE_PATH . '/plugins/'
					. PATH_SEPARATOR . PIWIK_INCLUDE_PATH . '/modules/'
					. PATH_SEPARATOR . get_include_path());

if(!defined('E_STRICT'))            define('E_STRICT', 2048);
if(!defined('E_RECOVERABLE_ERROR')) define('E_RECOVERABLE_ERROR', 4096);
if(!defined('E_EXCEPTION')) 		define('E_EXCEPTION', 8192);

/**
 * Error / exception handling functions
 */
require_once PIWIK_INCLUDE_PATH . "/modules/ErrorHandler.php";
require_once PIWIK_INCLUDE_PATH . "/modules/ExceptionHandler.php";
set_error_handler('Piwik_ErrorHandler');
set_exception_handler('Piwik_ExceptionHandler');

require_once "FrontController.php";


Piwik_FrontController::$enableDispatch = ENABLE_DISPATCH;

$controller = Piwik_FrontController::getInstance();
$controller->init();
$controller->dispatch();
$controller->end();
