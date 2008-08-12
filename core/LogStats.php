<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: LogStats.php 575 2008-07-26 23:08:32Z matt $
 * 
 * @package Piwik_LogStats
 */

/**
 * Class used by the logging script piwik.php called by the javascript tag.
 * Handles the visitor & his/her actions on the website, saves the data in the DB, saves information in the cookie, etc.
 * 
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
 * We could also imagine a batch system that would read a log file every 5min,
 * and which prepares the file containg the rows to insert, then we load DATA INFILE 
 * 
 * 
 * Configuration options for the statsLogEngine module:
 * - use_cookie  ; defines if we try to get/set a cookie to help recognize a unique visitor
 * 
 * @package Piwik_LogStats
 */
class Piwik_LogStats
{	
	protected $stateValid;
	
	protected $urlToRedirect;
	
	/**
	 *
	 * @var Piwik_LogStats_Db
	 */
	static protected $db = null;
	
	const STATE_NOTHING_TO_NOTICE = 1;
	const STATE_TO_REDIRECT_URL = 2;
	const STATE_LOGGING_DISABLE = 10;
	const STATE_NO_GET_VARIABLE = 11;
		
	const COOKIE_INDEX_IDVISITOR 				= 1;
	const COOKIE_INDEX_TIMESTAMP_LAST_ACTION 	= 2;
	const COOKIE_INDEX_TIMESTAMP_FIRST_ACTION 	= 3;
	const COOKIE_INDEX_ID_VISIT 				= 4;
	const COOKIE_INDEX_ID_LAST_ACTION 			= 5;
	
	public function __construct()
	{
		$this->stateValid = self::STATE_NOTHING_TO_NOTICE;
	}
	
	static function connectDatabase()
	{
		if( !is_null(self::$db))
		{
			return;
		}
		
		$db = null;
		Piwik_PostEvent('Tracker.createDatabase', $db);
		if(is_null($db))
		{
			$configDb = Piwik_LogStats_Config::getInstance()->database;
			
			// we decode the password. Password is html encoded because it's enclosed between " double quotes
			$configDb['password'] = htmlspecialchars_decode($configDb['password']);
			if(!isset($configDb['port']))
			{
				// before 0.2.4 there is no port specified in config file
				$configDb['port'] = '3306';  
			}
			
			$db = new Piwik_LogStats_Db( 	$configDb['host'], 
												$configDb['username'], 
												$configDb['password'], 
												$configDb['dbname'],
												$configDb['port'] );
			$db->connect();
		}
		self::$db = $db;
	}
	
	public static function getDb()
	{
		return self::$db;
	}

	static function disconnectDb()
	{
		if(isset(self::$db))
		{
			self::$db->disconnect();
		}
	}
	
	private function initProcess()
	{
		try{
			$pluginsLogStats = Piwik_LogStats_Config::getInstance()->Plugins_LogStats;
			if(is_array($pluginsLogStats)
				&& count($pluginsLogStats) != 0)
			{
				Piwik_PluginsManager::getInstance()->doNotLoadAlwaysActivatedPlugins();
				Piwik_PluginsManager::getInstance()->setPluginsToLoad( $pluginsLogStats['Plugins_LogStats'] );
			}
		} catch(Exception $e) {		
		}
		
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
			if( Piwik_Common::getRequestVar( 'redirect', 1, 'int') == 1)
			{
				$this->setState( self::STATE_TO_REDIRECT_URL );
			}
			$this->setUrlToRedirect ( $urlDownload);
		}
		
		$outlinkVariableName = Piwik_LogStats_Config::getInstance()->LogStats['outlink_url_var_name'];
		$urlOutlink = Piwik_Common::getRequestVar( $outlinkVariableName, '', 'string');
		
		if( !empty($urlOutlink) )
		{
			if( Piwik_Common::getRequestVar( 'redirect', 1, 'int') == 1)
			{
				$this->setState( self::STATE_TO_REDIRECT_URL );
			}
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
	
	/**
	 * Returns the LogStats_Visit object.
	 * This method can be overwritten so that we use a different LogStats_Visit object
	 *
	 * @return Piwik_LogStats_Visit
	 */
	protected function getNewVisitObject()
	{
		$visit = null;
		Piwik_PostEvent('LogStats.getNewVisitObject', $visit);
	
		if(is_null($visit))
		{
			$visit = new Piwik_LogStats_Visit();
		}
		elseif(!($visit instanceof Piwik_LogStats_Visit_Interface ))
		{
			throw new Exception("The Visit object set in the plugin must implement Piwik_LogStats_Visit_Interface");
		}
		
		$visit->setDb(self::$db);
		return $visit;
	}
	
	// main algorithm 
	// => input : variables filtered
	// => action : read cookie, read database, database logging, cookie writing
	function main()
	{
		$this->initProcess();
		
		if( $this->processVisit() )
		{
			try {
				self::connectDatabase();
				$visit = $this->getNewVisitObject();
				$visit->handle();
			} catch (PDOException $e) {
				$this->setState(self::STATE_LOGGING_DISABLE);
			}				
		}
		$this->endProcess();
	}	

	// display the logo or pixel 1*1 GIF
	// or a marketing page if no parameters in the url
	// or redirect to a url
	// or load a URL (rss feed) (transmit the cookie as well)
	protected function endProcess()
	{
		switch($this->getState())
		{
			case self::STATE_LOGGING_DISABLE:
				printDebug("Logging disabled, display transparent logo");
				$this->outputTransparentGif();
			break;
			
			case self::STATE_NO_GET_VARIABLE:
				printDebug("No get variables => piwik page");
				echo "<a href='index.php'>Piwik</a> is a free open source <a href='http://piwik.org'>web analytics</a> alternative to Google analytics.";
			break;
			
			
			case self::STATE_TO_REDIRECT_URL:
				$this->sendHeader('Location: ' . $this->getUrlToRedirect());
			break;
			
			
			case self::STATE_NOTHING_TO_NOTICE:
			default:
				printDebug("Nothing to notice => default behaviour");
				$this->outputTransparentGif();
			break;
		}
		printDebug("End of the page.");
		
		if($GLOBALS['DEBUGPIWIK'] === true)
		{
			Piwik::printSqlProfilingReportLogStats(self::$db);
		}
		
		self::disconnectDb();
	}
	
	protected function outputTransparentGif()
	{
		if( !isset($GLOBALS['DEBUGPIWIK']) || !$GLOBALS['DEBUGPIWIK'] ) 
		{
			$trans_gif_64 = "R0lGODlhAQABAJEAAAAAAP///////wAAACH5BAEAAAIALAAAAAABAAEAAAICVAEAOw==";
			header("Content-type: image/gif");
			print(base64_decode($trans_gif_64));
		}
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
