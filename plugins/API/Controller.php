<?php
require_once "API/Request.php";

class Piwik_API_Controller extends Piwik_Controller
{
	function index()
	{
//		sleep(1);
		$request = new Piwik_API_Request();
		echo $request->process();
	}
	
	function listAllAPI()
	{
		echo "<h1>List of all modules API</h1>";
		$errors = '';
		$plugins = Piwik_PluginsManager::getInstance()->getLoadedPluginsName();
		
		$loaded = 0;
		foreach( $plugins as $plugin )
		{		
			$plugin = Piwik::unprefixClass($plugin);
				
			try {
				Piwik_API_Proxy::getInstance()->registerClass($plugin);
				$loaded++;
			}
			catch(Exception $e){
				$errors .= "<br>\n" . $e->getMessage();
			}
		}
		echo "<p> Loaded successfully $loaded APIs</p>\n";
		echo Piwik_API_Proxy::getInstance()->getAllInterfaceString();
		
		echo "<p>Errors = " . $errors . "</p>\n";
	}
	
}

