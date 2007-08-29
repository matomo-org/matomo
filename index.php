<?php
/**
 * PHP Configuration init
 */
error_reporting(E_ALL|E_NOTICE);
date_default_timezone_set('Europe/London');
define('PIWIK_INCLUDE_PATH', '.');
define('PIWIK_PLUGINS_PATH', PIWIK_INCLUDE_PATH . '/plugins');
define('PIWIK_DATAFILES_INCLUDE_PATH', PIWIK_INCLUDE_PATH . "/modules/DataFiles");

set_include_path(PIWIK_INCLUDE_PATH 
					. PATH_SEPARATOR . PIWIK_INCLUDE_PATH . '/libs/'
					. PATH_SEPARATOR . PIWIK_INCLUDE_PATH . '/plugins/'
					. PATH_SEPARATOR . PIWIK_INCLUDE_PATH . '/modules/'
					. PATH_SEPARATOR . get_include_path());

assert_options(ASSERT_ACTIVE, 	1);
assert_options(ASSERT_WARNING, 	1);
assert_options(ASSERT_BAIL, 	1);

/**
 * Error / exception handling functions
 */
require_once PIWIK_INCLUDE_PATH . "/modules/ErrorHandler.php";
require_once PIWIK_INCLUDE_PATH . "/modules/ExceptionHandler.php";
set_error_handler('Piwik_ErrorHandler');
set_exception_handler('Piwik_ExceptionHandler');

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

require_once "Piwik.php";

require_once "Access.php";
require_once "Auth.php";
require_once "API/Proxy.php";
require_once "Site.php";
require_once "Translate.php";
require_once "Url.php";
require_once "Controller.php";

$controller = new Piwik_FrontController;
$controller->init();
$controller->dispatch();
$controller->end();
exit;

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
		
		Piwik::displayZendProfiler();
		Piwik::printMemoryUsage();
		Piwik::printQueryCount();		
//		Piwik::uninstall();

		echo $this->timer;
		
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

//
//main();

function dump($var)
{
	print("<pre>");
	var_export($var);
	print("</pre>");
}

?>

<br>
<br>
<a href="piwik.php?idsite=1&download=http://php.net/get&name=test download/ the file">test download </a>
<br>
<a href="piwik.php?idsite=1&download=http://php.net/get">test download - without name var</a>
<br>
<a href="piwik.php?idsite=1&link=http://php.net/&name=php.net website">test link php</a>
<br>
<a href="piwik.php?idsite=1&link=http://php.net/">test link php - without name var</a>
<br>
<!-- Piwik -->
<a href="http://piwik.org" title="Web analytics" onclick="window.open(this.href);return(false);">
<script language="javascript" src="piwik.js" type="text/javascript"></script>
<script type="text/javascript">
<!--
	piwik_action_name = '';
	piwik_idsite = 1;
	piwik_url = "http://localhost/dev/piwiktrunk/piwik.php";
	piwik_log(piwik_action_name, piwik_idsite, piwik_url);
//-->
</script><object>
<noscript><p>Web analytics<img src="http://localhost/dev/piwiktrunk/piwik.php" style="border:0" /></p>
</noscript></object></a>
<!-- /Piwik --> 