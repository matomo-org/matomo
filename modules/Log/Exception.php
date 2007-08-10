<?php

class Piwik_Log_Exception extends Piwik_Log
{
	const ID = 'logger_exception';
	function __construct()
	{
		$logToFileFilename = self::ID;
		$logToDatabaseTableName = self::ID;
		$logToDatabaseColumnMapping = null;
		$screenFormatter = new Piwik_Log_Formatter_Exception_ScreenFormatter;
		$fileFormatter = new Piwik_Log_Formatter_FileFormatter;
		
		parent::__construct($logToFileFilename, 
							$fileFormatter,
							$screenFormatter,
							$logToDatabaseTableName, 
							$logToDatabaseColumnMapping );
	}
	
	public function log($exception)
	{
		
		$event = array();
		$event['errno'] 	= $exception->getCode();
		$event['message'] 	= $exception->getMessage();
		$event['errfile'] 	= $exception->getFile();
		$event['errline'] 	= $exception->getLine();
		$event['backtrace'] = $exception->getTraceAsString();
		
		parent::log($event);
	}
}


class Piwik_Log_Formatter_Exception_ScreenFormatter implements Zend_Log_Formatter_Interface
{
	/**
     * Formats data into a single line to be written by the writer.
     *
     * @param  array    $event    event data
     * @return string             formatted line to write to the log
     */
    public function format($event)
    {
		$errno = $event['errno'] ;
		$errstr = $event['message'] ;
		$errfile = $event['errfile'] ;
		$errline = $event['errline'] ;
		$backtrace = $event['backtrace'] ;
		
		$strReturned = '';
	    $strReturned .= "\n<div style='word-wrap: break-word; border: 3px solid red; padding:4px; width:70%; background-color:#FFFF96;'><b>";
	    $strReturned .= "Exception uncaught</b> <i>$errstr</i> in <b>$errfile</b> on line <b>$errline</b>\n";
	    $strReturned .= "<br><br>Backtrace --><DIV style='font-family:Courier;font-size:10pt'>";
	    $strReturned .= str_replace("\n", "<br>", $backtrace);
	    $strReturned .= "</div><br><br>";
	    $strReturned .= "\n</pre></div><br>";
	    
	    return $strReturned;
    }
}




?>
