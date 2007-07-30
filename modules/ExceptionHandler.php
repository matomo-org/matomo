<?php

function Piwik_ExceptionHandler(Exception $exception) 
{
	try	{
		Zend_Registry::get('logger_exception')->log($exception);
	} catch(Exception $e) {
		print("<br> -------------------------- <br>An exception occured while dealing with an uncaught exception... <br>");
		print("'" . $e->getMessage()."'");
		print("<br> The initial exception was: <br>'". $exception->getMessage()."'");
	}
}
?>
