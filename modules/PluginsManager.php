<?php

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
require_once "Plugin.php";
require_once "Event/Dispatcher.php";

class Piwik_PluginsManager
{
	public $dispatcher;
	private $pluginsPath;
	protected $pluginsToLoad = array();
	protected $installPlugins = false;
	protected $doLoadPlugins = true;
	
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
//		$this->pluginsPath = '/plugins/';
//		$this->pluginsCategory = null;
		
		$this->dispatcher = Event_Dispatcher::getInstance();
	}
	
	public function setPluginsToLoad( $array )
	{
		$this->pluginsToLoad = $array;
		
		$this->loadPlugins();
	}
	
	public function setInstallPlugins()
	{
		$this->installPlugins = true;
	}
	
	public function doNotLoadPlugins()
	{
		$this->doLoadPlugins = false;
	}
	public function doInstallPlugins()
	{
		return $this->installPlugins;
	}
	/**
	 * Load the plugins classes installed.
	 * Register the observers for every plugin.
	 * 
	 */
	public function loadPlugins()
	{
//		$defaultPlugins = array(
//			array( 'fileName' => 'Provider', 'className' => 'Piwik_Plugin_LogStats_Provider' ),
//		//	'Piwik_Plugin_LogStats_UserSettings',
//		);
		
		foreach($this->pluginsToLoad as $pluginName)
		{
			$pluginFileName = $pluginName . ".php";
			$pluginClassName = "Piwik_".$pluginName;
			
			// TODO make sure the plugin name is secure
			// make sure thepluigin is a child of Piwik_Plugin
			$path = 
//					PIWIK_INCLUDE_PATH 
//					. $this->pluginsPath 
//					. $this->pluginsCategory
					$pluginFileName;
			
			if(is_file($path))
			{
				throw new Exception("The plugin file $path couldn't be found.");
			}
			
			require_once $path;
			
			if($pluginClassName instanceof Piwik_Plugin)
			{
				throw new Exception("The plugin $pluginClassName in the file $path must inherit from Piwik_Plugin.");
			}
			$newPlugin = new $pluginClassName;
			
			
			if($this->doInstallPlugins())
			{
				try{
					$newPlugin->install();
				} catch(Exception $e) {
					//TODO Better plugin management....
				}
			}
			
			if($this->doLoadPlugins)
			{
				$this->addPluginObservers( $newPlugin );
			}
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
			$this->dispatcher->addObserver( array( $plugin, $methodToCall), $hookName );
		}
	}
	
}

/**
 * Post an event to the dispatcher which will notice the observers
 */
function Piwik_PostEvent( $eventName, $object = null, $info = array() )
{
	Piwik_PluginsManager::getInstance()->dispatcher->post( $object, $eventName, $info, false, false );
}


?>
