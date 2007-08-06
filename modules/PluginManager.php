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

?>
