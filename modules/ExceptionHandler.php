<?php
/**
 * 
 * @package Piwik
 */
function Piwik_ExceptionHandler(Exception $exception) 
{
	try	{
		Zend_Registry::get('logger_exception')->log($exception);
	} catch(Exception $e) {
		// case when the exception is raised before the logger being ready
		// we handle the exception a la mano, but using the Logger formatting properties
		$formatter = new Piwik_Log_Formatter_Exception_ScreenFormatter;
		$message = $formatter->format($e);
		Piwik::exitWithErrorMessage( $message );
	}
}

