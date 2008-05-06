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
		$request = new Piwik_API_Request();
		echo $request->process();
	}
	
	protected function init()
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
		echo "<style>body{ font-family:georgia,arial; font-size:0.95em;} 
		#token_auth { 
			background-color:#E8FFE9; 
			border-color:#00CC3A; 
			border-style: solid;
			border-width: 1px;
			margin: 0pt 0pt 16px 8px;
			padding: 12px;			
			line-height:4em;
		</style>";
		echo sprintf(Piwik_Translate('API_QuickDocumentation'),$token_auth);
		echo "<span id='token_auth'>token_auth = <b>$token_auth</b></span>";

		$loaded = $this->init();
		echo "<p><i> ".sprintf(Piwik_Translate('API_LoadedAPIs'),$loaded)."</i></p>\n";
		
		echo Piwik_API_Proxy::getInstance()->getAllInterfaceString();
		echo "<p><a href='?module=Home'>".Piwik_Translate('General_BackToHomepage')."</a></p>";
	}
	
}

