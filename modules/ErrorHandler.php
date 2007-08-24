<?php
function Piwik_ErrorHandler($errno, $errstr, $errfile, $errline)
{
    ob_start();
    debug_print_backtrace();
    $backtrace = ob_get_contents();
    ob_end_clean();
    Zend_Registry::get('logger_error')->log($errno, $errstr, $errfile, $errline, $backtrace);
    
	switch($errno)
	{
		case E_ERROR:
		case E_PARSE:
		case E_CORE_ERROR:
		case E_CORE_WARNING:
		case E_COMPILE_ERROR:
		case E_COMPILE_WARNING:
		case E_USER_ERROR:
		case E_EXCEPTION:
			exit;
		break;
		
		case E_WARNING:
		case E_NOTICE:
		case E_USER_WARNING:
		case E_USER_NOTICE:
		case E_STRICT:
		case E_RECOVERABLE_ERROR:
		default:
			// do not exit
		break;
    }
}
?>
