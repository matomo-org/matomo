<?php

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

class Piwik_FrontController
{
	function dispatch()
	{
		$defaultModule = 'Home';
		
		// load the module requested
		$module = Piwik_Common::getRequestVar('module', $defaultModule, 'string');
		
		if(ctype_alnum($module))
		{
			$moduleController = PIWIK_PLUGINS_PATH . "/" . $module . "/Controller.php";
			if(is_readable($moduleController))
			{
				require_once $moduleController;
				
				$controllerClassName = "Piwik_".$module."_Controller";
				
				$controller = new $controllerClassName;
				
				$defaultAction = $controller->getDefaultAction();
				$action = Piwik_Common::getRequestVar('action', $defaultAction, 'string');
				
				try{
					$controller->$action();
				} catch(Piwik_Access_NoAccessException $e) {
//					Piwik::log("NO ACCESS EXCEPTION =>");
					
					Piwik_PostEvent('FrontController.NoAccessException', $e);					
				}
			}
			else
			{
				throw new Exception("Module controller $moduleController not found!");
			}			
		}
		else
		{
			throw new Exception("Invalid module name '$module'");
		}
	}
	
	function end()
	{
//		Piwik::printZendProfiler();
//		Piwik::printMemoryUsage();
//		Piwik::printQueryCount();
//		Piwik::uninstall();
//
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

		$doNotDrop = array(
				Piwik::prefixTable('log_visit'),
				Piwik::prefixTable('access'),
				Piwik::prefixTable('user'),
				Piwik::prefixTable('site'),
				Piwik::prefixTable('log_link_visit_action'),
				Piwik::prefixTable('log_action'),
				Piwik::prefixTable('log_profiling'),
				Piwik::prefixTable('archive'),
				Piwik::prefixTable('logger_api_call'),
				Piwik::prefixTable('logger_error'),
				Piwik::prefixTable('logger_exception'),
				Piwik::prefixTable('logger_message'),
		);
		
		Piwik::dropTables($doNotDrop);
		Piwik::createTables();
		
		Piwik_PluginsManager::getInstance()->installPlugins();
		
		// Setup the auth object
		Piwik_PostEvent('FrontController.authSetCredentials');

		$authAdapter = Zend_Registry::get('auth');
		
		// Perform the authentication query, saving the result
		$access = new Piwik_Access($authAdapter);
		Zend_Registry::set('access', $access);		
		Zend_Registry::get('access')->loadAccess();					
	}
}

