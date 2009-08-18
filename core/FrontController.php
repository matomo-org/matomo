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

// no direct access
defined('PIWIK_INCLUDE_PATH') or die;

require_once PIWIK_INCLUDE_PATH . '/core/PluginsManager.php';
require_once PIWIK_INCLUDE_PATH . '/core/Translate.php';
require_once PIWIK_INCLUDE_PATH . '/core/Option.php';

/**
 * Front controller.
 * This is the class hit in the first place.
 * It dispatches the request to the right controller.
 * 
 * For a detailed explanation, see the documentation on http://dev.piwik.org/trac/wiki/MainSequenceDiagram
 * 
 * @package Piwik
 */
class Piwik_FrontController
{
	/**
	 * Set to false and the Front Controller will not dispatch the request
	 *
	 * @var bool
	 */
	static public $enableDispatch = true;
	
	static private $instance = null;
	
	/**
	 * returns singleton
	 * 
	 * @return Piwik_FrontController
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
	
	/**
	 * Dispatches the request to the right plugin and executes the requested action on the plugin controller.
	 * 
	 * @throws Exception in case the plugin doesn't exist, the action doesn't exist, there is not enough permission, etc.
	 *
	 * @param string $module
	 * @param string $action
	 * @param array $parameters
	 * @return mixed The returned value of the calls, often nothing as the module print but don't return data
	 * @see fetchDispatch() 
	 */
	function dispatch( $module = null, $action = null, $parameters = null)
	{
		if( self::$enableDispatch === false)
		{
			return;
		}
		
		if(is_null($module))
		{
			$defaultModule = 'CoreHome';
			$module = Piwik_Common::getRequestVar('module', $defaultModule, 'string');
		}
		
		if(is_null($action))
		{
			$action = Piwik_Common::getRequestVar('action', false);
		}
		
		if(is_null($parameters))
		{
			$parameters = array();
		}
		
		if(!ctype_alnum($module))
		{
			throw new Exception("Invalid module name '$module'");
		}
		
		if( ! Piwik_PluginsManager::getInstance()->isPluginActivated( $module )) 
		{
			throw new Piwik_FrontController_PluginDeactivatedException($module);
		}
				
		$controllerClassName = 'Piwik_'.$module.'_Controller';

		// FrontController's autoloader
		if(!class_exists($controllerClassName, false))
		{
			$moduleController = PIWIK_INCLUDE_PATH . '/plugins/' . $module . '/Controller.php';
			if( !Zend_Loader::isReadable($moduleController))
			{
				throw new Exception("Module controller $moduleController not found!");
			}
			require_once $moduleController; // prefixed by PIWIK_INCLUDE_PATH
		}
		
		$controller = new $controllerClassName();
		if($action === false)
		{
			$action = $controller->getDefaultAction();
		}
		
		if( !is_callable(array($controller, $action)))
		{
			throw new Exception("Action $action not found in the controller $controllerClassName.");				
		}
		try {
			return call_user_func_array( array($controller, $action ), $parameters);
		} catch(Piwik_Access_NoAccessException $e) {
			Piwik_PostEvent('FrontController.NoAccessException', $e);					
		}
	}
	
	/**
	 * Often plugins controller display stuff using echo/print.
	 * Using this function instead of dispatch() returns the output string form the actions calls.
	 *
	 * @param string $controllerName
	 * @param string $actionName
	 * @param array $parameters
	 * @return string
	 */
	function fetchDispatch( $controllerName = null, $actionName = null, $parameters = null)
	{
		ob_start();
		$output = $this->dispatch( $controllerName, $actionName, $parameters);
		// if nothing returned we try to load something that was printed on the screen
		if(empty($output))
		{
			$output = ob_get_contents();
		}
		ob_end_clean();
		return $output;
	}
	
	/**
	 * Called at the end of the page generation
	 *
	 */
	function __destruct()
	{
		try {
			Piwik::printSqlProfilingReportZend();
			Piwik::printQueryCount();
		} catch(Exception $e) {}
		
		if(Piwik::getModule() !== 'API')
		{
//			Piwik::printMemoryUsage();
//			Piwik::printTimer();
		}
	}
	
	/**
	 * Must be called before dispatch()
	 * - checks that directories are writable,
	 * - loads the configuration file,
	 * - loads the plugin, 
	 * - inits the DB connection,
	 * - etc.
	 * 
	 * @return void 
	 */
	function init()
	{
		try {
			Zend_Registry::set('timer', new Piwik_Timer);
			
			$directoriesToCheck = array(
					'/tmp', 
					'/tmp/templates_c',
					'/tmp/cache',
			);
			
			Piwik::checkDirectoriesWritableOrDie($directoriesToCheck);
			self::assignCliParametersToRequest();

			Piwik_Translate::getInstance()->loadEnglishTranslation();

			$exceptionToThrow = false;

			try {
				Piwik::createConfigObject();
			} catch(Exception $e) {
				Piwik_PostEvent('FrontController.NoConfigurationFile', $e);
				$exceptionToThrow = $e;
			}

			$pluginsManager = Piwik_PluginsManager::getInstance();
			$pluginsManager->setPluginsToLoad( Zend_Registry::get('config')->Plugins->Plugins->toArray() );

			if($exceptionToThrow)
			{
				throw $exceptionToThrow;
			}

			Piwik_Translate::getInstance()->loadUserTranslation();

			Piwik::createDatabaseObject();
			Piwik::createLogObject();
			
			// creating the access object, so that core/Updates/* can enforce Super User and use some APIs
			Piwik::createAccessObject();
			Piwik_PostEvent('FrontController.DispatchCoreAndPluginUpdatesScreen');

			Piwik_PluginsManager::getInstance()->installLoadedPlugins();
			Piwik::install();

			Piwik_PostEvent('FrontController.initAuthenticationObject');
			try {
				$authAdapter = Zend_Registry::get('auth');
			} catch(Exception $e){
				throw new Exception("Authentication object cannot be found in the Registry. Maybe the Login plugin is not activated?
									<br>You can activate the plugin by adding:<br>
									<code>Plugins[] = Login</code><br>
									under the <code>[Plugins]</code> section in your config/config.inc.php");
			}
			
			Zend_Registry::get('access')->reloadAccess($authAdapter);
			
			Piwik::raiseMemoryLimitIfNecessary();

			$pluginsManager->setLanguageToLoad( Piwik_Translate::getInstance()->getLanguageToLoad() );
			$pluginsManager->postLoadPlugins();
			
			Piwik_PostEvent('FrontController.CheckForUpdates');
		} catch(Exception $e) {
			Piwik_ExitWithMessage($e->getMessage(), $e->getTraceAsString(), true);
		}
	}
	
	/**
	 * Assign CLI parameters as if they were REQUEST or GET parameters.
	 * You can trigger Piwik from the command line by
	 * # /usr/bin/php5 /path/to/piwik/index.php -- "module=API&method=Actions.getActions&idSite=1&period=day&date=previous8&format=php"
	 *
	 * @return void
	 */
	static protected function assignCliParametersToRequest()
	{
		if(isset($_SERVER['argc'])
			&& $_SERVER['argc'] > 0)
		{
			for ($i=1; $i < $_SERVER['argc']; $i++)
			{
				parse_str($_SERVER['argv'][$i],$tmp);
				$_GET = array_merge($_GET, $tmp);
			}
		}				
	}
}

/**
 * Exception thrown when the requested plugin is not activated in the config file
 *
 * @package Piwik
 */
class Piwik_FrontController_PluginDeactivatedException extends Exception
{
	function __construct($module)
	{
		parent::__construct("The plugin '$module' is not activated. You can activate the plugin on the 'Plugins admin' page.");
	}
}


// for more information see http://dev.piwik.org/trac/ticket/374
function destroy(&$var) 
{
	if (is_object($var)) $var->__destruct();
	unset($var);
	$var = null;
}
