<?php

error_reporting(E_ALL|E_NOTICE);
define('PIWIK_INCLUDE_PATH', '.');

@ignore_user_abort(true);
@set_time_limit(0);

set_include_path(PIWIK_INCLUDE_PATH 
					. PATH_SEPARATOR . PIWIK_INCLUDE_PATH . '/libs/'
					. PATH_SEPARATOR . PIWIK_INCLUDE_PATH . '/core/'
					. PATH_SEPARATOR . PIWIK_INCLUDE_PATH . '/modules'
					. PATH_SEPARATOR . PIWIK_INCLUDE_PATH . '/core/models'
					. PATH_SEPARATOR . get_include_path() );

require_once "Event/Dispatcher.php";
require_once "Common.php";

function printDebug( $info = '' )
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

ob_start();

/*
 * Some benchmarks
 * 
 * - with the config parsing + db connection
 * Requests per second:    471.91 [#/sec] (mean)
 * 
 * 
 */

/**
 * Simple database PDO wrapper
 * 
 */
class Piwik_LogStats_Db 
{
	private $connection;
	private $username;
	private $password;
	
	public function __construct( $host, $username, $password, $dbname) 
	{
		$this->dsn = "mysql:dbname=$dbname;host=$host";
		$this->username = $username;
		$this->password = $password;
	}

	public function connect() 
	{
		try {
			$pdoConnect = new PDO($this->dsn, $this->username, $this->password);
			$pdoConnect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->connection = $pdoConnect;
		} catch (PDOException $e) {
			throw new Exception("Error connecting database: ".$e->getMessage());
		}
	}

	public function fetchAll( $query )
	{
		try {
			$sth = $this->connexion->prepare($query);
			$sth->execute();
			return $sth->fetchAll(PDO::FETCH_ASSOC);
		} catch (PDOException $e) {
			throw new Exception("Error query: ".$e->getMessage());
		}
	}
	
	public function query($query) 
	{
		if (!$this->connection->query($query)) {
			throw new Exception($this->connection->errorInfo());
		} else {
			return true;
		}
	}
}

/**
 * Simple class to access the configuration file
 */
class Piwik_LogStats_Config
{
	static private $instance = null;
	
	static public function getInstance()
	{
		if (self::$instance == null)
		{			
			$c = __CLASS__;
			self::$instance = new $c();
		}
		return self::$instance;
	}
	
	public $config = array();
	
	private function __construct()
	{
		$pathIniFile = PIWIK_INCLUDE_PATH . '/config/config.ini.php';
		$this->config = parse_ini_file($pathIniFile, true);
	}
	
	public function __get( $name )
	{
		if(isset($this->config[$name]))
		{
			return $this->config[$name];
		}
		else
		{
			throw new Exception("The config element $name is not available in the configuration (check the configuration file).");
		}
	}
}


/**
 * Plugin specification for a statistics logging plugin
 * 
 * A plugin that display data in the Piwik Interface is very different from a plugin 
 * that will save additional data in the database during the statistics logging. 
 * These two types of plugins don't have the same requirements at all. Therefore a plugin
 * that saves additional data in the database during the stats logging process will have a different
 * structure.
 * 
 * A plugin for logging data has to focus on performance and therefore has to stay as simple as possible.
 * For input data, it is strongly advised to use the Piwik methods available in Piwik_Common 
 *
 * Things that can be done with such a plugin:
 * - having a dependency with a list of other plugins
 * - have an install step that would prepare the plugin environment
 * 		- install could add columns to the tables
 * 		- install could create tables 
 * - register to hooks at several points in the logging process
 * - register to hooks in other plugins
 * - generally a plugin method can modify data (filter) and add/remove data 
 * 
 * 
 */ 
class Piwik_PluginsManager
{
	public $dispatcher;
	private $pluginsPath;
	
	static private $instance = null;
	
	static public function getInstance()
	{
		if (self::$instance == null)
		{			
			$c = __CLASS__;
			self::$instance = new $c();
		}
		return self::$instance;
	}
	
	private function __construct()
	{
		$this->pluginsPath = 'plugins/';
		$this->pluginsCategory = 'LogsStats/';
		
		$this->dispatcher = Event_Dispatcher::getInstance();
		$this->loadPlugins();
	}
	
	/**
	 * Load the plugins classes installed.
	 * Register the observers for every plugin.
	 * 
	 */
	public function loadPlugins()
	{
		$defaultPlugins = array(
			array( 'fileName' => 'Provider', 'className' => 'Piwik_Plugin_LogStats_Provider' ),
		//	'Piwik_Plugin_LogStats_UserSettings',
		);
		
		foreach($defaultPlugins as $pluginInfo)
		{
			$pluginFileName = $pluginInfo['fileName'];
			$pluginClassName = $pluginInfo['className'];
			/*
			// TODO make sure the plugin name is secure
			// make sure thepluigin is a child of Piwik_Plugin
			$path = PIWIK_INCLUDE_PATH 
					. $this->pluginsPath 
					. $this->pluginsCategory
					. $pluginFileName . ".php";
			
			if(is_file($path))
			{
				throw new Exception("The plugin file $path couldn't be found.");
			}
			
			require_once $path;
			*/
			
			$newPlugin = new $pluginClassName;
			
			$this->addPluginObservers( $newPlugin );
		}
	}
	
	/**
	 * For the given plugin, add all the observers of this plugin.
	 */
	private function addPluginObservers( Piwik_Plugin $plugin )
	{
		$hooks = $plugin->getListHooksRegistered();
		
		foreach($hooks as $hookName => $methodToCall)
		{
			$this->dispatcher->addObserver( array( $plugin, $methodToCall) );
		}
	}
	
}

/**
 * Post an event to the dispatcher which will notice the observers
 */
function Piwik_PostEvent( $eventName, $object = null, $info = array() )
{
	printDebug("Dispatching event $eventName...");
	Piwik_PluginsManager::getInstance()->dispatcher->post( $object, $eventName, $info, false, false );
}

/**
 * Abstract class to define a Piwik_Plugin.
 * Any plugin has to at least implement the abstract methods of this class.
 */
abstract class Piwik_Plugin
{
	/**
	 * Returns the plugin details
	 */
	abstract function getInformation();
	
	/**
	 * Returns the list of hooks registered with the methods names
	 */
	abstract function getListHooksRegistered();
	
	/**
	 * Returns the names of the required plugins
	 */
	public function getListRequiredPlugins()
	{
		return array();
	}
	 
	/**
	 * Install the plugin
	 * - create tables
	 * - update existing tables
	 * - etc.
	*/
	public function install()
	{
		return;
	}
	  
	/**
	 * Remove the created resources during the install
	 */
	public function uninstall()
	{
		return;
	}
}



class Piwik_Plugin_LogStats_Provider extends Piwik_Plugin
{	
	public function __construct()
	{
	}

	public function getInformation()
	{
		$info = array(
			'name' => 'LogProvider',
			'description' => 'Log in the DB the hostname looked up from the IP',
			'author' => 'Piwik',
			'homepage' => 'http://piwik.org/plugins/LogProvider',
			'version' => '0.1',
		);
		
		return $info;
	}
	
	function install()
	{
		// add column hostname / hostname ext in the visit table
	}
	
	function uninstall()
	{
		// add column hostname / hostname ext in the visit table
	}
	
	function getListHooksRegistered()
	{
		$hooks = array(
			'LogsStats.NewVisitor' => 'detectHostname'
		);
		return $hooks;
	}
	
	function detectHostname( $notification )
	{
		$object = $notification->getNotificationObject();
		var_dump($object);printDebug();
	}
}
/*
class Piwik_Plugin_LogStats_UserSettings extends Piwik_Plugin
{
	
}*/

Piwik_PostEvent( 'LogsStats.NewVisitor' );

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
 * 
 * [ - use a partitionning by date for the tables ]
 *   
 * - handle the timezone settings??
 * 
 * [ - country detection plugin => ip lookup ]
 * [ - precise country detection plugin ]
 * 
 * 
 * 
 */

/**
 * Configuration options for the statsLogEngine module:
 * - use_cookie  ; defines if we try to get/set a cookie to help recognize a unique visitor
 */

/**
 * Simple class to handle the cookies.
 * Its features are:
 * 
 * - read a cookie values
 * - edit an existing cookie and save it
 * - create a new cookie, set values, expiration date, etc. and save it
 * 
 * The cookie content is saved in an optimized way.
 */
class Piwik_LogStats_Cookie
{
	/**
	 * The name of the cookie 
	 */
	protected $name = null;
	
	/**
	 * The expire time for the cookie (expressed in UNIX Timestamp)
	 */
	protected $expire = null;
	
	/**
	 * The content of the cookie
	 */
	protected $value = array();
	
	const VALUE_SEPARATOR = ':';
	
	public function __construct( $cookieName, $expire = null)
	{
		$this->name = $cookieName;
		
		if(is_null($expire)
			|| !is_numeric($expire)
			|| $expire <= 0)
		{
			$this->expire = $this->getDefaultExpire();
		}
		
		if($this->isCookieFound())
		{
			$this->loadContentFromCookie();
		}
	}
	
	public function isCookieFound()
	{
		return isset($_COOKIE[$this->name]);
	}
	
	protected function getDefaultExpire()
	{
		return 86400*365*10;
	}	
	
	/**
	 * taken from http://usphp.com/manual/en/function.setcookie.php
	 * fix expires bug for IE users (should i say expected to fix the bug in 2.3 b2)
	 * TODO use the other parameters of the function
	 */
	protected function setCookie($Name, $Value, $Expires, $Path = '', $Domain = '', $Secure = false, $HTTPOnly = false)
	{
		if (!empty($Domain))
		{	
			// Fix the domain to accept domains with and without 'www.'.
			if (strtolower(substr($Domain, 0, 4)) == 'www.')  $Domain = substr($Domain, 4);
			
			$Domain = '.' . $Domain;
			
			// Remove port information.
			$Port = strpos($Domain, ':');
			if ($Port !== false)  $Domain = substr($Domain, 0, $Port);
		}
		
		header('Set-Cookie: ' . rawurlencode($Name) . '=' . rawurlencode($Value)
		 . (empty($Expires) ? '' : '; expires=' . gmdate('D, d-M-Y H:i:s', $Expires) . ' GMT')
		 . (empty($Path) ? '' : '; path=' . $Path)
		 . (empty($Domain) ? '' : '; domain=' . $Domain)
		 . (!$Secure ? '' : '; secure')
		 . (!$HTTPOnly ? '' : '; HttpOnly'), false);
	}
	
	protected function setP3PHeader()
	{
		header("P3P: CP='OTI DSP COR NID STP UNI OTPa OUR'");
	}
	
	public function deleteCookie()
	{
		$this->setP3PHeader();
		setcookie($this->name, false, time() - 86400);
	}
	
	public function save()
	{
		$this->setP3PHeader();
		$this->setCookie( $this->name, $this->generateContentString(), $this->expire);
	}
	
	/**
	 * Load the cookie content into a php array 
	 */
	protected function loadContentFromCookie()
	{
		$cookieStr = $_COOKIE[$this->name];
		
		$values = explode( self::VALUE_SEPARATOR, $cookieStr);
		foreach($values as $nameValue)
		{
			$equalPos = strpos($nameValue, '=');
			$varName = substr($nameValue,0,$equalPos);
			$varValue = substr($nameValue,$equalPos+1);
			
			// no numeric value are base64 encoded so we need to decode them
			if(!is_numeric($varValue))
			{
				$varValue = base64_decode($varValue);
				
				// some of the values may be serialized array so we try to unserialize it
				if( ($arrayValue = @unserialize($varValue)) !== false
					// we set the unserialized version only for arrays as you can have set a serialized string on purpose
					&& is_array($arrayValue) 
					)
				{
					$varValue = $arrayValue;
				}
			}
			
			$this->set($varName, $varValue);
		}
	}
	
	/**
	 * Returns the string to save in the cookie frpm the $this->value array of values
	 * 
	 */
	public function generateContentString()
	{
		$cookieStr = '';
		foreach($this->value as $name=>$value)
		{
			if(is_array($value))
			{
				$value = base64_encode(serialize($value));
			}
			elseif(is_string($value))
			{
				$value = base64_encode($value);
			}
			
			$cookieStr .= "$name=$value" . self::VALUE_SEPARATOR;
		}
		$cookieStr = substr($cookieStr, 0, strlen($cookieStr)-1);
		return $cookieStr;
	}
	
	/**
	 * Registers a new name => value association in the cookie.
	 * 
	 * Registering new values is optimal if the value is a numeric value.
	 * If the value is a string, it will be saved as a base64 encoded string.
	 * If the value is an array, it will be saved as a serialized and base64 encoded 
	 * string which is not very good in terms of bytes usage. 
	 * You should save arrays only when you are sure about their maximum data size.
	 * 
	 * @param string Name of the value to save; the name will be used to retrieve this value
	 * @param string|array|numeric Value to save
	 * 
 	 */
	public function set( $name, $value )
	{
		$name = self::escapeValue($name);
		$this->value[$name] = $value;
	}
	
	/**
	 * Returns the value defined by $name from the cookie.
	 * 
	 * @param string|integer Index name of the value to return
	 * @return mixed The value if found, false if the value is not found
	 */
	public function get( $name )
	{
		$name = self::escapeValue($name);
		return isset($this->value[$name]) ? self::escapeValue($this->value[$name]) : false;
	}
	
	public function __toString()
	{
		$str = "<-- Content of the cookie '{$this->name}' <br>\n";
		foreach($this->value as $name => $value )
		{
			$str .= $name . " = " . var_export($this->get($name), true) . "<br>\n";
		}
		$str .= "--> <br>\n";
		return $str;
	}
	
	static protected function escapeValue( $value )
	{
		return Piwik_Common::sanitizeInputValues($value);
	}	
}

//
//$c = new Piwik_LogStats_Cookie( 'piwik_logstats', 86400);
//echo $c;
//$c->set(1,1);
//$c->set('test',1);
//$c->set('test2','test=432:gea785');
//$c->set('test3',array('test=432:gea785'));
//$c->set('test4',array(array(0=>1),1=>'test'));
//echo $c;
//echo "<br>";
//echo $c->generateContentString();
//echo "<br>";
//$v=$c->get('more!');
//if(empty($v)) $c->set('more!',1);
//$c->set('more!', array($c->get('more!')));
//$c->save();
//$c->deleteCookie();

class Piwik_LogStats_Action
{
	function __construct( $actionName, $currentUrl)
	{
		$this->actionName = $actionName;
		$this->url = $currentUrl;
	}
	
	/**
	 * About the Action concept:
	 * 
	 * - An action is defined by a name.
	 * - The name can be specified in the JS Code in the variable 'action_name'
	 * - An action is associated to a URL
	 * - Handling UTF8 in the action name
	 * 
	 * + If the name is not specified, we use the URL(path+query) to build a default name.
	 *   For example for "http://piwik.org/test/my_page/test.html" 
	 *   the name would be "test/my_page/test.html"
	 * 
	 * We make sure it is clean and displayable.
	 * If the name is empty we set it to a default name.
	 * 
	 */
	function getName()
	{
		$actionName = $this->actionName;
		$url = $this->url;
		
		// the ActionName wasn't specified
		if($actionName === false)
		{
			$parsedUrl = parse_url( $url );
			
			$actionName = '';
			
			if(isset($parsedUrl['path']))
			{
				$actionName .= substr($parsedUrl['path'], 1);
			}
			
			if(isset($parsedUrl['query']))
			{
				$actionName .= '?'.$parsedUrl['query'];
			}
		}
		
		// clean the name
		$actionName = str_replace(array("\n", "\r"), '', $actionName);
				
	}
	
	/**
	 * A query to the Piwik statistics logging engine is associated to 1 action.
	 * 
	 * We have to save the action for the current visit.
	 * - check the action exists already in the db
	 * - save the relation between idvisit and idaction
	 */
	function save()
	{}
}

class Piwik_LogStats_Visit
{
	
	function __construct()
	{
	}
	
	// test if the visitor is excluded because of
	// - IP
	// - cookie
	// - configuration option?
	private function isExcluded()
	{
		$excluded = 0;
		
		if($excluded)
		{
			printDebug("Visitor excluded.");
			return true;
		}
		
		return false;
	}
	
	/**
	 * Handles the visitor.
	 * 
	 * We have to split the visitor into one of the category 
	 * - Known visitor
	 * - New visitor
	 * 
	 * A known visitor is a visitor that has already visited the website in the current month.
	 * We define a known visitor using (in order of importance):
	 * 1) A cookie that contains
	 * 		// a unique id for the visitor
	 * 		- id_visitor 
	 * 
	 * 		// the timestamp of the last action in the most recent visit
	 * 		- timestamp_last_action 
	 * 
 	 *  	// the timestamp of the first action in the most recent visit
	 * 		- timestamp_first_action
	 * 
	 * 		// the ID of the most recent visit (which could be in the past or the current visit)
	 * 		- id_visit 
	 * 
	 * 		// the ID of the most recent action
	 * 		- id_last_action
	 * 2) If the visitor doesn't have a cookie, we try to look for a similar visitor configuration.
	 * 	  We search for a visitor with the same plugins/OS/Browser/Resolution for today for this website.
	 */
	private function recognizeTheVisitor()
	{
		$this->visitorKnown = false;
	}
	
	private function isVisitorKnown()
	{
		return $this->visitorKnown === true;
	}
	
	/**
	 * Once we have the visitor information, we have to define if the visit is a new or a known visit.
	 * 
	 * 1) When the last action was done more than 30min ago, 
	 * 	  or if the visitor is new, then this is a new visit.
	 *	
	 * 2) If the last action is less than 30min ago, then the same visit is going on. 
	 *	Because the visit goes on, we can get the time spent during the last action.
	 *
	 * NB:
	 *  - In the case of a new visit, then the time spent 
	 *	during the last action of the previous visit is unknown.
	 * 
	 *	- In the case of a new visit but with a known visitor, 
	 *	we can set the 'returning visitor' flag.
	 *
	 */
	 
	/**
	 * In all the cases we set a cookie to the visitor with the new information.
	 */
	public function handle()
	{
		if(!$this->isExcluded())
		{
			$this->recognizeTheVisitor();
			
			// known visitor
			if($this->isVisitorKnown())
			{
				if($this->isLastActionInTheSameVisit())
				{
					$this->handleKnownVisit();
				}
				else
				{
					$this->handleNewVisit();
				}
			}
			// new visitor
			else
			{
				$this->handleNewVisit();
			}
		}
	}
	
	/**
	 * In the case of a known visit, we have to do the following actions:
	 * 
	 * 1) Insert the new action
	 * 
	 * 2) Update the visit information
	 */
	private function handleKnownVisit()
	{
		printDebug("Visit known.");
	}
	
	/**
	 * In the case of a new visit, we have to do the following actions:
	 * 
	 * 1) Insert the new action
	 * 
	 * 2) Insert the visit information
	 */
	private function handleNewVisit()
	{
		printDebug("New Visit.");
		
		/**
		 * Get the variables from the REQUEST 
		 */
		$plugin_Flash 			= Piwik_Common::getRequestVar( 'fla', 0, 'int');
		$plugin_Director 		= Piwik_Common::getRequestVar( 'dir', 0, 'int');
		$plugin_RealPlayer 		= Piwik_Common::getRequestVar( 'realp', 0, 'int');
		$plugin_Pdf 			= Piwik_Common::getRequestVar( 'pdf', 0, 'int');
		$plugin_WindowsMedia 	= Piwik_Common::getRequestVar( 'wma', 0, 'int');
		$plugin_Java 			= Piwik_Common::getRequestVar( 'java', 0, 'int');
		$plugin_Cookie 			= Piwik_Common::getRequestVar( 'cookie', 0, 'int');
		
		$localTime				= Piwik_Common::getRequestVar( 'h', date("H"), 'numeric')
							.':'. Piwik_Common::getRequestVar( 'm', date("i"), 'numeric')
							.':'. Piwik_Common::getRequestVar( 's', date("s"), 'numeric');

		$userAgent		= Piwik_Common::sanitizeInputValues(@$_SERVER['HTTP_USER_AGENT']);
		$aBrowserInfo	= Piwik_Common::getBrowserInfo($userAgent);
		$os				= Piwik_Common::getOs($userAgent);
		
		$resolution		= Piwik_Common::getRequestVar('res', 'unknown', 'string');
		$colorDept		= Piwik_Common::getRequestVar('col', 32, 'numeric');
		
		$urlReferer		= Piwik_Common::getRequestVar( 'urlref', '', 'string');

		$ip				= Piwik_Common::getIp();
		
		$serverDate 	= date("Y-m-d");
		$serverTime 	= date("H:i:s");
		
		$browserLang	= Piwik_Common::sanitizeInputValues(@$_SERVER['HTTP_ACCEPT_LANGUAGE']);
		$country 		= Piwik_Common::getCountry($browserLang);
				
		$continent		= Piwik_Common::getContinent( $country );
		
		/**
		 * Init the action
		 */
		$action = new Piwik_LogStats_Action;
		
		$actionId = $action->getActionId();
		
		
		/**
		 * Save the visitor
		 */
		$visitorId = 1;
		
		
		/**
		 * Save the action
		 */
		$action->save( $visitorId );
				
			/*	CREATE TABLE log_visit (
		  idvisit INTEGER(10) UNSIGNED NOT NULL,
		  idsite INTEGER(10) UNSIGNED NOT NULL,
		  visitor_localtime TIME NOT NULL DEFAULT 00:00:00,
		  visitor_idcookie CHAR(32) NOT NULL,
		  visitor_returning TINYINT(1) NOT NULL,
		  visitor_last_visit_time TIME NOT NULL DEFAULT 00:00:00,
		  visit_server_date DATE NOT NULL,
		  visit_server_time TIME NOT NULL DEFAULT 00:00:00,
		  visit_exit_idpage INTEGER(11) NOT NULL,
		  visit_entry_idpage INTEGER(11) NOT NULL,
		  visit_entry_idpageurl INTEGER(11) NOT NULL,
		  visit_total_pages SMALLINT(5) UNSIGNED NOT NULL,
		  visit_total_time SMALLINT(5) UNSIGNED NOT NULL,
		  referer_type INTEGER UNSIGNED NULL,
		  referer_name VARCHAR(70) NULL,
		  referer_url TEXT NOT NULL,
		  referer_keyword VARCHAR(255) NULL,
		  config_md5config CHAR(32) NOT NULL,
		  -config_os CHAR(3) NOT NULL,
		  -config_browser_name VARCHAR(10) NOT NULL,
		  -config_browser_version VARCHAR(20) NOT NULL,
		  -config_resolution VARCHAR(9) NOT NULL,
		  -config_color_depth TINYINT(2) UNSIGNED NOT NULL,
		  -config_pdf TINYINT(1) NOT NULL,
		  -config_flash TINYINT(1) NOT NULL,
		  -config_java TINYINT(1) NOT NULL,
		  -config_javascript TINYINT(1) NOT NULL,
		  -config_director TINYINT(1) NOT NULL,
		  -config_quicktime TINYINT(1) NOT NULL,
		  -config_realplayer TINYINT(1) NOT NULL,
		  -config_windowsmedia TINYINT(1) NOT NULL,
		  -config_cookie TINYINT(1) NOT NULL,
		  -location_ip BIGINT(11) NOT NULL,
		  -location_browser_lang VARCHAR(20) NOT NULL,
		  -location_country CHAR(3) NOT NULL,
		  -location_continent CHAR(3) NOT NULL,
		  PRIMARY KEY(idvisit)
		);
		*/
	}
}

printDebug($_GET);

class Piwik_LogStats
{	
	private $stateValid;
	
	const NOTHING_TO_NOTICE = 1;
	const LOGGING_DISABLE = 10;
	const NO_GET_VARIABLE = 11;
	
	public function __construct()
	{
		$this->stateValid = self::NOTHING_TO_NOTICE;
	}
	
	// create the database object
	function connectDatabase()
	{
		$configDb = Piwik_LogStats_Config::getInstance()->database;
		$db = new Piwik_LogStats_Db( 	$configDb['host'], 
										$configDb['username'], 
										$configDb['password'], 
										$configDb['dbname']
							);  
		$db->connect();
	}
	
	private function initProcess()
	{
		
		$saveStats = Piwik_LogStats_Config::getInstance()->LogStats['record_statistics'];
		if($saveStats == 0)
		{
			$this->setState(self::LOGGING_DISABLE);
		}
		
		if( count($_GET) == 0)
		{
			$this->setState(self::NO_GET_VARIABLE);			
		}
	}
	
	private function processVisit()
	{
		return $this->stateValid === self::NOTHING_TO_NOTICE;
	}
	private function getState()
	{
		return $this->stateValid;
	}
	private function setState( $value )
	{
		$this->stateValid = $value;
	}
	
	// main algorithm 
	// => input : variables filtered
	// => action : read cookie, read database, database logging, cookie writing
	function main()
	{
		$this->initProcess();
		
		if( $this->processVisit() )
		{
			$visit = new Piwik_LogStats_Visit;
			$visit->handle();
		}
		$this->endProcess();
	}	
	
	// display the logo or pixel 1*1 GIF
	// or a marketing page if no parameters in the url
	// or redirect to a url (transmit the cookie as well)
	// or load a URL (rss feed) (transmit the cookie as well)
	private function endProcess()
	{
		switch($this->getState())
		{
			case self::LOGGING_DISABLE:
				printDebug("Logging disabled, display transparent logo");
			break;
			case self::NO_GET_VARIABLE:
				printDebug("No get variables => piwik page");
			break;
			case self::NOTHING_TO_NOTICE:
			default:
				printDebug("Nothing to notice => default behaviour");
			break;
		}
		printDebug("End of the page.");
	}
}

$process = new Piwik_LogStats;
$process->main();

ob_end_flush();
?>
