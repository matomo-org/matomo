<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id: Request.php 506 2008-06-06 01:18:47Z matt $
 * 
 * 
 * @package Piwik_API
 */

require_once "API/ResponseBuilder.php";

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
 * @package Piwik_API
 */
class Piwik_API_Request
{	
	protected $request = null;
	
	/**
	 * Constructs the request to the API, given the request url
	 * 
	 * @param string GET request that defines the API call (must at least contain a "method" parameter) 
	 *  Example: method=UserSettings.getWideScreen&idSite=1&date=yesterday&period=week&format=xml
	 * 	If a request is not provided, then we use the $_REQUEST superglobal and fetch
	 * 	the values directly from the HTTP GET query.
	 */
	function __construct($request = null)
	{
		$requestArray = $_REQUEST;
		
		// If an array is specified we use it
		if(!is_null($request))
		{
			$request = trim($request);
			$request = str_replace(array("\n","\t"),'', $request);
			parse_str($request, $requestArray);
				
			// but we handle the case when an array is specified but we also want
			// to look for the value in the _REQUEST
			$requestArray = array_merge( $_REQUEST, $requestArray);
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
	 * @see the method handleReturnedValue() for the data post process logic 
	 * 
	 * @return mixed The data resulting from the API call  
	 */
	public function process()
	{
		// read the format requested for the output data
		$outputFormat = strtolower(Piwik_Common::getRequestVar('format', 'xml', 'string', $this->request));
		
		// create the response
		$response = new Piwik_API_ResponseBuilder($this->request, $outputFormat);
		
		try {
		
			// read parameters
			$moduleMethod = Piwik_Common::getRequestVar('method', null, null, $this->request);
			
			list($module, $method) = $this->extractModuleAndMethod($moduleMethod); 
			
			if(!Piwik_PluginsManager::getInstance()->isPluginActivated($module))
			{
				throw new Exception_PluginDeactivated($module);
			}
			// call the method via the API_Proxy class
			$api = Piwik_Api_Proxy::getInstance();
			$api->registerClass($module);
			
			// read method to call meta information
			$className = "Piwik_" . $module . "_API";
			
			// check method exists
			$api->checkMethodExists($className, $method);
			
			// get the list of parameters required by the method
			$parameters = $api->getParametersList($className, $method);

			// load the parameters from the request URL
			$finalParameters = $this->getRequestParametersArray( $parameters );
			
			// call the method 
			$returnedValue = call_user_func_array( array( $api->$module, $method), $finalParameters );
			
			$toReturn = $response->getResponse($returnedValue);
			
		} catch(Exception $e ) {
			return $response->getResponseException( $e );
		}
		return $toReturn;
	}
	
	/**
	 * Returns an array containing the information of the generic Piwik_DataTable_Filter 
	 * to be applied automatically to the data resulting from the API calls.
	 *
	 * @return array See the code for spec
	 */
	public static function getGenericFiltersInformation()
	{
		$genericFilters = array(
			
			'Pattern' => array(
								'filter_column' 			=> array('string'), 
								'filter_pattern' 			=> array('string'),
						),
			'PatternRecursive' => array(
								'filter_column_recursive' 	=> array('string'), 
								'filter_pattern_recursive' 	=> array('string'),
						),
			'ExcludeLowPopulation'	=> array(
								'filter_excludelowpop' 		=> array('string'), 
								'filter_excludelowpop_value'=> array('float'),
						),
			'Sort' => array(
								'filter_sort_column' 		=> array('string', Piwik_Archive::INDEX_NB_VISITS),
								'filter_sort_order' 		=> array('string', Zend_Registry::get('config')->General->dataTable_default_sort_order),
						),
			'Limit' => array(
								'filter_offset' 			=> array('integer', '0'),
								'filter_limit' 				=> array('integer', Zend_Registry::get('config')->General->dataTable_default_limit),
						),
			'ExactMatch' => array(
								'filter_exact_column'		=> array('string'),
								'filter_exact_pattern'		=> array('array'),
						),
		);
		
		return $genericFilters;
	}
	
	/**
	 * Returns an array containing the values of the parameters to pass to the method to call
	 *
	 * @param array array of (parameter name, default value)
	 * @return array values to pass to the function call
	 * @throws exception If there is a parameter missing from the required function parameters
	 */
	protected function getRequestParametersArray( $parameters )
	{
		$finalParameters = array();
		foreach($parameters as $name => $defaultValue)
		{
			try{
				if($defaultValue === Piwik_API_Proxy::NO_DEFAULT_VALUE)
				{
					try {
						$requestValue = Piwik_Common::getRequestVar($name, null, null, $this->request);
					} catch(Exception $e) {
						$requestValue = null;
					}
				}
				else
				{
					$requestValue = Piwik_Common::getRequestVar($name, $defaultValue, null, $this->request);
				}
			} catch(Exception $e) {
				throw new Exception("The required variable '$name' is not correct or has not been found in the API Request. Add the parameter '&$name=' (with a value) in the URL.");
			}			
			$finalParameters[] = $requestValue;
		}
		return $finalParameters;
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
			throw new Exception("The method name is invalid. Must be on the form 'module.methodName'");
		}
		return $a;
	}

}