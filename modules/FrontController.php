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


/**
 * Zend classes
 */
include "Zend/Exception.php";
include "Zend/Loader.php";
require_once "Zend/Debug.php";
require_once "Zend/Auth.php";
require_once "Zend/Auth/Adapter/DbTable.php";

/**
 * Piwik classes
 */
require_once "Timer.php";
require_once "modules/Piwik.php";
require_once "API/APIable.php";
require_once "Access.php";
require_once "Auth.php";
require_once "API/Proxy.php";
require_once "Site.php";
require_once "Translate.php";
require_once "Url.php";
require_once "Controller.php";

require_once "Menu.php";
require_once "Widget.php";

/**
 * 
 * 
 * @package Piwik
 */
class Piwik_FrontController
{
	static public $enableDispatch = true;
	
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
	
	function dispatch( $module = null, $action = null, $parameters = null)
	{
		if( self::$enableDispatch === false)
		{
			return;
		}
		
		if(is_null($module))
		{
			$defaultModule = 'Home';
			// load the module requested
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
		
		$controllerClassName = "Piwik_".$module."_Controller";
		
		if(!class_exists($controllerClassName))
		{
			$moduleController = PIWIK_PLUGINS_PATH . "/" . $module . "/Controller.php";
			
			if( !is_readable($moduleController))
			{
				throw new Exception("Module controller $moduleController not found!");
			}
			require_once $moduleController;
		}
		
		// check that the plugin is enabled
		if( ! Piwik_PluginsManager::getInstance()->isPluginEnabled( $module )) 
		{
			throw new Exception("The plugin '$module' is not enabled. You can activate the plugin on the <a href='?module=PluginsAdmin'>Plugins admin page</a>.");
		}
				
		$controller = new $controllerClassName;
		
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
	
	function end()
	{
		try {
			Piwik::printZendProfiler();
			Piwik::printQueryCount();
		} catch(Exception $e) {}
		
//		Piwik::printMemoryUsage();
//		Piwik::printTimer();
//		Piwik::uninstall();

	}
	
	protected function checkDirectoriesWritableOrDie()
	{
		$resultCheck = Piwik::checkDirectoriesWritable( );
		if( array_search(false, $resultCheck) !== false )
		{ 
			$directoryList = '';
			foreach($resultCheck as $dir => $bool)
			{
				$dir = realpath($dir);
				if(!empty($dir) && $bool === false)
				{
					$directoryList .= "<code>chmod 777 $dir</code><br>";
				}
			}
			$directoryList .= '';
			
			$directoryMessage = "<p><b>Piwik couldn't write to some directories</b>.</p> <p>Try to Execute the following commands on your Linux server:</P>";
			$directoryMessage .= $directoryList;
			$directoryMessage .= "<p>If this doesn't work, you can try to create the directories with your FTP software, and set the CHMOD to 777 (with your FTP software, right click on the directories, permissions).";
			$directoryMessage .= "<p>After applying the modifications, you can <a href='index.php'>refresh the page</a>.";
			$directoryMessage .= "<p>If you need more help, try <a href='http://piwik.org'>Piwik.org</a>.";
			
			
			$html = '
				<html>
				<head>
					<title>Piwik &rsaquo; Error</title>
					<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
				<style>
				
				html { background: #eee; }
				
				body {
					background: #fff;
					color: #000;
					font-family: Georgia, "Times New Roman", Times, serif;
					margin-left: 20%;
					margin-top: 25px;
					margin-right: 20%;
					padding: .2em 2em;
				}
				
				#h1 {
					color: #006;
					font-size: 45px;
					font-weight: lighter;
				}
				
				#subh1 {
					color: #879DBD;
					font-size: 25px;
					font-weight: lighter;
				}
				
				
				p, li, dt {
					line-height: 140%;
					padding-bottom: 2px;
				}
				
				ul, ol { padding: 5px 5px 5px 20px; }
				
				#logo { margin-bottom: 2em; }
				
				code { margin-left: 40px; }
				</style>
				</head>
				<body>
					<span id="h1">Piwik </span><span id="subh1"> # open source web analytics</span>
					<p>'.$directoryMessage.'</p>
				
				</body>
				</html>
				';
		
			print($html);
			exit;	
		}
	}
	
	function init()
	{
		Zend_Registry::set('timer', new Piwik_Timer);
		
		$this->checkDirectoriesWritableOrDie();
		
		$exceptionToThrow = false;
		
		//move into a init() method
		try {
			Piwik::createConfigObject();
		} catch(Exception $e) {
			Piwik_PostEvent('FrontController.NoConfigurationFile', $e);
			$exceptionToThrow = $e;
		}
		
		Piwik::loadPlugins();
		
		if($exceptionToThrow)
		{
			throw $exceptionToThrow;
		}
		// database object
		Piwik::createDatabaseObject();
		
		// Create the log objects
		Piwik::createLogObject();
		
		Piwik::install();
		
//		Piwik::printMemoryUsage('Start program');

		// can be used for debug purpose
		$doNotDrop = array(
				Piwik::prefixTable('access'),
				Piwik::prefixTable('user'),
				Piwik::prefixTable('site'),
				Piwik::prefixTable('archive'),
				
				Piwik::prefixTable('logger_api_call'),
				Piwik::prefixTable('logger_error'),
				Piwik::prefixTable('logger_exception'),
				Piwik::prefixTable('logger_message'),
				
				Piwik::prefixTable('log_visit'),
				Piwik::prefixTable('log_link_visit_action'),
				Piwik::prefixTable('log_action'),
				Piwik::prefixTable('log_profiling'),
		);
		
		//Piwik::dropTables($doNotDrop);
		//Piwik::createTables();
		//Piwik_PluginsManager::getInstance()->installPlugins();
		
		// Setup the auth object
		Piwik_PostEvent('FrontController.authSetCredentials');

		try {
			$authAdapter = Zend_Registry::get('auth');
		}
		catch(Exception $e){
			throw new Exception("Object 'auth' cannot be found in the Registry. Maybe the Login plugin is not enabled?");
		}
		
		// Perform the authentication query, saving the result
		$access = new Piwik_Access($authAdapter);
		Zend_Registry::set('access', $access);		
		Zend_Registry::get('access')->loadAccess();					
	}
}

