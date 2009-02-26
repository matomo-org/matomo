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

error_reporting(E_ALL|E_NOTICE);
@ini_set('display_errors', 1);
@ini_set('magic_quotes_runtime', 0);
if(!defined('PIWIK_INCLUDE_PATH'))
{
	define('PIWIK_INCLUDE_PATH', dirname(__FILE__));
}

set_include_path(PIWIK_INCLUDE_PATH 
					. PATH_SEPARATOR . PIWIK_INCLUDE_PATH . '/libs/'
					. PATH_SEPARATOR . PIWIK_INCLUDE_PATH . '/plugins/'
					. PATH_SEPARATOR . PIWIK_INCLUDE_PATH . '/core/'
					. PATH_SEPARATOR . get_include_path());
 
require_once "core/testMinimumPhpVersion.php";

// NOTE: the code above this comment must be PHP4 compatible
date_default_timezone_set(date_default_timezone_get());

if(!defined('ENABLE_ERROR_HANDLER') || ENABLE_ERROR_HANDLER)
{
	require_once "core/ErrorHandler.php";
	require_once "core/ExceptionHandler.php";
	set_error_handler('Piwik_ErrorHandler');
	set_exception_handler('Piwik_ExceptionHandler');
}

if(strlen(session_id()) === 0)
{
	session_start();
}

require_once "FrontController.php";

if(!defined('ENABLE_DISPATCH') || ENABLE_DISPATCH)
{
	$controller = Piwik_FrontController::getInstance();
	$controller->init();
	$controller->dispatch();
}
