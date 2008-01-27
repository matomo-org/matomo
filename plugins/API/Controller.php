<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @package Piwik_API
 */

require_once "API/Request.php";


/**
 * 
 * @package Piwik_API
 */
class Piwik_API_Controller extends Piwik_Controller
{
	function index()
	{
//		sleep(1);
		$request = new Piwik_API_Request();
		echo $request->process();
	}
	
	function init()
	{
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
//				$errors .= "<br>\n" . $e->getMessage();
			}
		}
		return $loaded;
	}
	
	function listAllMethods()
	{
		$this->init();
		echo Piwik_API_Proxy::getInstance()->getAllInterfaceString( $outputExampleUrls = true, $prefixUrls = Piwik_Common::getRequestVar('prefixUrl', '') );
	}
	
	
	function listAllAPI()
	{
		$token_auth = Zend_Registry::get('auth')->getTokenAuth();
		echo "<style>body{ font-family:georgia,arial; font-size:0.95em;} </style>";
		echo "<h1>API quick documentation</h1>";
		echo "<p>If you don't have data for today you can first <a href='misc/generateVisits.php' target=_blank>generate some data</a> using the Visits Generator script.</p>";
		echo "<p>You can try the different formats available for every method. It is very easy to extract any data you want from piwik!</p>";
		echo "<p>If you want to <b>request the data without being logged in to Piwik</b> you need to add the parameter <code><u>&token_auth=$token_auth</u></code> to the API calls URLs that require authentication.</p>";
		echo "<p><b>For more information have a look at the <a href='http://dev.piwik.org/trac/wiki/API'>official API Documentation</a>.</b></P>";

		$loaded = $this->init();
		echo "<p><i> Loaded successfully $loaded APIs</i></p>\n";
		
		echo Piwik_API_Proxy::getInstance()->getAllInterfaceString();
		echo "<p><a href='?module=Home&action=index&idSite=1&period=day&date=yesterday'>Back to Piwik homepage</a></p>";
	}
	
}

