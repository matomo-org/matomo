<?php

class Piwik_API_Request
{
	function __construct($request = null)
	{
		$requestArray = $_REQUEST;
		
		if(!is_null($request))
		{
			$request = trim($request);
			$request = str_replace(array("\n","\t"),'', $request);
			parse_str($request, $requestArray);
		}
		$this->requestToUse = $requestArray;
	}
	
	private function extractModuleAndMethod($parameter)
	{
		$a = explode('.',$parameter);
		if(count($a) != 2)
		{
			throw new Exception("The method name is invalid. Must be on the form 'module.methodName'");
		}
		return $a;
	}
	
	public function process()
	{
		// read parameters
		$moduleMethod = Piwik_Common::getRequestVar('method', null, null, $this->requestToUse);
		
		list($module, $method) = $this->extractModuleAndMethod($moduleMethod); 
				
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
			if(!empty($defaultValue))
			{
				$requestValue = Piwik_Common::getRequestVar($name, $defaultValue, null, $this->requestToUse);
			}
			else
			{
				$requestValue = Piwik_Common::getRequestVar($name, null, null, $this->requestToUse);				
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
			$toReturn = $this->getRenderedDataTable($dataTable);
			
		}
		
		return $toReturn;
	}
	
	protected function getRenderedDataTable($dataTable)
	{
		// Renderer
		$format = Piwik_Common::getRequestVar('format', 'php', 'string', $this->requestToUse);
		$renderer = Piwik_DataTable_Renderer::factory($format);
		$renderer->setTable($dataTable);
		
		$toReturn = (string)$renderer;
		return $toReturn;
	}
	
	protected function applyDataTableGenericFilters($dataTable)
	{
		
		// Generic filters
		// PatternFileName => Parameter names to match to constructor parameters
		$genericFilters = array(
			'Limit' => array(
								'filter_offset' 	=> 'integer',
								'filter_limit' 		=> 'integer',
						),
			'Pattern' => array(
								'filter_column' => 'string', 
								'filter_pattern' => 'string',
						),
			'Sort' => array(
								'filter_sort_column' => 'string', 
								'filter_sort_order' => 'string',
						),
			'ExcludeLowPopulation'	=> array(
								'filter_excludelowpop' => 'string', 
								'filter_excludelowpop_value' => 'float',
						),
		);
		
		foreach($genericFilters as $filterName => $parameters)
		{
			$filterParameters = array();
			$exceptionRaised = false;
			
			foreach($parameters as $name => $type)
			{
				try {
					$value = Piwik_Common::getRequestVar($name, null, null, $this->requestToUse);
					settype($value, $type);
					$filterParameters[] = $value;
				}
				catch(Exception $e)
				{
					$exceptionRaised = true;
					break;
				}
			}
			
			if(!$exceptionRaised)
			{
				assert(count($filterParameters)==count($parameters));
				
				// a generic filter class name must follow this pattern
				$class = "Piwik_DataTable_Filter_".$filterName;
				
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