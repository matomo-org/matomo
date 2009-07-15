<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @package Piwik_Tracker
 */

/**
 * Class used by the logging script piwik.php called by the javascript tag.
 * Handles the visitor & his/her actions on the website, saves the data in the DB, 
 * saves information in the cookie, etc.
 * 
 * We try to include as little files as possible (no dependency on 3rd party modules).
 * 
 * @package Piwik_Tracker
 */
class Piwik_Tracker
{	
	protected $stateValid = self::STATE_NOTHING_TO_NOTICE;
	protected $urlToRedirect;
	
	/**
	 * @var Piwik_Tracker_Db
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
	const COOKIE_INDEX_REFERER_ID_VISIT			= 6;
	const COOKIE_INDEX_REFERER_TIMESTAMP		= 7;
	const COOKIE_INDEX_REFERER_TYPE				= 8;
	const COOKIE_INDEX_REFERER_NAME				= 9;
	const COOKIE_INDEX_REFERER_KEYWORD			= 10;
	const COOKIE_INDEX_VISITOR_RETURNING		= 11;
	
	public function __construct() 
	{
		$this->request = $_GET + $_POST;
	}

	public function main()
	{
		$this->init();
		
		if( $this->isVisitValid() )
		{
			try {
				self::connectDatabase();
				
				$visit = $this->getNewVisitObject();
				$visit->setRequest($this->request);
				$visit->handle();
				unset($visit);
			} catch (PDOException $e) {
				printDebug($e->getMessage());
			} catch(Piwik_Tracker_Visit_Excluded $e) {
			}
		}
		$this->end();
	}	
	
	/**
	 * Returns the date in the "Y-m-d H:i:s" PHP format
	 * @return string
	 */
	public static function getDatetimeFromTimestamp($timestamp)
	{
		return date("Y-m-d H:i:s", $timestamp);
	}
	
	
	protected function init()
	{
		$this->loadTrackerPlugins();
		$this->handleDisabledTracker();
		$this->handleEmptyGetVariable();
		$this->handleDownloadRedirect();
		$this->handleOutlinkRedirect();
	}

	// display the logo or pixel 1*1 GIF
	// or a marketing page if no parameters in the url
	// or redirect to a url
	// or load a URL (rss feed) (forward the cookie as well)
	protected function end()
	{
		switch($this->getState())
		{
			case self::STATE_LOGGING_DISABLE:
				printDebug("Logging disabled, display transparent logo");
				$this->outputTransparentGif();
			break;
			
			case self::STATE_NO_GET_VARIABLE:
				printDebug("No get variables => Piwik page");
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
		
		if($GLOBALS['PIWIK_TRACKER_DEBUG'] === true)
		{
			Piwik::printSqlProfilingReportTracker(self::$db);
		}
		
		self::disconnectDatabase();
	}
	
	public static function connectPiwikTrackerDb()
	{
		$db = null;
		$configDb = Piwik_Tracker_Config::getInstance()->database;
		
		if(!isset($configDb['port']))
		{
			// before 0.2.4 there is no port specified in config file
			$configDb['port'] = '3306';  
		}
		$db = new Piwik_Tracker_Db( 	$configDb['host'], 
										$configDb['username'], 
										$configDb['password'], 
										$configDb['dbname'],
										$configDb['port'] );
		$db->connect();
		
		return $db;
	}
	
	public static function connectDatabase()
	{
		if( !is_null(self::$db))
		{
			return;
		}
		
		$db = null;
		Piwik_PostEvent('Tracker.createDatabase', $db);
		if(is_null($db))
		{
			$db = self::connectPiwikTrackerDb();
		}
		self::$db = $db;
	}
	
	/**
	 * @return Piwik_Tracker_Db
	 */
	public static function getDatabase()
	{
		return self::$db;
	}

	public static function disconnectDatabase()
	{
		if(isset(self::$db))
		{
			self::$db->disconnect();
			self::$db = null;
		}
	}

	/**
	 * Returns the Tracker_Visit object.
	 * This method can be overwritten to use a different Tracker_Visit object
	 *
	 * @return Piwik_Tracker_Visit
	 */
	protected function getNewVisitObject()
	{
		$visit = null;
		Piwik_PostEvent('Tracker.getNewVisitObject', $visit);
	
		if(is_null($visit))
		{
			$visit = new Piwik_Tracker_Visit();
		}
		elseif(!($visit instanceof Piwik_Tracker_Visit_Interface ))
		{
			throw new Exception("The Visit object set in the plugin must implement Piwik_Tracker_Visit_Interface");
		}
		return $visit;
	}
	
	protected function outputTransparentGif()
	{
		if( !isset($GLOBALS['PIWIK_TRACKER_DEBUG']) || !$GLOBALS['PIWIK_TRACKER_DEBUG'] ) 
		{
			$trans_gif_64 = "R0lGODlhAQABAIAAAAAAAAAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==";
			header("Content-type: image/gif");
			print(base64_decode($trans_gif_64));
		}
	}
	
	protected function sendHeader($header)
	{
		header($header);
	}
	
	protected function isVisitValid()
	{
		return $this->stateValid !== self::STATE_LOGGING_DISABLE
				&&  $this->stateValid !== self::STATE_NO_GET_VARIABLE;
	}
	
	protected function getState()
	{
		return $this->stateValid;
	}
	
	protected function setUrlToRedirect( $url )
	{
		$this->urlToRedirect = $url;
	}
	
	protected function getUrlToRedirect()
	{
		return $this->urlToRedirect;
	}
	
	protected function setState( $value )
	{
		$this->stateValid = $value;
	}

	protected function loadTrackerPlugins()
	{
		try{
			$pluginsTracker = Piwik_Tracker_Config::getInstance()->Plugins_Tracker;
			if(is_array($pluginsTracker)
				&& count($pluginsTracker) != 0)
			{
				Piwik_PluginsManager::getInstance()->doNotLoadAlwaysActivatedPlugins();
				Piwik_PluginsManager::getInstance()->setPluginsToLoad( $pluginsTracker['Plugins_Tracker'] );
				
				printDebug("Loading plugins: { ". implode(",", $pluginsTracker['Plugins_Tracker']) . "}");
			}
		} catch(Exception $e) {
			printDebug("ERROR: ".$e->getMessage());
		}
	}
	
	protected function handleDownloadRedirect()
	{
		$downloadVariableName = Piwik_Tracker_Config::getInstance()->Tracker['download_url_var_name'];
		$urlDownload = Piwik_Common::getRequestVar( $downloadVariableName, '', 'string', $this->request);

		if( !empty($urlDownload) )
		{
			$redirectVariableName = Piwik_Tracker_Config::getInstance()->Tracker['download_redirect_var_name'];
			if( Piwik_Common::getRequestVar( $redirectVariableName, 1, 'int', $this->request) == 1)
			{
				$this->setState( self::STATE_TO_REDIRECT_URL );
				$this->setUrlToRedirect ( $urlDownload );
			}
		}
	}
	
	protected function handleOutlinkRedirect()
	{
		$outlinkVariableName = Piwik_Tracker_Config::getInstance()->Tracker['outlink_url_var_name'];
		$urlOutlink = Piwik_Common::getRequestVar( $outlinkVariableName, '', 'string', $this->request);
		
		if( !empty($urlOutlink) )
		{
			$redirectVariableName = Piwik_Tracker_Config::getInstance()->Tracker['outlink_redirect_var_name'];
			if( Piwik_Common::getRequestVar( $redirectVariableName, 1, 'int', $this->request) == 1)
			{
				$this->setState( self::STATE_TO_REDIRECT_URL );
				$this->setUrlToRedirect ( $urlOutlink);
			}
		}
	}
	
	protected function handleEmptyGetVariable()
	{
		if( count($_GET) == 0)
		{
			$this->setState(self::STATE_NO_GET_VARIABLE);			
		}
	}
	
	protected function handleDisabledTracker()
	{
		$saveStats = Piwik_Tracker_Config::getInstance()->Tracker['record_statistics'];
		if($saveStats == 0)
		{
			$this->setState(self::STATE_LOGGING_DISABLE);
		}
	}
}

function printDebug( $info = '' )
{
	if(isset($GLOBALS['PIWIK_TRACKER_DEBUG']) && $GLOBALS['PIWIK_TRACKER_DEBUG'])
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
