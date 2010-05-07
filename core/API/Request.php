<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @category Piwik
 * @package Piwik
 */

/**
 * An API request is the object used to make a call to the API and get the result.
 * The request has the format of a normal GET request, ie. parameter_1=X&parameter_2=Y
 * 
 * You can use this object from anywhere in piwik (inside plugins for example).
 * You can even call it outside of piwik  using the REST API over http
 * or in a php script on the same server as piwik, by including piwik/index.php
 * (see examples in the documentation http://dev.piwik.org/trac/wiki/API)
 * 
 * Example: 
 * $request = new Piwik_API_Request('
 * 				method=UserSettings.getWideScreen
 * 				&idSite=1
 *  			&date=yesterday
 * 				&period=week
 *				&format=xml
 *				&filter_limit=5
 *				&filter_offset=0
 *	');
 *	$result = $request->process();
 *  echo $result;
 * 
 * @see http://dev.piwik.org/trac/wiki/API
 * @package Piwik
 * @subpackage Piwik_API
 */
class Piwik_API_Request
{	
	protected $request = null;
	
	/**
	 * Constructs the request to the API, given the request url
	 * 
	 * @param string GET request that defines the API call (must at least contain a "method" parameter) 
	 *  Example: method=UserSettings.getWideScreen&idSite=1&date=yesterday&period=week&format=xml
	 * 	If a request is not provided, then we use the $_GET and $_POST superglobal and fetch
	 * 	the values directly from the HTTP GET query.
	 */
	function __construct($request = null)
	{
		$defaultRequest = $_GET + $_POST;
		$requestArray = $defaultRequest;
		
		if(!is_null($request))
		{
			$request = trim($request);
			$request = str_replace(array("\n","\t"),'', $request);
			parse_str($request, $requestArray);
		
			$requestArray = $requestArray + $defaultRequest;
		}
		
		foreach($requestArray as &$element)
		{
			if(!is_array($element))
			{
				$element = trim($element);
			}
		}
		
		$this->request = $requestArray;
	}
	
	/**
	 * Handles the request to the API.
	 * It first checks that the method called (parameter 'method') is available in the module (it means that the method exists and is public)
	 * It then reads the parameters from the request string and throws an exception if there are missing parameters.
	 * It then calls the API Proxy which will call the requested method.
	 * 
	 * @return mixed The data resulting from the API call  
	 */
	public function process()
	{
		// read the format requested for the output data
		$outputFormat = strtolower(Piwik_Common::getRequestVar('format', 'xml', 'string', $this->request));
		
		// create the response
		$response = new Piwik_API_ResponseBuilder($outputFormat, $this->request);
		
		try {
			// read parameters
			$moduleMethod = Piwik_Common::getRequestVar('method', null, null, $this->request);
			
			list($module, $method) = $this->extractModuleAndMethod($moduleMethod); 
			
			if(!Piwik_PluginsManager::getInstance()->isPluginActivated($module))
			{
				throw new Piwik_FrontController_PluginDeactivatedException($module);
			}
			$module = "Piwik_" . $module . "_API";

			self::reloadAuthUsingTokenAuth($this->request);
			
			// call the method 
			$returnedValue = Piwik_API_Proxy::getInstance()->call($module, $method, $this->request);
			
			$toReturn = $response->getResponse($returnedValue);
		} catch(Exception $e ) {
			$toReturn = $response->getResponseException( $e );
		}
		return $toReturn;
	}

	/**
	 * If the token_auth is found in the $request parameter, 
	 * the current session will be authenticated using this token_auth.
	 * It will overwrite the previous Auth object.
	 * 
	 * @param $request If null, uses the default request ($_GET)
	 * @return void
	 */
	static public function reloadAuthUsingTokenAuth($request = null)
	{
		// if a token_auth is specified in the API request, we load the right permissions
		$token_auth = Piwik_Common::getRequestVar('token_auth', '', 'string', $request);
		if($token_auth)
		{
			Piwik_PostEvent('API.Request.authenticate', $token_auth);
			Zend_Registry::get('access')->reloadAccess();
		}
	}
	
	/**
	 * Returns array( $class, $method) from the given string $class.$method
	 * 
	 * @return array
	 * @throws exception if the name is invalid
	 */
	private function extractModuleAndMethod($parameter)
	{
		$a = explode('.',$parameter);
		if(count($a) != 2)
		{
			throw new Exception("The method name is invalid. Expected 'module.methodName'");
		}
		return $a;
	}
}
