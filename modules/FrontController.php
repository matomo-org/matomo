<?php

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
					Piwik::log("NO ACCESS EXCEPTION =>");
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
			throw new Exception("Invalid module name");
		}
		
	}
	
	function end()
	{
		
		Piwik::printZendProfiler();
		Piwik::printMemoryUsage();
		Piwik::printQueryCount();		
//		Piwik::uninstall();

		Piwik::log($this->timer);
		
	}
	
	function init()
	{
		$this->timer = new Piwik_Timer;
		
		//move into a init() method
		Piwik::createConfigObject();
		
		// database object
		Piwik::createDatabaseObject();
		
		// Create the log objects
		Piwik::createLogObject();
		
		Piwik::printMemoryUsage('Start program');
		//TODO move all DB related methods in a DB static class
		
		//Piwik::createDatabase();
		//Piwik::createDatabaseObject();
		
		$doNotDrop = array(
				Piwik::prefixTable('log_visit'),
				Piwik::prefixTable('access'),
				Piwik::prefixTable('user'),
				Piwik::prefixTable('site'),
				Piwik::prefixTable('log_link_visit_action'),
				Piwik::prefixTable('log_action'),
				Piwik::prefixTable('log_profiling'),
				Piwik::prefixTable('archive'),
		);
		
		Piwik::dropTables($doNotDrop);
		Piwik::createTables();
		
		// load plugins
		Piwik_PluginsManager::getInstance()->setInstallPlugins(); 
		//TODO plugins install to handle in a better way
		Piwik::loadPlugins();
		
		// Create auth object
		Zend_Registry::set('auth', $authAdapter = new Piwik_Auth());
		
		// Setup the auth object
		Piwik_PostEvent('FrontController.authSetCredentials');

		// Perform the authentication query, saving the result
		$access = new Piwik_Access($authAdapter);
		Zend_Registry::set('access', $access);		
		Zend_Registry::get('access')->loadAccess();					
	}
}

