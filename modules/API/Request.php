<?php
/**
 * An API request is the object used to make a call to the API and get the result.
 * The request has the form of a normal GET request, ie. parameter_1=X&parameter_2=Y
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
 * @package Piwik_API
 */
class Piwik_API_Request
{
	/**
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
		
		$this->requestToUse = $requestArray;
	}
	
	/**
	 * Returns array( $class, $method) from the given string $class.$method
	 * 
	 * @return array
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
	
	/**
	 * Handles the request to the API.
	 * It first checks that the method called (parameter 'method') is available in the module (it means that the method exists and is public)
	 * It then reads the parameters from the request string and throws an exception if there are absent parameters.
	 * It then calls the API Proxy which will call the method.
	 * If the data resulted from the API call is a Piwik_DataTable then 
	 * 		- we apply the standard filters if the parameters have been found
	 * 		  in the URL. For example to offset,limit the Table you can add the following parameters to any API
	 *  	  call that returns a DataTable: filter_limit=10&filter_offset=20
	 * 		- we apply the filters that have been previously queued on the DataTable
	 * 		- we apply the renderer that generate the DataTable in a given format (XML, PHP, HTML, JSON, etc.) 
	 * 		  the format can be changed using the 'format' parameter in the request.
	 *        Example: format=xml
	 * 
	 * @return mixed The data resulting from the API call  
	 */
	public function process()
	{
		try {
			
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
			
			$parameters = $api->getParametersList($className, $method);
			
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
			
			$returnedValue = call_user_func_array( array( $api->$module, $method), $finalParameters );
			
			$toReturn = $returnedValue;
			
			// If the returned value is an object DataTable we
			// apply the set of generic filters if asked in the URL
			// and we render the DataTable according to the format specified in the URL
			if($returnedValue instanceof Piwik_DataTable)
			{
				$dataTable = $returnedValue;
				
				$this->applyDataTableGenericFilters($dataTable);
				$dataTable->applyQueuedFilters();
				$toReturn = $this->getRenderedDataTable($dataTable);
				
			}
			
			
			if(!is_array($toReturn) // an empty array is not considered as "nothing has been returned"
				&& empty($toReturn))
			{
				$format = Piwik_Common::getRequestVar('format', 'xml', 'string', $this->requestToUse);
				$toReturn = $this->getStandardSuccessOutput($format);
			}
			
		} catch(Exception $e ) {
			$format = Piwik_Common::getRequestVar('format', 'xml', 'string', $this->requestToUse);
			
			// if it is not a direct API call, we are requesting the original data structure
			// and we actually are handling this exception at the top level in the FrontController
			if($format == 'original')
			{
				throw $e;
			}
			$toReturn =  $this->getExceptionOutput( $e->getMessage(), $format);
			
		}
		
		return $toReturn;
	}
	
	
	function getStandardSuccessOutput($format)
	{
		$return = 'TO OVERWRITE! getStandardSuccessOutput()';
		switch($format)
		{
			case 'xml':
				header('Content-type: text/xml');
				$return = 
					'<?xml version="1.0" encoding="utf-8" ?>'.
					'<result>'.
					'	<success message="ok" />'.
					'</result>';
			break;
			case 'json':
				header( "Content-type: application/json" );
				$return = '{"result":"success", "message":"ok"}';
			break;
			case 'php':
				$return = array('result' => 'success', 'message' => 'ok');
				if($this->caseRendererPHPSerialize())
				{
					$return = serialize($return);
				}
			break;
			default:
				$return = 'Success:ok';
			break;
		}
		
		return $return;
	}
	function getExceptionOutput($message, $format)
	{
		$return = 'TO OVERWRITE! getExceptionOutput()';
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
				$return = 'Error:'.$message;
			break;
		}
		
		return $return;
	}

	/**
	 * Apply the specified renderer to the DataTable
	 * @return Piwik_DataTable
	 */
	protected function getRenderedDataTable($dataTable)
	{
		// Renderer
		$format = Piwik_Common::getRequestVar('format', 'php', 'string', $this->requestToUse);
		
		// if asked for original dataStructure
		if($format == 'original')
		{
			// if the original dataStructure is a simple data structure
			if($dataTable instanceof Piwik_DataTable_Simple
				&& $dataTable->getRowsCount() == 1)
			{
				return $dataTable->getRowFromId(0)->getColumn('value');
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
	
	
	function caseRendererPHPSerialize()
	{
		$serialize = Piwik_Common::getRequestVar('serialize', 1, 'int', $this->requestToUse);
		if($serialize)
		{
			return true;
		}
		else
		{
			return false;		
		}
	}
	
	public static function getGenericFiltersInformation()
	{
		$genericFilters = array(
			
			'Pattern' => array(
								'filter_column' 			=> array('string'), 
								'filter_pattern' 			=> array('string'),
						),
			'ExcludeLowPopulation'	=> array(
								'filter_excludelowpop' 		=> array('string'), 
								'filter_excludelowpop_value'=> array('float'),
						),
			'Sort' => array(
								'filter_sort_column' 		=> array('string', Piwik_Archive::INDEX_NB_VISITS),
								'filter_sort_order' 		=> array('string', 'desc'),
						),
			'Limit' => array(
								'filter_offset' 			=> array('integer', '0'),
								'filter_limit' 				=> array('integer', Zend_Registry::get('config')->General->dataTable_default_limit),
						),
		);
		
		return $genericFilters;
	}
	
	
	/**
	 * Applys generic filters to the DataTable object resulting from the API Call.
	 * @return void
	 */
	protected function applyDataTableGenericFilters($dataTable)
	{
//		print($dataTable->getRowsCount());
//		$filter = new Piwik_DataTable_Filter_Sort($dataTable, 2, 'desc');
		
		// Generic filters
		// PatternFileName => Parameter names to match to constructor parameters
		/*
		 * Order to apply the filters:
		 * 1 - Filter that remove filtered rows
		 * 2 - Filter that sort the remaining rows
		 * 3 - Filter that keep only a subset of the results
		 */
		$genericFilters = Piwik_API_Request::getGenericFiltersInformation();
		
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