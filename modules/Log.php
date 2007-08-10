<?php
Zend_Loader::loadClass('Zend_Log');
Zend_Loader::loadClass('Zend_Log_Formatter_Interface');
Zend_Loader::loadClass('Zend_Log_Writer_Stream');
Zend_Loader::loadClass('Zend_Log_Writer_Db');

Zend_Loader::loadClass('Piwik_Common');

abstract class Piwik_Log extends Zend_Log
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
		Piwik::mkdir(Zend_Registry::get('config')->path->log);
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




?>
