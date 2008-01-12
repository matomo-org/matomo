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

//		http://127.0.0.1/svn-dev/trunk/?module=API&method=UserSettings.getResolution&idSite=1&period=day&date=today&format=xml&token_auth=0b809661490d605bfd77f57ed11f0b14

		$token_auth = Zend_Registry::get('auth')->getTokenAuth();
		echo "<style>body{ font-family:georgia,arial; font-size:0.95em;} </style>";
		echo "<h1>API quick documentation</h1>";
		echo "<p>If you don't have data for today you can first <a href='misc/generateVisits.php' target=_blank>generate some data</a> using the Visits Generator script.</p>";
		echo "<p>You can try the different formats available for every method. It is very easy to extract any data you want from piwik!</p>";
		echo "<p>If you want to <b>request the data without being logged in to Piwik</b> you need to add the parameter <code><u>&token_auth=$token_auth</u></code> to the API calls URLs that require authentication.</p>";
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
		echo "<p><i> Loaded successfully $loaded APIs</i></p>\n";
		echo Piwik_API_Proxy::getInstance()->getAllInterfaceString();
		
		echo "<p>Notice = " . $errors . "</p>\n";
		
		echo "<p><a href='?module=Home&action=index&idSite=1&period=day&date=yesterday'>Back to Piwik homepage</a></p>";
		
	}
	
}

