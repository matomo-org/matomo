<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: PluginsManager.php 583 2008-07-28 00:37:19Z matt $
 * 
 * @package Piwik
 */

require_once "Plugin.php";
require_once "Event/Dispatcher.php";
require_once "PluginsFunctions/Menu.php";
require_once "PluginsFunctions/AdminMenu.php";
require_once "PluginsFunctions/WidgetsList.php";
require_once "PluginsFunctions/Sql.php";

require_once "Zend/Exception.php";
require_once "Zend/Loader.php"; 
require_once "Auth.php";
require_once "Controller.php";

/**
 * @package Piwik
 */
class Piwik_PluginsManager
{
	/**
	 * @var Event_Dispatcher
	 */
	public $dispatcher;
	
	protected $pluginsToLoad = array();
	protected $languageToLoad = null;

	protected $doLoadPlugins = true;
	protected $loadedPlugins = array();
	
	protected $doLoadAlwaysActivatedPlugins = true;
	protected $pluginToAlwaysActivate = array( 'CoreHome', 'CoreUpdater', 'CoreAdminHome', 'CorePluginsAdmin' );

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
	
	public function isPluginAlwaysActivated( $name )
	{
		return in_array( $name, $this->pluginToAlwaysActivate);
	}
	
	public function isPluginActivated( $name )
	{
		return in_array( $name, $this->pluginsToLoad)
			|| $this->isPluginAlwaysActivated( $name );		
	}
	
	public function isPluginLoaded( $name )
	{
		return isset($this->loadedPlugins[$name]);
	}
	
	/**
	 * Reads the directories inside the plugins/ directory and returns their names in an array
	 *
	 * @return array
	 */
	public function readPluginsDirectory()
	{
		$pluginsName = glob( "plugins/*", GLOB_ONLYDIR);
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
		
		$pluginsTracker = Zend_Registry::get('config')->Plugins_Tracker->Plugins_Tracker;
		if(!is_null($pluginsTracker))
		{
			$pluginsTracker = $pluginsTracker->toArray();
			$key = array_search($pluginName,$pluginsTracker);
			if($key !== false)
			{
				unset($pluginsTracker[$key]);
				Zend_Registry::get('config')->Plugins_Tracker = $pluginsTracker;
			}
		}
	}
	
	public function installLoadedPlugins()
	{
		foreach($this->getLoadedPlugins() as $plugin)
		{
			try {
				$this->installPluginIfNecessary( $plugin );
			}catch(Exception $e){
				echo $e->getMessage();
			}				
		}
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
		
		$this->installPluginIfNecessary($plugin);
		
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

	public function doNotLoadAlwaysActivatedPlugins()
	{
		$this->doLoadAlwaysActivatedPlugins = false;
	}
	
	public function postLoadPlugins()
	{
		$plugins = $this->getLoadedPlugins();
		foreach($plugins as $plugin)
		{
			$this->loadTranslation( $plugin, $this->languageToLoad );
			$plugin->postLoad();
		}
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
	 * Returns the given Piwik_Plugin object 
	 *
	 * @param string $name
	 * @return Piwik_Piwik
	 */
	public function getLoadedPlugin($name)
	{
		if(!isset($this->loadedPlugins[$name]))
		{
			throw new Exception("The plugin '$name' has not been loaded.");
		}
		return $this->loadedPlugins[$name];
	}
	
	/**
	 * Load the plugins classes installed.
	 * Register the observers for every plugin.
	 * 
	 */
	public function loadPlugins()
	{
		$this->pluginsToLoad = array_unique($this->pluginsToLoad);

		if($this->doLoadAlwaysActivatedPlugins)
		{
			$this->pluginsToLoad = array_merge($this->pluginsToLoad, $this->pluginToAlwaysActivate);
		}
		
		foreach($this->pluginsToLoad as $pluginName)
		{
			if(!$this->isPluginLoaded($pluginName))
			{
				$newPlugin = $this->loadPlugin($pluginName);	
				if($this->doLoadPlugins
					&& $this->isPluginActivated($pluginName))
				{
					$this->addPluginObservers( $newPlugin );
					$this->addLoadedPlugin( $pluginName, $newPlugin);
				}
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
		if(isset($this->loadedPlugins[$pluginName]))
		{
			return $this->loadedPlugins[$pluginName];
		}
		$pluginFileName = $pluginName . '/' . $pluginName . ".php";
		$pluginClassName = "Piwik_".$pluginName;
		
		if( !Piwik_Common::isValidFilename($pluginName))
		{
			throw new Exception("The plugin filename '$pluginFileName' is not a valid filename");
		}
		
		$path = PIWIK_INCLUDE_PATH . '/plugins/' . $pluginFileName;

		if(!file_exists($path))
		{
			throw new Exception("The plugin '$pluginName' is enabled, but the file '$path' couldn't be found.
							To continue, please disable the plugin manually by removing the line 
							<pre>Plugins[] = $pluginName</pre>
							in the configuration file <code>config/config.ini.php</code>");
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
	
	public function setLanguageToLoad( $code )
	{
		$this->languageToLoad = $code;
	}

	/**
	 * @param Piwik_Plugin $plugin
	 * @return void
	 */
	public function unloadPlugin( $plugin )
	{
		if(!($plugin instanceof Piwik_Plugin ))
		{
			$plugin = $this->loadPlugin( $plugin );
		}
		$hooks = $plugin->getListHooksRegistered();
			
		foreach($hooks as $hookName => $methodToCall)
		{
			$success = $this->dispatcher->removeObserver( array( $plugin, $methodToCall), $hookName );
			if($success !== true)
			{
				throw new Exception("Error unloading plugin = ".$plugin->getClassName() . ", method = $methodToCall, hook = $hookName ");
			}
		}
		unset($this->loadedPlugins[$plugin->getClassName()]);
	}
	
	public function unloadPlugins()
	{
		$pluginsLoaded = $this->getLoadedPlugins();
		foreach($pluginsLoaded as $plugin)
		{
			$this->unloadPlugin($plugin);
		}
	}

	private function installPlugins()
	{
		foreach($this->getLoadedPlugins() as $plugin)
		{		
			$this->installPlugin($plugin);
		}
	}
	
	private function installPlugin( Piwik_Plugin $plugin )
	{
		try{
			$plugin->install();
		} catch(Exception $e) {
			throw new Piwik_Plugin_Exception($plugin->getName(), $plugin->getClassName(), $e->getMessage());		}	
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
	
	/**
	 * Add a plugin in the loaded plugins array
	 *
	 * @param string plugin name without prefix (eg. 'UserCountry')
	 * @param Piwik_Plugin $newPlugin
	 */
	private function addLoadedPlugin( $pluginName, Piwik_Plugin $newPlugin )
	{
		$this->loadedPlugins[$pluginName] = $newPlugin;
	}
	
	/**
	 * @param Piwik_Plugin $plugin
	 * @param string $langCode
	 */
	private function loadTranslation( $plugin, $langCode )
	{
		// we are certainly in Tracker mode, Zend is not loaded
		if(!class_exists('Zend_Loader'))
		{
			return ;
		}
		
		$infos = $plugin->getInformation();		
		if(!isset($infos['translationAvailable']))
		{
			$infos['translationAvailable'] = false;
		}
		$translationAvailable = $infos['translationAvailable'];
		
		if(!$translationAvailable)
		{
			return;
		}
		
		$pluginName = $plugin->getClassName();
		
		$path = PIWIK_INCLUDE_PATH . "/plugins/" . $pluginName ."/lang/%s.php";
		
		$defaultLangPath = sprintf($path, $langCode);
		$defaultEnglishLangPath = sprintf($path, 'en');
		
		$translations = array();
				
		if(file_exists($defaultLangPath))
		{
			require $defaultLangPath;
		}
		elseif(file_exists($defaultEnglishLangPath))
		{
			require $defaultEnglishLangPath;
		}
		else
		{
			throw new Exception("Language file not found for the plugin '$pluginName'.");
		}
		Piwik_Translate::getInstance()->mergeTranslationArray($translations);
	}
	
	/**
	 * @return array
	 */
	public function getInstalledPluginsName()
	{
		if(!class_exists('Zend_Registry'))
		{
			throw new Exception("Not possible to list installed plugins (case Tracker module)");
		}
		$pluginNames = Zend_Registry::get('config')->PluginsInstalled->PluginsInstalled->toArray();
		return $pluginNames;
	}
	
	public function getInstalledPlugins()
	{
		$plugins = $this->getLoadedPlugins();
		$installed = $this->getInstalledPluginsName();
		return array_intersect_key($plugins, array_combine($installed, array_fill(0, count($installed), 1)));
	}

	private function installPluginIfNecessary( Piwik_Plugin $plugin )
	{
		$pluginName = $plugin->getClassName();
		
		// is the plugin already installed or is it the first time we activate it?
		$pluginsInstalled = $this->getInstalledPluginsName();
		if(!in_array($pluginName,$pluginsInstalled))
		{
			$this->installPlugin($plugin);
			$pluginsInstalled[] = $pluginName;
			Zend_Registry::get('config')->PluginsInstalled = array('PluginsInstalled' => $pluginsInstalled);	
		}
		
		$information = $plugin->getInformation();
		
		// if the plugin is to be loaded during the statistics logging
		if(isset($information['TrackerPlugin'])
			&& $information['TrackerPlugin'] === true)
		{
			$pluginsTracker = Zend_Registry::get('config')->Plugins_Tracker->Plugins_Tracker;
			if(is_null($pluginsTracker))
			{
				$pluginsTracker = array();
			}
			else
			{
				$pluginsTracker = $pluginsTracker->toArray();
			}
			if(!in_array($pluginName, $pluginsTracker))
			{
				$pluginsTracker[] = $pluginName;
				Zend_Registry::get('config')->Plugins_Tracker = array('Plugins_Tracker' => $pluginsTracker);
			}
		}
	}
}

class Piwik_Plugin_Exception extends Exception 
{
	function __construct($pluginName, $className, $message)
	{
		parent::__construct("There was a problem installing the plugin ". $pluginName . ": " . $message. "
				If this plugin has already been installed, and if you want to hide this message</b>, you must add the following line under the 
				[PluginsInstalled] 
				entry in your config/config.ini.php file:
				PluginsInstalled[] = $className" );
	}
}

/**
 * Post an event to the dispatcher which will notice the observers
 */
function Piwik_PostEvent( $eventName,  &$object = null, $info = array() )
{
	$notification = new Piwik_Event_Notification($object, $eventName, $info);
	Piwik_PluginsManager::getInstance()->dispatcher->postNotification( $notification, true, false );
}

/**
 * Register an action to execute for a given event
 */
function Piwik_AddAction( $hookName, $function )
{
	Piwik_PluginsManager::getInstance()->dispatcher->addObserver( $function, $hookName );
}

class Piwik_Event_Notification extends Event_Notification
{
	static $showProfiler = false;
	function increaseNotificationCount($className, $method) {
		parent::increaseNotificationCount();
		if(self::$showProfiler)
		{
			echo "after $className -> $method <br>";
			echo "-"; Piwik::printTimer();
			echo "<br>";
			echo "-"; Piwik::printMemoryLeak();
			echo "<br>";
		}
	}
}

