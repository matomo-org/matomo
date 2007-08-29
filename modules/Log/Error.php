<?php

/**
 * Class used to log an error event.
 * 
 * @package Piwik_Log
 * @subpackage Piwik_Log_Error
 */
class Piwik_Log_Error extends Piwik_Log
{
	const ID = 'logger_error';
	function __construct()
	{
		$logToFileFilename = self::ID;
		$logToDatabaseTableName = self::ID;
		$logToDatabaseColumnMapping = null;
		$screenFormatter = new Piwik_Log_Formatter_Error_ScreenFormatter;
		$fileFormatter = new Piwik_Log_Formatter_FileFormatter;
		
		parent::__construct($logToFileFilename, 
							$fileFormatter,
							$screenFormatter,
							$logToDatabaseTableName, 
							$logToDatabaseColumnMapping );
	}
	
	public function log($errno, $errstr, $errfile, $errline, $backtrace)
	{
		$event = array();
		$event['errno'] = $errno;
		$event['message'] = $errstr;
		$event['errfile'] = $errfile;
		$event['errline'] = $errline;
		$event['backtrace'] = $backtrace;
		
		parent::log($event);
	}
}



/**
 * Format an error event to be displayed on the screen.
 * 
 * @package Piwik_Log
 * @subpackage Piwik_Log_Error
 */
class Piwik_Log_Formatter_Error_ScreenFormatter implements Zend_Log_Formatter_Interface
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
	    $errno = $errno & error_reporting();
	    //if($errno == 0) return '';
	    if(!defined('E_STRICT'))            define('E_STRICT', 2048);
	    if(!defined('E_RECOVERABLE_ERROR')) define('E_RECOVERABLE_ERROR', 4096);
	    if(!defined('E_EXCEPTION')) 		define('E_EXCEPTION', 8192);
	    $strReturned .= "\n<div style='word-wrap: break-word; border: 3px solid red; padding:4px; width:70%; background-color:#FFFF96;'><b>";
	    switch($errno)
	    {
	        case E_ERROR:               $strReturned .=  "Error";                  break;
	        case E_WARNING:             $strReturned .=  "Warning";                break;
	        case E_PARSE:               $strReturned .=  "Parse Error";            break;
	        case E_NOTICE:              $strReturned .=  "Notice";                 break;
	        case E_CORE_ERROR:          $strReturned .=  "Core Error";             break;
	        case E_CORE_WARNING:        $strReturned .=  "Core Warning";           break;
	        case E_COMPILE_ERROR:       $strReturned .=  "Compile Error";          break;
	        case E_COMPILE_WARNING:     $strReturned .=  "Compile Warning";        break;
	        case E_USER_ERROR:          $strReturned .=  "User Error";             break;
	        case E_USER_WARNING:        $strReturned .=  "User Warning";           break;
	        case E_USER_NOTICE:         $strReturned .=  "User Notice";            break;
	        case E_STRICT:              $strReturned .=  "Strict Notice";          break;
	        case E_RECOVERABLE_ERROR:   $strReturned .=  "Recoverable Error";      break;
	        case E_EXCEPTION:   		$strReturned .=  "Exception";      break;
	        default:                    $strReturned .=  "Unknown error ($errno)"; break;
	    }
	    $strReturned .= ":</b> <i>$errstr</i> in <b>$errfile</b> on line <b>$errline</b>\n";
	    $strReturned .= "<br><br>Backtrace --><DIV style='font-family:Courier;font-size:10pt'>";
	    $strReturned .= str_replace("\n", "<br>", $backtrace);
	    $strReturned .= "</div><br><br>";
	    $strReturned .= "\n</pre></div><br>";
	    
	    return $strReturned;
    }
}


