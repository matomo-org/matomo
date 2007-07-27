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
		
		$this->logToFileFilename = $logToFileFilename;
		$this->fileFormatter = $fileFormatter;
		$this->screenFormatter = $screenFormatter;
		$this->logToDatabaseTableName = $logToDatabaseTableName;
		$this->logToDatabaseColumnMapping = $logToDatabaseColumnMapping;
	}
	
	static public function dump($var, $label=null)
	{
		Zend_Registry::get('LoggerMessages')->log(Zend_Debug::dump($var, $label, false), Piwik_Log::DEBUG);
	}
	
	function addWriteToFile()
	{
		$writerFile = new Zend_Log_Writer_Stream($this->logToFileFilename);
		$writerFile->setFormatter( $this->fileFormatter );
		$this->addWriter($writerFile);
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
		if(isset($event['priority']))
		{
	        if (! isset($this->_priorities[$event['priority']])) {
	            throw new Zend_Log_Exception('Bad log priority');
	        }
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
    	$str = implode(" ", $event);
    	return $str;
    }
}

class Piwik_Log_Formatter_ScreenFormatter implements Zend_Log_Formatter_Interface
{
	/**
     * Formats data into a single line to be written by the writer.
     *
     * @param  array    $event    event data
     * @return string             formatted line to write to the log
     */
    public function format($event)
    {
    	$str = '';
    	foreach($event as $name => $value)
    	{
    		$str .= "$name : $value \n<br>";
    	}
    	return $str;
    }
}

class Piwik_Log_APICalls extends Piwik_Log
{
	function __construct()
	{
		$logToFileFilename = 'api_call';
		$logToDatabaseTableName = 'log_api_calls';//TODO generalize
		$logToDatabaseColumnMapping = null;
		$screenFormatter = new Piwik_Log_Formatter_ScreenFormatter;
		$fileFormatter = new Piwik_Log_Formatter_FileFormatter;
		
		parent::__construct($logToFileFilename, 
							$fileFormatter,
							$screenFormatter,
							$logToDatabaseTableName, 
							$logToDatabaseColumnMapping );
		
		$this->setEventItem('ip', ip2long( Piwik::getIp() ) );
	}
	
	function log( $methodName, $parameters, $executionTime)
	{
		$event = array();
		$event['methodName'] = $methodName;
		$event['parameters'] = serialize($parameters);
		$event['executionTime'] = $executionTime;
		
		parent::log($event);
	}
}

class Piwik_Log_Messages extends Piwik_Log
{
	function __construct()
	{
		$logToFileFilename = 'message';
		$logToDatabaseTableName = 'log_message';//TODO generalize
		$logToDatabaseColumnMapping = null;
		$screenFormatter = new Piwik_Log_Formatter_ScreenFormatter;
		$fileFormatter = new Piwik_Log_Formatter_FileFormatter;
		
		parent::__construct($logToFileFilename, 
							$fileFormatter,
							$screenFormatter,
							$logToDatabaseTableName, 
							$logToDatabaseColumnMapping );
		
		$this->setEventItem('ip', ip2long( Piwik::getIp() ) );
	}
	
	public function log( $message )
	{
		$event = array();
		$event['message'] = $message;
		
		parent::log($event);
	}
}

?>
