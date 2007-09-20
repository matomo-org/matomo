<?php
/**
 * To maximise the performance of the logging module, we use different techniques.
 * 
 * On the PHP-only side:
 * - minimize the number of external files included. 
 * 	 Ideally only one (the configuration file) in all the normal cases.
 *   We load the Loggers only when an error occurs ; this error is logged in the DB/File/etc
 *   depending on the loggers settings in the configuration file.
 * - we may have to include external classes but we try to include only very 
 *   simple code without any dependency, so that we could simply write a script
 *   that would merge all this simple code into a big piwik.php file.
 * 
 * On the Database-related side:
 * - write all the SQL queries without using any DB abstraction layer.
 * 	 Of course we carefully filter all input values.
 * - minimize the number of SQL queries necessary to complete the algorithm.
 * - carefully index the tables used
 * - try to have fixed length rows
 * 
 * [ - use a partitionning by date for the tables ]
 *   
 * - handle the timezone settings??
 * 
 * [ - country detection plugin => ip lookup ]
 * [ - precise country detection plugin ]
 * 
 * We could also imagine a batch system that would read a log file every 5min,
 * and which prepares the file containg the rows to insert, then we load DATA INFILE 
 * 
 * 
 * @package Piwik_LogStats
 */

/**
 * Configuration options for the statsLogEngine module:
 * - use_cookie  ; defines if we try to get/set a cookie to help recognize a unique visitor
 */


class Piwik_LogStats
{	
	private $stateValid;
	
	private $urlToRedirect;
	
	private $db = null;
	
	const STATE_NOTHING_TO_NOTICE = 1;
	const STATE_TO_REDIRECT_URL = 2;
	const STATE_LOGGING_DISABLE = 10;
	const STATE_NO_GET_VARIABLE = 11;
		
	const COOKIE_INDEX_IDVISITOR 				= 1;
	const COOKIE_INDEX_TIMESTAMP_LAST_ACTION 	= 2;
	const COOKIE_INDEX_TIMESTAMP_FIRST_ACTION 	= 3;
	const COOKIE_INDEX_ID_VISIT 				= 4;
	const COOKIE_INDEX_ID_LAST_ACTION 			= 5;
	
	const VISIT_STANDARD_LENGTH = 1800;
	
	public function __construct()
	{
		$this->stateValid = self::STATE_NOTHING_TO_NOTICE;
	}
	
	// create the database object
	function connectDatabase()
	{
		$configDb = Piwik_LogStats_Config::getInstance()->database;
		$this->db = new Piwik_LogStats_Db( 	$configDb['host'], 
										$configDb['username'], 
										$configDb['password'], 
										$configDb['dbname']
							);  
		$this->db->connect();
	}

	private function initProcess()
	{
		Piwik_PluginsManager::getInstance()->setPluginsToLoad( 
				Piwik_LogStats_Config::getInstance()->Plugins_LogStats['enabled'] 
			);
		
		$saveStats = Piwik_LogStats_Config::getInstance()->LogStats['record_statistics'];
		
		if($saveStats == 0)
		{
			$this->setState(self::STATE_LOGGING_DISABLE);
		}
		
		if( count($_GET) == 0)
		{
			$this->setState(self::STATE_NO_GET_VARIABLE);			
		}
		
		$downloadVariableName = Piwik_LogStats_Config::getInstance()->LogStats['download_url_var_name'];
		$urlDownload = Piwik_Common::getRequestVar( $downloadVariableName, '', 'string');
		
		if( !empty($urlDownload) )
		{
			$this->setState( self::STATE_TO_REDIRECT_URL );
			$this->setUrlToRedirect ( $urlDownload);
		}
		
		$outlinkVariableName = Piwik_LogStats_Config::getInstance()->LogStats['outlink_url_var_name'];
		$urlOutlink = Piwik_Common::getRequestVar( $outlinkVariableName, '', 'string');
		
		if( !empty($urlOutlink) )
		{
			$this->setState( self::STATE_TO_REDIRECT_URL );
			$this->setUrlToRedirect ( $urlOutlink);
		}
	}
	
	private function processVisit()
	{
		return $this->stateValid !== self::STATE_LOGGING_DISABLE
				&&  $this->stateValid !== self::STATE_NO_GET_VARIABLE;
	}
	private function getState()
	{
		return $this->stateValid;
	}
	
	private function setUrlToRedirect( $url )
	{
		$this->urlToRedirect = $url;
	}
	private function getUrlToRedirect()
	{
		return $this->urlToRedirect;
	}
	private function setState( $value )
	{
		$this->stateValid = $value;
	}
	
	// main algorithm 
	// => input : variables filtered
	// => action : read cookie, read database, database logging, cookie writing
	function main( $class_LogStats_Visit = "Piwik_LogStats_Visit")
	{
		$this->initProcess();
		
		if( $this->processVisit() )
		{
			$this->connectDatabase();
			$visit = new $class_LogStats_Visit( $this->db );
			$visit->handle();
		}
		$this->endProcess();
	}	

	// display the logo or pixel 1*1 GIF
	// or a marketing page if no parameters in the url
	// or redirect to a url (transmit the cookie as well)
	// or load a URL (rss feed) (transmit the cookie as well)
	protected function endProcess()
	{
		switch($this->getState())
		{
			case self::STATE_LOGGING_DISABLE:
				printDebug("Logging disabled, display transparent logo");
			break;
			
			case self::STATE_NO_GET_VARIABLE:
				printDebug("No get variables => piwik page");
			break;
			
			
			case self::STATE_TO_REDIRECT_URL:
				$this->sendHeader('Location: ' . $this->getUrlToRedirect());
			break;
			
			
			case self::STATE_NOTHING_TO_NOTICE:
			default:
				printDebug("Nothing to notice => default behaviour");
				$trans_gif_64 = "R0lGODlhAQABAJEAAAAAAP///////wAAACH5BAEAAAIALAAAAAABAAEAAAICVAEAOw==";
				header("Content-type: image/gif");
				print(base64_decode($trans_gif_64));
			break;
		}
		printDebug("End of the page.");
	}
	
	protected function sendHeader($header)
	{
		header($header);
	}
}



function printDebug( $info = '' )
{
	if(isset($GLOBALS['DEBUGPIWIK']) && $GLOBALS['DEBUGPIWIK'])
	{
		if(is_array($info))
		{
			print("<PRE>");
			print(var_export($info,true));
			print("</PRE>");
		}
		else
		{
			print($info . "<br>\n");
		}
	}
}

