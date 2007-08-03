<?php
/**
 * Misc Thoughts about optimization
 * 
 * - after a day is archived, we delete all the useless information from the log table, keeping only the useful data for weeks/month
 *   maybe we create a new table containing only these aggregate and we can delete the rows of the day in the log table
 * - To avoid join two huge tables (log_visit, log_link_visit_action) we may have to denormalize (idsite, date)#
 * -  
 */
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

	public function prefixTable( $suffix )
	{
		$prefix = Piwik_LogStats_Config::getInstance()->database['tables_prefix'];
		
		return $prefix . $suffix;
	}
	
	public function fetchAll( $query, $parameters )
	{
		try {
			$sth = $this->query( $query, $parameters );
			return $sth->fetchAll(PDO::FETCH_ASSOC);
		} catch (PDOException $e) {
			throw new Exception("Error query: ".$e->getMessage());
		}
	}
	public function fetch( $query, $parameters )
	{
		try {
			$sth = $this->query( $query, $parameters );
			return $sth->fetch(PDO::FETCH_ASSOC);
		} catch (PDOException $e) {
			throw new Exception("Error query: ".$e->getMessage());
		}
	}
	
	public function query($query, $parameters = array()) 
	{
		try {
			$sth = $this->connection->prepare($query);
			$sth->execute( $parameters );
			return $sth;
		} catch (PDOException $e) {
			throw new Exception("Error query: ".$e->getMessage());
		}
	}
	
	public function lastInsertId()
	{
		return  $this->connection->lastInsertId();
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
	
	 /*
	  * Specifications
	  *  
	  * - External file tracking
	  * 
	  *    * MANUAL Download tracking 
	  *      download = http://piwik.org/hellokity.zip
	  * 	(name = dir1/file alias name)
	  *
	  *    * AUTOMATIC Download tracking for a known list of file extensions. 
	  *    Make a hit to the piwik.php with the parameter: 
	  *      download = http://piwik.org/hellokity.zip
	  *  
	  *   When 'name' is not specified, 
	  * 	if AUTOMATIC and if anchor not empty => name = link title anchor
	  * 	else name = path+query of the URL
	  *   Ex: myfiles/beta.zip
	  *
	  * - External link tracking
	  * 
	  *    * MANUAL External link tracking
	  * 	 outlink = http://amazon.org/test
	  * 	(name = the big partners / amazon)
	  * 
	  *    * AUTOMATIC External link tracking
	  *      When a link is not detected as being part of the same website 
	  *     AND when the url extension is not detected as being a file download
	  * 	 outlink = http://amazon.org/test
	  * 
	  *  When 'name' is not specified, 
	  * 	if AUTOMATIC and if anchor not empty => name = link title anchor
	  * 	else name = URL
	  *   Ex: http://amazon.org/test
	  */
	private $actionName;
	private $url;
	private $defaultActionName;
	private $nameDownloadOutlink;
	
	const TYPE_ACTION   = 1;
	const TYPE_DOWNLOAD = 3;
	const TYPE_OUTLINK  = 2;
	
	function __construct( $db )
	{
		$this->actionName = Piwik_Common::getRequestVar( 'action_name', '', 'string');
		
		$downloadVariableName = Piwik_LogStats_Config::getInstance()->LogStats['download_url_var_name'];
		$this->downloadUrl = Piwik_Common::getRequestVar( $downloadVariableName, '', 'string');
		
		$outlinkVariableName = Piwik_LogStats_Config::getInstance()->LogStats['outlink_url_var_name'];
		$this->outlinkUrl = Piwik_Common::getRequestVar( $outlinkVariableName, '', 'string');
		
		$nameVariableName = Piwik_LogStats_Config::getInstance()->LogStats['download_outlink_name_var'];
		$this->nameDownloadOutlink = Piwik_Common::getRequestVar( $nameVariableName, '', 'string');
		
		$this->url = Piwik_Common::getRequestVar( 'url', '', 'string');
		$this->db = $db;
		$this->defaultActionName = Piwik_LogStats_Config::getInstance()->LogStats['default_action_name'];
	}
	
	/**
	 * About the Action concept:
	 * 
	 * - An action is defined by a name.
	 * - The name can be specified in the JS Code in the variable 'action_name'
	 * - Handling UTF8 in the action name
	 * PLUGIN_IDEA - An action is associated to URLs and link to the URL from the interface
	 * PLUGIN_IDEA - An action hit by a visitor is associated to the HTML title of the page that triggered the action
	 * 
	 * + If the name is not specified, we use the URL(path+query) to build a default name.
	 *   For example for "http://piwik.org/test/my_page/test.html" 
	 *   the name would be "test/my_page/test.html"
	 * 
	 * We make sure it is clean and displayable.
	 * If the name is empty we set it to a default name.
	 * 
	 * TODO UTF8 handling to test
	 * 
	 */
	 
	private function generateInfo()
	{
		if(!empty($this->downloadUrl))
		{
			$this->actionType = self::TYPE_DOWNLOAD;
			$url = $this->downloadUrl;
			$actionName = $this->nameDownloadOutlink;
		}
		elseif(!empty($this->outlinkUrl))
		{
			$this->actionType = self::TYPE_OUTLINK;
			$url = $this->outlinkUrl;
			$actionName = $this->nameDownloadOutlink;
			if( empty($actionName) )
			{
				$actionName = $url;
			}
		}
		else
		{
			$this->actionType = self::TYPE_ACTION;
			$url = $this->url;
			$actionName = $this->actionName;
		}		
		
		// the ActionName wasn't specified
		if( empty($actionName) )
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
		
		if(empty($actionName))
		{
			$actionName = $this->defaultActionName;
		}
		
		$this->finalActionName = $actionName;
	}
	
	/**
	 * Returns the idaction of the current action name.
	 * This idaction is used in the visitor logging table to link the visit information 
	 * (entry action, exit action) to the actions.
	 * This idaction is also used in the table that links the visits and their actions.
	 * 
	 * The methods takes care of creating a new record in the action table if the existing 
	 * action name doesn't exist yet.
	 * 
	 * @return int Id action
	 */
	function getActionId()
	{
		$this->loadActionId();
		return $this->idAction;
	}
	
	/**
	 * @see getActionId()
	 */
	private function loadActionId()
	{		
		$this->generateInfo();
		
		$name = $this->finalActionName;
		$type = $this->actionType;
		
		$idAction = $this->db->fetch("	SELECT idaction 
							FROM ".$this->db->prefixTable('log_action')
						."  WHERE name = ? AND type = ?", array($name, $type) );
		
		// the action name has not been found, create it
		if($idAction === false)
		{
			$this->db->query("INSERT INTO ". $this->db->prefixTable('log_action'). "( name, type ) 
								VALUES (?,?)",array($name,$type) );
			$idAction = $this->db->lastInsertId();
		}
		else
		{
			$idAction = $idAction['idaction'];
		}
		
		$this->idAction = $idAction;
	}
	
	/**
	 * Records in the DB the association between the visit and this action.
	 */
	 public function record( $idVisit, $idRefererAction, $timeSpentRefererAction)
	 {
	 	$this->db->query("INSERT INTO ".$this->db->prefixTable('log_link_visit_action')
						." (idvisit, idaction, idaction_ref, time_spent_ref_action) VALUES (?,?,?,?)",
					array($idVisit, $this->idAction, $idRefererAction, $timeSpentRefererAction)
					);
	 }
	 
}

class Piwik_LogStats_Visit
{
	
	function __construct( $db )
	{
		$this->db = $db;
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
			
			$this->updateCookie();
			
		}
	}
	
	private function updateCookie()
	{
		printDebug("We manage the cookie...");
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

		/*
		 * Configuration settings
		 */
		$plugin_Flash 			= Piwik_Common::getRequestVar( 'fla', 0, 'int');
		$plugin_Director 		= Piwik_Common::getRequestVar( 'dir', 0, 'int');
		$plugin_Quicktime		= Piwik_Common::getRequestVar( 'qt', 0, 'int');
		$plugin_RealPlayer 		= Piwik_Common::getRequestVar( 'realp', 0, 'int');
		$plugin_Pdf 			= Piwik_Common::getRequestVar( 'pdf', 0, 'int');
		$plugin_WindowsMedia 	= Piwik_Common::getRequestVar( 'wma', 0, 'int');
		$plugin_Java 			= Piwik_Common::getRequestVar( 'java', 0, 'int');
		$plugin_Cookie 			= Piwik_Common::getRequestVar( 'cookie', 0, 'int');
		
		$userAgent		= Piwik_Common::sanitizeInputValues(@$_SERVER['HTTP_USER_AGENT']);
		$aBrowserInfo	= Piwik_Common::getBrowserInfo($userAgent);
		$browserName	= $aBrowserInfo['name'];
		$browserVersion	= $aBrowserInfo['version'];
		
		$os				= Piwik_Common::getOs($userAgent);
		
		$resolution		= Piwik_Common::getRequestVar('res', 'unknown', 'string');
		$colorDepth		= Piwik_Common::getRequestVar('col', 32, 'numeric');
		


		/*
		 * General information
		 */
		$ip				= Piwik_Common::getIp();
		$ip 			= ip2long($ip);
		$localTime				= Piwik_Common::getRequestVar( 'h', date("H"), 'numeric')
							.':'. Piwik_Common::getRequestVar( 'm', date("i"), 'numeric')
							.':'. Piwik_Common::getRequestVar( 's', date("s"), 'numeric');
		
		$serverDate 	= date("Y-m-d");
		$serverTime 	= date("H:i:s");
		
		$idsite = Piwik_Common::getRequestVar('idsite', 0, 'int');
		if($idsite <= 0)
		{
			throw new Exception("The 'idsite' in the request is not acceptable.");
		}
		
		$idcookie = $this->getVisitorUniqueId();
		
		$defaultTimeOnePageVisit = Piwik_LogStats_Config::getInstance()->LogStats['default_time_one_page_visit'];
		
		/*
		 * Location information
		 */
		$browserLang	= Piwik_Common::sanitizeInputValues(@$_SERVER['HTTP_ACCEPT_LANGUAGE']);
		$country 		= Piwik_Common::getCountry($browserLang);
				
		$continent		= Piwik_Common::getContinent( $country );
		
		/*
		 * Misc
		 */
		$configurationHash = $this->getConfigHash( 
												$os,
												$browserName,
												$browserVersion,
												$resolution,
												$colorDepth,
												$plugin_Flash,
												$plugin_Director,
												$plugin_RealPlayer,
												$plugin_Pdf,
												$plugin_WindowsMedia,
												$plugin_Java,
												$plugin_Cookie,
												$ip,
												$browserLang);
		/**
		 * Init the action
		 */
		$action = new Piwik_LogStats_Action( $this->db );
		
		$actionId = $action->getActionId();
		
		printDebug("idAction = $actionId");
		
		
		/**
		 * Save the visitor
		 */
		
		$informationToSave = array(
			//'idvisit' => ,
			'idsite' 				=> $idsite,
			'visitor_localtime' 	=> $localTime,
			'visitor_idcookie' 		=> $idcookie,
			'visitor_returning' 	=> 0,
			'visitor_last_visit_time' => 0,
			'visit_server_date' 	=> $serverDate,
			'visit_server_time' 	=> $serverTime,
			'visit_exit_idaction' 	=> $actionId,
			'visit_entry_idaction' 	=> $actionId,
			'visit_total_actions' 	=> 1,
			'visit_total_time' 		=> $defaultTimeOnePageVisit,
			'referer_type' 			=> '',
			'referer_name' 			=> '',
			'referer_url' 			=> '',
			'referer_keyword' 		=> '',
			'config_md5config' 		=> $configurationHash,
			'config_os' 			=> $os,
			'config_browser_name' 	=> $browserName,
			'config_browser_version' => $browserVersion,
			'config_resolution' 	=> $resolution,
			'config_color_depth' 	=> $colorDepth,
			'config_pdf' 			=> $plugin_Pdf,
			'config_flash' 			=> $plugin_Flash,
			'config_java' 			=> $plugin_Java,
			'config_director' 		=> $plugin_Director,
			'config_quicktime' 		=> $plugin_Quicktime,
			'config_realplayer' 	=> $plugin_RealPlayer,
			'config_windowsmedia' 	=> $plugin_WindowsMedia,
			'config_cookie' 		=> $plugin_RealPlayer,
			'location_ip' 			=> $ip,
			'location_browser_lang' => $browserLang,
			'location_country' 		=> $country,
			'location_continent' 	=> $continent,
		);
		
		$fields = implode(", ", array_keys($informationToSave));
		$values = substr(str_repeat( "?,",count($informationToSave)),0,-1);
		
		$this->db->query( "INSERT INTO ".$this->db->prefixTable('log_visit').
						" ($fields) VALUES ($values)", array_values($informationToSave));
						
		$idVisit = $this->db->lastInsertId();
		
		/**
		 * Save the action
		 */
		$action->record( $idVisit, 0, 0 );
		
	}
	
	/**
	 * Compute the following information
	 * - referer_type
	 *		- direct			-- absence of referer URL
	 *		- site				-- based on the referer URL
	 *		- search_engine		-- based on the referer URL
	 *		- cpc				-- based on campaign URL parameter
	 *		- newsletter		-- based on campaign URL parameter
	 *		- partner			-- based on campaign URL parameter
	 *
	 * - referer_name
	 * 		- piwik.net
	 * 		- ()
	 * 		- google.fr
	 * 		- adwords-search
	 * 		- beta-release
	 * 		- my-nice-partner
	 * 		
	 * - referer_keyword
	 * 		- ()
	 * 		- ()
	 * 		- my keyword
	 * 		- my paid keyword
	 * 		- ()
	 * 		- ()
	 *  
	 * - referer_url : the same for all the referer types
	 * 
	 */
	private function handleReferer()
	{
		/*
		 * Referer analysis
		 */
		$refererUrl	= Piwik_Common::getRequestVar( 'urlref', '', 'string');
		$url		= Piwik_Common::getRequestVar( 'url', '', 'string');
		
		
		 /*
		  * 
		  * - Campaign tagging specification
		  *		* newsletter / beta-release
		  *     * partner / Amazon / [autofilled by piwik http://amazon.com/refererpage.html]
		  *   	* CPC / adwords-search / myKeyword
		  */
		  
		  
	}
	
	private function getConfigHash( $os, $browserName, $browserVersion, $resolution, $colorDepth, $plugin_Flash, $plugin_Director, $plugin_RealPlayer, $plugin_Pdf, $plugin_WindowsMedia, $plugin_Java, $plugin_Cookie, $ip, $browserLang)
	{
		return md5( $os . $browserName . $browserVersion . $resolution . $colorDepth . $plugin_Flash . $plugin_Director . $plugin_RealPlayer . $plugin_Pdf . $plugin_WindowsMedia . $plugin_Java . $plugin_Cookie . $ip . $browserLang );
	}
	
	private function getVisitorUniqueId()
	{
		if($this->isVisitorKnown())
		{
			return -1;
		}
		else
		{
			return Piwik_Common::generateUniqId();
		}
	}
		
}

printDebug($_GET);

class Piwik_LogStats
{	
	private $stateValid;
	
	private $urlToRedirect;
	
	private $db = null;
	
	const NOTHING_TO_NOTICE = 1;
	const TO_REDIRECT_URL = 2;
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
		$this->db = new Piwik_LogStats_Db( 	$configDb['host'], 
										$configDb['username'], 
										$configDb['password'], 
										$configDb['dbname']
							);  
		$this->db->connect();
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
		
		$downloadVariableName = Piwik_LogStats_Config::getInstance()->LogStats['download_url_var_name'];
		$urlDownload = Piwik_Common::getRequestVar( $downloadVariableName, '', 'string');
		
		if( !empty($urlDownload) )
		{
			$this->setState( self::TO_REDIRECT_URL );
			$this->setUrlToRedirect ( $urlDownload);
		}
		
		$outlinkVariableName = Piwik_LogStats_Config::getInstance()->LogStats['outlink_url_var_name'];
		$urlOutlink = Piwik_Common::getRequestVar( $outlinkVariableName, '', 'string');
		
		if( !empty($urlOutlink) )
		{
			$this->setState( self::TO_REDIRECT_URL );
			$this->setUrlToRedirect ( $urlOutlink);
		}
	}
	
	private function processVisit()
	{
		return $this->stateValid !== self::LOGGING_DISABLE
				&&  $this->stateValid !== self::NO_GET_VARIABLE;
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
	function main()
	{
		$this->initProcess();
		
		if( $this->processVisit() )
		{
			$this->connectDatabase();
			$visit = new Piwik_LogStats_Visit( $this->db );
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
			
			
			case self::TO_REDIRECT_URL:
				header('Location: ' . $this->getUrlToRedirect());
			break;
			
			
			case self::NOTHING_TO_NOTICE:
			default:
				printDebug("Nothing to notice => default behaviour");
			break;
		}
		printDebug("End of the page.");
		exit;
	}
}

$process = new Piwik_LogStats;
$process->main();

// yet to do
// known visitor test 1h
// known visitor update 1h
// referer analysis 3h
// unit testing the module 7h
ob_end_flush();
?>
