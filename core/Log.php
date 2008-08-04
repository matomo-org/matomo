<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: Log.php 558 2008-07-20 23:10:38Z matt $
 * 
 * @package Piwik_Log
 */

require_once "Zend/Log.php";
require_once "Zend/Log/Formatter/Interface.php";
require_once "Zend/Log/Writer/Stream.php";
require_once "Zend/Log/Writer/Db.php";

require_once "Common.php";

/**
 * 
 * @package Piwik_Log
 */
abstract class Piwik_Log extends Zend_Log
{
	protected $logToDatabaseTableName = null;
	protected $logToDatabaseColumnMapping = null;
	protected $logToFileFilename = null;
	protected $fileFormatter = null;
	protected $screenFormatter = null;
	
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
	
	static public function dump($var)
	{
		Zend_Registry::get('logger_message')->log(var_export($var, true), Piwik_Log::DEBUG);
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
	 * Overload Zend_log::log
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

/**
 * 
 * 
 * @package Piwik_Log
 */
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

class Piwik_Log_Formatter_ScreenFormatter implements Zend_Log_Formatter_Interface
{
	function format($string)
	{
		$string = self::getFormattedString($string);
		return $string;
	}
	
	static public function getFormattedString($string)
	{
		if(Piwik::isPhpCliMode())
		{
			$string = str_replace(array('<br>','<br />','<br/>'), "\n", $string);
			$string = strip_tags($string);
		}
		return $string;
	}
}

