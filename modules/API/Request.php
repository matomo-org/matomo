<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * 
 * @package Piwik_API
 */


/**
 * An API request is the object used to make a call to the API and get the result.
 * The request has the format of a normal GET request, ie. parameter_1=X&parameter_2=Y
 * 
 * You can use this object from anywhere in piwik (inside plugins for example).
 * You can even call it outside of piwik  using the REST API over http
 * or in a php script on the same server as piwik, by including piwik/index.php
 * (see the documentation http://dev.piwik.org/trac/wiki/API)
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
		
		// remove all spaces from parameters values (when calling internally the API for example)
		$requestArray = array_map('trim',$requestArray);
		
		$this->requestToUse = $requestArray;
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
	
	protected $outputFormatRequested;
	
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
		try {
			
			// read the format requested for the output data
			$this->outputFormatRequested = Piwik_Common::getRequestVar('format', 'xml', 'string', $this->requestToUse);
			$this->outputFormatRequested = strtolower($this->outputFormatRequested);
		
			// read parameters
			$moduleMethod = Piwik_Common::getRequestVar('method', null, null, $this->requestToUse);
			
			list($module, $method) = $this->extractModuleAndMethod($moduleMethod); 
			
			if(!Piwik_PluginsManager::getInstance()->isPluginEnabled($module))
			{
				throw new Exception("The plugin '$module' is not enabled.");
			}
			// call the method via the PublicAPI class
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
			
			// post process the data
			$toReturn = $this->handleReturnedValue( $returnedValue );
			
			
		} catch(Exception $e ) {
			
			// if it is not a direct API call, we are requesting the original data structure
			// and we actually are handling this exception at the top level in the FrontController
			if($this->outputFormatRequested == 'original')
			{
				throw $e;
			}
			$message = $e->getMessage();
			
			// it seems that JSON doesn't like line breaks
			$message = nl2br($message);
			
			$toReturn =  $this->getExceptionOutput( $message, $this->outputFormatRequested);
			
		}
		
		return $toReturn;
	}
	
	/**
	 * Returns the values of the current request
	 *
	 * @param array Parameters array of the method called. Contains name and default values of the required parameters
	 * @return array Values of the given parameters
	 * @throws exception If there is a missing parameter
	 */
	protected function getRequestParametersArray( $parameters )
	{
		$finalParameters = array();
		foreach($parameters as $name => $defaultValue)
		{
			try{
				// there is a default value specified
				if($defaultValue !== Piwik_API_Proxy::NO_DEFAULT_VALUE)
				{
					$requestValue = Piwik_Common::getRequestVar($name, $defaultValue, null, $this->requestToUse);
				}
				else
				{
					$requestValue = Piwik_Common::getRequestVar($name, null, null, $this->requestToUse);				
				}
			} catch(Exception $e) {
				throw new Exception("The required variable '$name' is not correct or has not been found in the API Request.");
			}			
			$finalParameters[] = $requestValue;
		}
		return $finalParameters;
	}
	
	/**
	 * This method post processes the data resulting from the API call.
	 * 
	 *
	 * - If the data resulted from the API call is a Piwik_DataTable then 
	 * 		- we apply the standard filters if the parameters have been found
	 * 		  in the URL. For example to offset,limit the Table you can add the following parameters to any API
	 *  	  call that returns a DataTable: filter_limit=10&filter_offset=20
	 * 		- we apply the filters that have been previously queued on the DataTable
	 * 		- we apply the renderer that generate the DataTable in a given format (XML, PHP, HTML, JSON, etc.) 
	 * 		  the format can be changed using the 'format' parameter in the request.
	 *        Example: format=xml
	 * 
	 * - If there is nothing returned (void) we display a standard success message
	 * 
	 * - If there is a PHP array returned, we try to convert it to a dataTable 
	 *   It is then possible to convert this datatable to any requested format (xml/etc)
	 * 
	 * - If a bool is returned we convert to a string (true is displayed as 'true' false as 'false')
	 * 
	 * - If an integer / float is returned, we simply return it
	 * 
	 * @throws Exception If an object/resource is returned, if any of conversion fails, etc. 
	 * 
	 * @param mixed The initial returned value, before post process
	 * @return mixed Usually a string, but can still be a PHP data structure if the format requested is 'original'
	 */
	protected function handleReturnedValue( $returnedValue ) 
	{
		$toReturn = $returnedValue;
		
		// If the returned value is an object DataTable we
		// apply the set of generic filters if asked in the URL
		// and we render the DataTable according to the format specified in the URL
		if($returnedValue instanceof Piwik_DataTable)
		{			
			$this->applyDataTableGenericFilters($returnedValue);
			
			$returnedValue->applyQueuedFilters();
			
			$toReturn = $this->getRenderedDataTable($returnedValue);
			
			
		}
		
		// Case nothing returned (really nothing was 'return'ed), 
		// => the operation was successful
		elseif(!isset($toReturn))
		{
			$toReturn = $this->getStandardSuccessOutput($this->outputFormatRequested);
		}
		
		// Case an array is returned from the API call, we convert it to the requested format
		// - if calling from inside the application (format = original)
		//    => the data stays unchanged (ie. a standard php array or whatever data structure)
		// - if any other format is requested, we have to convert this data structure (which we assume 
		//   to be an array) to a DataTable in order to apply the requested DataTable_Renderer (for example XML)
		elseif(is_array($toReturn))
		{
			if($this->outputFormatRequested == 'original')
			{
				// we handle the serialization. Because some php array have a very special structure that 
				// couldn't be converted with the automatic DataTable->loadFromSimpleArray
				// the user may want to request the original PHP data structure serialized by the API
				// in case he has to setup serialize=1 in the URL
				if($this->caseRendererPHPSerialize( $defaultSerialize = 0))
				{
					$toReturn = serialize($toReturn);
				}
			}
			else
			{
				$dataTable = new Piwik_DataTable();
				$dataTable->loadFromSimpleArray($toReturn);
				$toReturn = $this->getRenderedDataTable($dataTable);
			}
		}
		// bool // integer // float // object is serialized
		// NB: null value is already handled by the isset() test above
		else
		{
			// original data structure requested, we return without process
			if( $this->outputFormatRequested == 'original' )
			{
				return $toReturn;
			}
			
			if( $toReturn === true )
			{
				$toReturn = 'true';
			}
			elseif( $toReturn === false )
			{
				$toReturn = 'false';
			}
			elseif( is_object($toReturn)
						|| is_resource($toReturn)
						)
			{
				return $this->getExceptionOutput( ' The API cannot handle this data structure. You can get the data internally by directly using the class.', $this->outputFormatRequested);
			}
			return $this->getStandardSuccessOutput($this->outputFormatRequested, $message = $toReturn);
		}
		return $toReturn;
	}
	
	/**
	 * Returns a success $message in the requested $format 
	 *
	 * @param string $format xml/json/php/csv
	 * @param string $message
	 * @return string
	 */
	protected function getStandardSuccessOutput($format, $message = 'ok')
	{
		switch($format)
		{
			case 'xml':
				@header('Content-type: text/xml');
				$return = 
					'<?xml version="1.0" encoding="utf-8" ?>'.
					'<result>'.
					'	<success message="'.$message.'" />'.
					'</result>';
			break;
			case 'json':
				@header( "Content-type: application/json" );
				$return = '{"result":"success", "message":"'.$message.'"}';
			break;
			case 'php':
				$return = array('result' => 'success', 'message' => $message);
				if($this->caseRendererPHPSerialize())
				{
					$return = serialize($return);
				}
			break;
			
			case 'csv':
				header("Content-type: application/vnd.ms-excel");
				header("Content-Disposition: attachment; filename=piwik-report-export.csv");	
				$return = "message\n".$message;
			break;
			
			default:
				$return = 'Success:'.$message;
			break;
		}
		
		return $return;
	}
	
	/**
	 * Returns an error $message in the requested $format 
	 *
	 * @param string $format xml/json/php/csv
	 * @param string $message
	 * @return string
	 */
	function getExceptionOutput($message, $format)
	{
		switch($format)
		{
			case 'xml':
				@header('Content-type: text/xml');
				$return = 
					'<?xml version="1.0" encoding="utf-8" ?>'.
					'<result>'.
					'	<error message="'.htmlentities($message).'" />'.
					'</result>';
			break;
			case 'json':
				@header( "Content-type: application/json" );
				$return = '{"result":"error", "message":"'.htmlentities($message).'"}';
			break;
			case 'php':
				$return = array('result' => 'error', 'message' => $message);
				if($this->caseRendererPHPSerialize())
				{
					$return = serialize($return);
				}
			break;
			default:
				$return = 'Error: '.$message;
			break;
		}
		
		return $return;
	}

	/**
	 * Apply the specified renderer to the DataTable
	 * 
	 * @param Piwik_DataTable
	 * @return Piwik_DataTable
	 */
	protected function getRenderedDataTable($dataTable)
	{
		// Renderer
		$format = Piwik_Common::getRequestVar('format', 'php', 'string', $this->requestToUse);
		$format = strtolower($format);
		
		// if asked for original dataStructure
		if($format == 'original')
		{
			// if the original dataStructure is a simpleDataTable and has only one row, we return the value
			if($dataTable instanceof Piwik_DataTable_Simple
				&& $dataTable->getRowsCount() == 1)
			{
				return $dataTable->getRowFromId(0)->getColumn('value');
			}
			
			// the original data structure can be asked as serialized. 
			// but by default it's not serialized
			if($this->caseRendererPHPSerialize( $defaultSerialize = 0))
			{
//				var_export($dataTable);exit;
				$dataTable = serialize($dataTable);
			}
			return $dataTable;
		}
		
		$renderer = Piwik_DataTable_Renderer::factory($format);
		$renderer->setTable($dataTable);
		
		if($format == 'php')
		{
			$renderer->setSerialize( $this->caseRendererPHPSerialize());
		}
		
		$toReturn = $renderer->render();
		return $toReturn;
	}
	
	/**
	 * Returns true if the user requested to serialize the output data (&serialize=1 in the request)
	 *
	 * @param $defaultSerializeValue Default value in case the user hasn't specified a value
	 * @return bool
	 */	
	protected function caseRendererPHPSerialize($defaultSerializeValue = 1)
	{
		$serialize = Piwik_Common::getRequestVar('serialize', $defaultSerializeValue, 'int', $this->requestToUse);
		if($serialize)
		{
			return true;
		}
		else
		{
			return false;		
		}
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
		);
		
		return $genericFilters;
	}
	
	
	/**
	 * Apply generic filters to the DataTable object resulting from the API Call.
	 * Disable this feature by setting the parameter disable_generic_filters to 1 in the API call request.
	 * 
	 * @param Piwik_DataTable
	 * @return void
	 */
	protected function applyDataTableGenericFilters($dataTable)
	{
		// Generic filters
		// PatternFileName => Parameter names to match to constructor parameters
		/*
		 * Order to apply the filters:
		 * 1 - Filter that remove filtered rows
		 * 2 - Filter that sort the remaining rows
		 * 3 - Filter that keep only a subset of the results
		 */
		$genericFilters = Piwik_API_Request::getGenericFiltersInformation();
		
		// if the flag disable_generic_filters is defined we skip the generic filters
		if(Piwik_Common::getRequestVar('disable_generic_filters', 'false', 'string', $this->requestToUse) != 'false')
		{
			return;
		}
		
		foreach($genericFilters as $filterName => $parameters)
		{
			$filterParameters = array();
			$exceptionRaised = false;
			
			foreach($parameters as $name => $info)
			{
				// parameter type to cast to
				$type = $info[0];
				
				// default value if specified, when the parameter doesn't have a value
				$defaultValue = null;
				if(isset($info[1]))
				{
					$defaultValue = $info[1];
				}
				
				try {
					$value = Piwik_Common::getRequestVar($name, $defaultValue, $type, $this->requestToUse);
					settype($value, $type);
					$filterParameters[] = $value;
				}
				catch(Exception $e)
				{
//					print($e->getMessage());
					$exceptionRaised = true;
					break;
				}
			}
			
			if(!$exceptionRaised)
			{
//				var_dump($filterParameters);
				assert(count($filterParameters)==count($parameters));
				
				// a generic filter class name must follow this pattern
				$class = "Piwik_DataTable_Filter_".$filterName;
				
				if($filterName == 'Limit')
				{
					$dataTable->setRowsCountBeforeLimitFilter();
				}
				
				// build the set of parameters for the filter					
				$filterParameters = array_merge(array($dataTable), $filterParameters);

				// make a reflection object
				$reflectionObj = new ReflectionClass($class);
				
				// use Reflection to create a new instance, using the $args
				$filter = $reflectionObj->newInstanceArgs($filterParameters); 
			}
		}
	}

}