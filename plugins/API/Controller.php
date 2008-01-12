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
//?module=API&method=Referers.getKeywords&idSite=1&period=month&date=today&format=xml
//
//or yesterday visits information in JSON
//?module=API&method=VisitsSummary.get&idSite=1&period=month&date=yesterday&format=json
		echo "<style>body{ font-family:georgia,arial; font-size:0.95em;} </style>";
		echo "<h1>API quick documentation</h1>";
		echo "<p>If you don't have data for today you can first <a href='misc/generateVisits.php' target=_blank>generate some data</a> using the Visits Generator script.</p>";
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

