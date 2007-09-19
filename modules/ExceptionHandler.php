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
		print("<br> <b>Exception</b>: '". $exception->getMessage()."'<br>");
		
		print("<br><small>Backtrace:<br><pre>");
		print($exception->getTraceAsString());
		print("</pre>");
		print("-------------------------- <br>
			This exception occured and also raised this exception: ");
		print("'" . $e->getMessage()."'");
		print("</small>");
	}
}

