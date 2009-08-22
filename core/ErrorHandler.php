<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @category Piwik
 * @package Piwik
 */

if(!defined('E_STRICT')) {            define('E_STRICT', 2048); }
if(!defined('E_RECOVERABLE_ERROR')) { define('E_RECOVERABLE_ERROR', 4096); }
if(!defined('E_DEPRECATED')) {        define('E_DEPRECATED', 8192); }
if(!defined('E_USER_DEPRECATED')) {   define('E_USER_DEPRECATED', 16384); }

/**
 * Error handler used to display nicely errors in Piwik
 * 
 * @param int $errno Error number
 * @param string $errstring Error message
 * @param string $errfile File name
 * @param int $errline Line number
 */
function Piwik_ErrorHandler($errno, $errstr, $errfile, $errline)
{
	// if the error has been suppressed by the @ we don't handle the error
	if( error_reporting() == 0 )
	{
		return;
	}
	
	ob_start();
	debug_print_backtrace();
	$backtrace = ob_get_contents();
	ob_end_clean();

	try {
		Zend_Registry::get('logger_error')->logEvent($errno, $errstr, $errfile, $errline, $backtrace);
	} catch(Exception $e) {
		// in case the error occurs before the logger creation, we simply display it
		print("<pre>$errstr \nin '$errfile' at the line $errline\n\n$backtrace\n</pre>");
		exit;
	}
	switch($errno)
	{
		case E_ERROR:
		case E_PARSE:
		case E_CORE_ERROR:
		case E_CORE_WARNING:
		case E_COMPILE_ERROR:
		case E_COMPILE_WARNING:
		case E_USER_ERROR:
			exit;
		break;
		
		case E_WARNING:
		case E_NOTICE:
		case E_USER_WARNING:
		case E_USER_NOTICE:
		case E_STRICT:
		case E_RECOVERABLE_ERROR:
		case E_DEPRECATED:
		case E_USER_DEPRECATED:
		default:
			// do not exit
		break;
	}
}
