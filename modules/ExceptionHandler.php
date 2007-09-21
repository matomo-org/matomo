<?php
/**
 * 
 * @package Piwik
 */
function Piwik_ExceptionHandler(Exception $exception) 
{
	try	{
		Zend_Registry::get('logger_exception')->log($exception);
	} catch(Exception $exception) {
		// case when the exception is raised before the logger being ready
		// we handle the exception a la mano, but using the Logger formatting properties
		require_once "Log/Exception.php";
		$event = array();
		$event['errno'] 	= $exception->getCode();
		$event['message'] 	= $exception->getMessage();
		$event['errfile'] 	= $exception->getFile();
		$event['errline'] 	= $exception->getLine();
		$event['backtrace'] = $exception->getTraceAsString();
		
		$formatter = new Piwik_Log_Formatter_Exception_ScreenFormatter;
		$message = $formatter->format($event);
		Piwik::exitWithErrorMessage( $message );
	}
}

