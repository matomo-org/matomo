<?php

function Piwik_ExceptionHandler(Exception $exception) 
{
	try	{
		Zend_Registry::get('logger_exception')->log($exception);
	} catch(Exception $e) {
		print("<br> <b>Exception</b>: '". $exception->getMessage()."'");
		
		print("<br> -------------------------- <br>
			This exception occured and also raised this exception: ");
		print("'" . $e->getMessage()."'");
	}
}
?>
