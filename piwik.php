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

function printDebug( $str = '' )
{
	print($str . "<br>\n");
}



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
	
//Constante com caminhos para includes
define("PATH_INCLUDES", $_SERVER['DOCUMENT_ROOT']."/conn");

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
			throw new Exception("Error connecting database: ".$e->getMessage());
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
 * - 
 */




class Piwik_LogStats_Action
{
	/**
	 * About the Action concept:
	 * 
	 * - An action is defined by a name.
	 * - The name can be specified in the JS Code in the variable 'action_name'
	 * - If the name is not specified, we use the URL to build a name based on the path.
	 *   For example for "http://piwik.org/test/my_page/test.html" 
	 *   the name would be "test/my_page/test.html"
	 * - An action is associated to a URL
	 * 
	 */
	function getName()
	{}
	
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
		
	}
	
	private function isVisitorKnown()
	{
		
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
	}
}


class Piwik_LogStats
{	
	private $stateValid;
	
	const NOT_SPECIFIED   = 1;
	const LOGGING_DISABLE = 10;
	const NO_GET_VARIABLE = 11;
	
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
		$this->stateValid = self::NOT_SPECIFIED;
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
		return $this->stateValid === true;
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
			case self::NOT_SPECIFIED:
			default:
				printDebug("Unknown state => default behaviour");
			break;
		}
		printDebug("End of the page.");
	}
}

$process = new Piwik_LogStats;
$process->main();
?>
