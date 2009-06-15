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
@ini_set('session.save_handler', 'files');

if(!defined('PIWIK_INCLUDE_PATH'))
{
	define('PIWIK_INCLUDE_PATH', dirname(__FILE__));
}

if((@include "Version.php") === false || !class_exists('Piwik_Version', false))
{
	set_include_path(PIWIK_INCLUDE_PATH . '/core'
		. PATH_SEPARATOR . PIWIK_INCLUDE_PATH . '/libs'
		. PATH_SEPARATOR . PIWIK_INCLUDE_PATH . '/plugins');
}

function piwikAutoloader($class)
{
	$class = str_replace('_', '/', $class) . '.php';
	if(substr($class, 0, 6) === 'Piwik/')
	{
		$class = substr($class, 6);
		if(file_exists(PIWIK_INCLUDE_PATH . "/core/" . $class))
		{
			include_once PIWIK_INCLUDE_PATH . "/core/" . $class;
		}
		else
		{
			include_once PIWIK_INCLUDE_PATH . "/plugins/" . $class;
		}
	}
	else
	{
		include_once PIWIK_INCLUDE_PATH . "/libs/" . $class;
	}
}

// Note: only one __autoload per PHP instance
if(function_exists('spl_autoload_register'))
{
	spl_autoload_register('piwikAutoloader'); // use the SPL autoload stack
	if(function_exists('__autoload'))
	{
		spl_auto_register('__autoload');
	}
}
else
{
	function __autoload($class)
	{
		piwikAutoloader($class);
	}
}
 
require_once "core/testMinimumPhpVersion.php";

// NOTE: the code above this comment must be PHP4 compatible
date_default_timezone_set(date_default_timezone_get());

if(!defined('PIWIK_ENABLE_ERROR_HANDLER') || PIWIK_ENABLE_ERROR_HANDLER)
{
	require_once "core/ErrorHandler.php";
	require_once "core/ExceptionHandler.php";

	set_error_handler('Piwik_ErrorHandler');
	set_exception_handler('Piwik_ExceptionHandler');
}

session_cache_limiter('nocache');
if(strlen(session_id()) === 0)
{
	session_start();
}

if(!defined('PIWIK_ENABLE_DISPATCH') || PIWIK_ENABLE_DISPATCH)
{
	$controller = Piwik_FrontController::getInstance();
	$controller->init();
	$controller->dispatch();
}
