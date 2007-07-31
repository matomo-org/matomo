<?php
Zend_Loader::loadClass('Zend_Log');
Zend_Loader::loadClass('Zend_Log');
Zend_Loader::loadClass('Zend_Log_Formatter_Interface');
Zend_Loader::loadClass('Zend_Log_Writer_Stream');
Zend_Loader::loadClass('Zend_Log_Writer_Db');


class Piwik_Log extends Zend_Log
{
	private $logToDatabaseTableName = null;
	private $logToDatabaseColumnMapping = null;
	private $logToFileFilename = null;
	private $fileFormatter = null;
	private $screenFormatter = null;
	
	function __construct( 	$logToFileFilename, 
							$fileFormatter,
							$screenFormatter,
							$logToDatabaseTableName, 
							$logToDatabaseColumnMapping )
	{
		parent::__construct();
		
		Piwik::mkdir(Zend_Registry::get('config')->path->log);
		
		$this->logToFileFilename = Zend_Registry::get('config')->path->log . $logToFileFilename;
		$this->fileFormatter = $fileFormatter;
		$this->screenFormatter = $screenFormatter;
		$this->logToDatabaseTableName = Piwik::prefixTable($logToDatabaseTableName);
		$this->logToDatabaseColumnMapping = $logToDatabaseColumnMapping;
	}
	
	static public function dump($var, $label=null)
	{
		Zend_Registry::get('logger_message')->log(Zend_Debug::dump($var, $label, false), Piwik_Log::DEBUG);
	}
	
	function addWriteToFile()
	{
		$writerFile = new Zend_Log_Writer_Stream($this->logToFileFilename);
		$writerFile->setFormatter( $this->fileFormatter );
		$this->addWriter($writerFile);
	}
	
	function addWriteToNull()
	{
		Zend_Loader::loadClass('Zend_Log_Writer_Null');
		$this->addWriter( new Zend_Log_Writer_Null );
	}
	
	function addWriteToDatabase()
	{
		$writerDb = new Zend_Log_Writer_Db(
								Zend_Registry::get('db'), 
								$this->logToDatabaseTableName, 
								$this->logToDatabaseColumnMapping);
		
		$this->addWriter($writerDb);
	}
	
	function addWriteToScreen()
	{
		$writerScreen = new Zend_Log_Writer_Stream('php://output');
		$writerScreen->setFormatter( $this->screenFormatter );
		$this->addWriter($writerScreen);
	}
	
	public function getWritersCount()
	{
		return count($this->_writers);
	}
	
    /**
     * Log an event
     * Overload Zend_log::log cos its too weak for our requirements
     */
    public function log($event)
    {
        // sanity checks
        if (empty($this->_writers)) {
            throw new Zend_Log_Exception('No writers were added');
        }
        
		$event['timestamp'] = date('c');
		
        // pack into event required by filters and writers
        $event = array_merge( $event, $this->_extras);

        // abort if rejected by the global filters
        foreach ($this->_filters as $filter) {
            if (! $filter->accept($event)) {
                return;
            }
        }

        // send to each writer
        foreach ($this->_writers as $writer) {
            $writer->write($event);
        }
    }
	
}

class Piwik_Log_Formatter_FileFormatter implements Zend_Log_Formatter_Interface
{
	/**
     * Formats data into a single line to be written by the writer.
     *
     * @param  array    $event    event data
     * @return string             formatted line to write to the log
     */
    public function format($event)
    {
    	foreach($event as &$value)
    	{
    		$value = str_replace("\n", '\n', $value);
    		$value = '"'.$value.'"';
    	}
    	$str = implode(" ", $event) . "\n";
    	return $str;
    }
}

class Piwik_Log_Formatter_Message_ScreenFormatter implements Zend_Log_Formatter_Interface
{
	/**
     * Formats data into a single line to be written by the writer.
     *
     * @param  array    $event    event data
     * @return string             formatted line to write to the log
     */
    public function format($event)
    {
    	return $event['message'];
    }
}
class Piwik_Log_Formatter_APICall_ScreenFormatter implements Zend_Log_Formatter_Interface
{
	/**
     * Formats data into a single line to be written by the writer.
     *
     * @param  array    $event    event data
     * @return string             formatted line to write to the log
     */
    public function format($event)
    {
    	$str =  "\n<br> ";
    	$str .= "Called: {$event['class_name']}.{$event['method_name']} (took {$event['execution_time']}ms) \n<br>";
    	$str .= "Parameters: ";
    	$parameterNamesAndDefault = unserialize($event['parameter_names_default_values']);
    	$parameterValues = unserialize($event['parameter_values']);
    	
    	$i = 0; 
    	foreach($parameterNamesAndDefault as $pName => $pDefault)
    	{
    		if(isset($parameterValues[$i]))
    		{
	    		$currentValue = $parameterValues[$i];
    		}
    		else
    		{
    			$currentValue = $pDefault;
    		}
    		
    		$currentValue = $this->formatValue($currentValue);
    		$str .= "$pName = $currentValue, ";
    		
    		$i++;
    	}
    	$str .=  "\n<br> ";
    	
    	$str .= "Returned: ".$this->formatValue($event['returned_value']);
    	$str .=  "\n<br> ";
    	return $str;
    }
    
    private function formatValue( $value )
    {
    	if(is_string($value))
		{
			$value = "'$value'";
		}
		if(is_null($value))
		{
			$value= 'null';
		}
		if(is_array($value))
		{
			$value = "array( ".implode(", ", $value). ")";
		}
		return $value;
		
    }
}


class Piwik_Log_Null extends Zend_Log
{
	public function __construct()
	{
	}
	
	public function log($message, $priority = Zend_Log::INFO )
	{
		parent::log($message, $priority);
	}
}

class Piwik_Log_APICall extends Piwik_Log
{
	const ID = 'logger_api_call';
	function __construct()
	{
		$logToFileFilename = self::ID;
		$logToDatabaseTableName = self::ID;
		$logToDatabaseColumnMapping = null;
		$screenFormatter = new Piwik_Log_Formatter_APICall_ScreenFormatter;
		$fileFormatter = new Piwik_Log_Formatter_FileFormatter;
		
		parent::__construct($logToFileFilename, 
							$fileFormatter,
							$screenFormatter,
							$logToDatabaseTableName, 
							$logToDatabaseColumnMapping );
		
		$this->setEventItem('caller_ip', ip2long( Piwik::getIp() ) );
	}
	
	function log( $className, $methodName, $parameterNames,	$parameterValues, $executionTime, $returnedValue)
	{
		$event = array();
		$event['class_name'] = $className;
		$event['method_name'] = $methodName;
		$event['parameter_names_default_values'] = serialize($parameterNames);
		$event['parameter_values'] = serialize($parameterValues);
		$event['execution_time'] = $executionTime;
		$event['returned_value'] = is_array($returnedValue) ? serialize($returnedValue) : $returnedValue;
		
		parent::log($event);
	}
}

class Piwik_Log_Message extends Piwik_Log
{
	const ID = 'logger_message';
	function __construct()
	{
		$logToFileFilename = self::ID;
		$logToDatabaseTableName = self::ID;
		$logToDatabaseColumnMapping = null;
		$screenFormatter = new Piwik_Log_Formatter_Message_ScreenFormatter;
		$fileFormatter = new Piwik_Log_Formatter_FileFormatter;
		
		parent::__construct($logToFileFilename, 
							$fileFormatter,
							$screenFormatter,
							$logToDatabaseTableName, 
							$logToDatabaseColumnMapping );
	}
	
	public function log( $message )
	{
		$event = array();
		$event['message'] = $message;
		
		parent::log($event);
	}
}

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
	    if($errno == 0) return '';
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

?>
