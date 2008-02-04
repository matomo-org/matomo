<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @package Piwik
 */


require_once "Plugin.php";
require_once "Event/Dispatcher.php";

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
 * @package Piwik
 */
class Piwik_PluginsManager
{
	public $dispatcher;
	protected $pluginsToLoad = array();
	protected $installPlugins = false;
	protected $doLoadPlugins = true;
	protected $languageToLoad = null;
	protected $loadedPlugins = array();
		
	static private $instance = null;
	
	/**
	 * Returns the singleton Piwik_PluginsManager
	 *
	 * @return Piwik_PluginsManager
	 */
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
		$this->dispatcher = Event_Dispatcher::getInstance();
	}
	
	public function isPluginEnabled( $name )
	{
		return in_array( $name, $this->pluginsToLoad);		
	}
	
	/**
	 * Reads the directories inside the plugins/ directory and returns their names in an array
	 *
	 * @return array
	 */
	public function readPluginsDirectory()
	{
		$pluginsName = glob(PIWIK_PLUGINS_PATH . "/*",GLOB_ONLYDIR);
		$pluginsName = array_map('basename', $pluginsName);
		return $pluginsName;
	}

	public function deactivatePlugin($pluginName)
	{
		$plugins = $this->pluginsToLoad;
	
		$key = array_search($pluginName,$plugins);
		if($key !== false)
		{
			unset($plugins[$key]);
			Zend_Registry::get('config')->Plugins = $plugins;
		}
		
		try{
			$pluginsLogStats = Zend_Registry::get('config')->Plugins_LogStats->Plugins_LogStats;
			if(!is_null($pluginsLogStats))
			{
				$pluginsLogStats = $pluginsLogStats->toArray();
				$key = array_search($pluginName,$pluginsLogStats);
				if($key !== false)
				{
					unset($pluginsLogStats[$key]);
					Zend_Registry::get('config')->Plugins_LogStats = $pluginsLogStats;
				}
			}
		} catch(Exception $e) {}
	}
	public function activatePlugin($pluginName)
	{
		$plugins = Zend_Registry::get('config')->Plugins->Plugins->toArray();
		if(in_array($pluginName,$plugins))
		{
			throw new Exception("Plugin '$pluginName' already activated.");
		}
		
		$existingPlugins = $this->readPluginsDirectory();
		if( array_search($pluginName,$existingPlugins) === false)
		{
			throw new Exception("Unable to find the plugin '$pluginName'.");
		}
		
		$plugin = $this->loadPlugin($pluginName);
		
		// is the plugin already installed or is it the first time we activate it?
		$pluginsInstalled = Zend_Registry::get('config')->PluginsInstalled->PluginsInstalled->toArray();
		if(!in_array($pluginName,$pluginsInstalled))
		{
			$this->installPlugin($plugin);
			$pluginsInstalled[] = $pluginName;
			Zend_Registry::get('config')->PluginsInstalled = $pluginsInstalled;	
		}
		
		$information = $plugin->getInformation();
		
		// if the plugin is to be loaded during the statistics logging
		if(isset($information['LogStatsPlugin'])
			&& $information['LogStatsPlugin'] === true)
		{
			$pluginsLogStats = Zend_Registry::get('config')->Plugins_LogStats->Plugins_LogStats;
			if(is_null($pluginsLogStats))
			{
				$pluginsLogStats = array();
			}
			else
			{
				$pluginsLogStats = $pluginsLogStats->toArray();
			}
			$pluginsLogStats[] = $pluginName;

			// the config file will automatically be saved with the new plugin
			Zend_Registry::get('config')->Plugins_LogStats = $pluginsLogStats;
			
		}
		
		// we add the plugin to the list of activated plugins
		$plugins[] = $pluginName;

		// the config file will automatically be saved with the new plugin
		Zend_Registry::get('config')->Plugins = $plugins;
	}
	
	public function setPluginsToLoad( array $pluginsToLoad )
	{
		// case no plugins to load
		if(is_null($pluginsToLoad))
		{
			$pluginsToLoad = array();
		}
		$this->pluginsToLoad = $pluginsToLoad;
		
		$this->loadPlugins();
	}
	
	public function doNotLoadPlugins()
	{
		$this->doLoadPlugins = false;
	}
	
	/**
	 * Add a plugin in the loaded plugins array
	 *
	 * @param Piwik_Plugin $newPlugin
	 * @param string plugin name without prefix (eg. 'UserCountry')
	 */
	protected function addLoadedPlugin( $pluginName, Piwik_Plugin $newPlugin )
	{
		$this->loadedPlugins[$pluginName] = $newPlugin;
	}
	
	/**
	 * Returns an array containing the plugins class names (eg. 'Piwik_UserCountry' and NOT 'UserCountry')
	 *
	 * @return array
	 */
	public function getLoadedPluginsName()
	{
		$oPlugins = $this->getLoadedPlugins();
		$pluginNames = array_map('get_class',$oPlugins);
		return $pluginNames;
	}
	
	/**
	 * Returns an array of key,value with the following format: array(
	 * 		'UserCountry' => Piwik_Plugin $pluginObject,
	 * 		'UserSettings' => Piwik_Plugin $pluginObject,
	 * 	);
	 *
	 * @return array 
	 */
	public function getLoadedPlugins()
	{
		return $this->loadedPlugins;
	}

	/**
	 * Load the plugins classes installed.
	 * Register the observers for every plugin.
	 * 
	 */
	public function loadPlugins()
	{
		$this->pluginsToLoad = array_unique($this->pluginsToLoad);
		foreach($this->pluginsToLoad as $pluginName)
		{
			$newPlugin = $this->loadPlugin($pluginName);
			
			// if we have to load the plugins
			// and if this plugin is activated
			
			if($this->doLoadPlugins
				&& $this->isPluginEnabled($pluginName))
			{
				$newPlugin->registerTranslation( $this->languageToLoad );
				$this->addPluginObservers( $newPlugin );
				$this->addLoadedPlugin( $pluginName, $newPlugin);
			}
		}
	}
	
	/**
	 * Loads the plugin filename and instanciates the plugin with the given name, eg. UserCountry
	 * Do NOT give the class name ie. Piwik_UserCountry, but give the plugin name ie. UserCountry 
	 *
	 * @param Piwik_Plugin $pluginName
	 */
	public function loadPlugin( $pluginName )
	{
		$pluginFileName = $pluginName . '/' . $pluginName . ".php";
		$pluginClassName = "Piwik_".$pluginName;
		
		if( !Piwik_Common::isValidFilename($pluginName))
		{
			throw new Exception("The plugin filename '$pluginFileName' is not valid");
		}
		
		$path = PIWIK_PLUGINS_PATH . '/' . $pluginFileName;

		if(!is_file($path))
		{
			throw new Exception("The plugin file {$path} couldn't be found.");
		}
		
		require_once $path;
		
		if(!class_exists($pluginClassName))
		{
			throw new Exception("The class $pluginClassName couldn't be found in the file '$path'");
		}
		$newPlugin = new $pluginClassName;
		
		if(!($newPlugin instanceof Piwik_Plugin))
		{
			throw new Exception("The plugin $pluginClassName in the file $path must inherit from Piwik_Plugin.");
		}
		return $newPlugin;
	}

	public function installPlugin( Piwik_Plugin $plugin )
	{
		try{
			$plugin->install();
		} catch(Exception $e) {
			throw new Exception("There was a problem installing the plugin ". $plugin->getName() . " = " . $e->getMessage() );
		}	
	}
	
	public function installPlugins()
	{
		foreach($this->getLoadedPlugins() as $plugin)
		{		
//			var_dump($plugin);
			try{
				$plugin->install();
			} catch(Exception $e) {
				throw new Exception("There was a problem installing the plugin ". $plugin->getName() . " = " . $e->getMessage() );
			}
		}
	}
	
	public function setLanguageToLoad( $code )
	{
		$this->languageToLoad = $code;
	}
	
	/**
	 * For the given plugin, add all the observers of this plugin.
	 */
	private function addPluginObservers( Piwik_Plugin $plugin )
	{
		$hooks = $plugin->getListHooksRegistered();
		
		foreach($hooks as $hookName => $methodToCall)
		{
			$this->dispatcher->addObserver( array( $plugin, $methodToCall), $hookName );
		}
	}
		
	public function unloadPlugins()
	{
		$pluginsLoaded = $this->getLoadedPlugins();
		foreach($pluginsLoaded as $plugin)
		{
			$hooks = $plugin->getListHooksRegistered();
			
			foreach($hooks as $hookName => $methodToCall)
			{
				$success = $this->dispatcher->removeObserver( array( $plugin, $methodToCall), $hookName );
				if($success !== true)
				{
					throw new Exception("Error unloading plugin for method = $methodToCall // hook = $hookName ");
				}
			}			
		}
		$this->loadedPlugins = array();
	}
}

/**
 * Post an event to the dispatcher which will notice the observers
 */
function Piwik_PostEvent( $eventName,  &$object = null, $info = array() )
{
	Piwik_PluginsManager::getInstance()->dispatcher->post( $object, $eventName, $info, true, false );
}

/**
 * Register an action to execute for a given event
 */
function Piwik_AddAction( $hookName, $function )
{
	Piwik_PluginsManager::getInstance()->dispatcher->addObserver( $function, $hookName );
}

/**
 * Executes a SQL query on the DB and returns the Zend_Db_Statement object
 * If you want to fetch data from the DB you should use the function Piwik_Fetch()
 * 
 * See also http://framework.zend.com/manual/en/zend.db.statement.html
 *
 * @param string $sqlQuery
 * @param array Parameters to bind in the query, array( param1 => value1, param2 => value2)
 * @return Zend_Db_Statement
 */
function Piwik_Query( $sqlQuery, $parameters = array())
{
	return Zend_Registry::get('db')->query( $sqlQuery, $parameters);
}

/**
 * Executes the SQL Query and fetches all the rows from the database
 *
 * @param string $sqlQuery
 * @param array Parameters to bind in the query, array( param1 => value1, param2 => value2)
 * @return array (one row in the array per row fetched in the DB)
 */
function Piwik_Fetch( $sqlQuery, $parameters = array())
{
	return Zend_Registry::get('db')->fetchAll( $sqlQuery, $parameters );
}
function Piwik_FetchOne( $sqlQuery, $parameters = array())
{
	return Zend_Registry::get('db')->fetchOne( $sqlQuery, $parameters );
}

