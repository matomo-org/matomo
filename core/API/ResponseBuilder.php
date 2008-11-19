<?php

class Piwik_API_ResponseBuilder
{
	private $request = null;
	private $outputFormat = null;
	
	public function __construct($request, $outputFormat)
	{
		$this->request = $request;
		$this->outputFormat = $outputFormat;
	}
	
	/**
	 * This method processes the data resulting from the API call.
	 * 
	 * - If the data resulted from the API call is a Piwik_DataTable then 
	 * 		- we apply the standard filters if the parameters have been found
	 * 		  in the URL. For example to offset,limit the Table you can add the following parameters to any API
	 *  	  call that returns a DataTable: filter_limit=10&filter_offset=20
	 * 		- we apply the filters that have been previously queued on the DataTable
	 *        @see Piwik_DataTable::queueFilter()
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
	public function getResponse($value)
	{ 
		// If the returned value is an object DataTable we
		// apply the set of generic filters if asked in the URL
		// and we render the DataTable according to the format specified in the URL
		if($value instanceof Piwik_DataTable
			|| $value instanceof Piwik_DataTable_Array)
		{
			return $this->handleDataTable($value);
		}
		
		// Case an array is returned from the API call, we convert it to the requested format
		// - if calling from inside the application (format = original)
		//    => the data stays unchanged (ie. a standard php array or whatever data structure)
		// - if any other format is requested, we have to convert this data structure (which we assume 
		//   to be an array) to a DataTable in order to apply the requested DataTable_Renderer (for example XML)
		if(is_array($value))
		{
			return $this->handleArray($value);
		}
		
		// when null or void is returned from the api call, we handle it as a successful operation 
		if(!isset($value))
		{
			return $this->handleSuccess();
		}
		
		// original data structure requested, we return without process
		if( $this->outputFormat == 'original' )
		{
			return $value;
		}
	
		if( is_object($value)
				|| is_resource($value))
		{
			return $this->getResponseException(new Exception('The API cannot handle this data structure.'));
		}
		
		// bool // integer // float // serialized object 
		return $this->handleScalar($value);
	}
	
	/**
	 * Returns an error $message in the requested $format 
	 *
	 * @param string $message
	 * @param string $format xml/json/php/csv
	 * @return string
	 */
	public function getResponseException(Exception $e)
	{
		$message = htmlentities($e->getMessage(), ENT_COMPAT, "UTF-8");
		switch($this->outputFormat)
		{
			case 'original':
				throw $e;
			break;
			case 'xml':
				@header("Content-Type: text/xml;charset=utf-8");
				$return = 
					"<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n" .
					"<result>\n".
					"\t<error message=\"".$message."\" />\n".
					"</result>";
			break;
			case 'json':
				@header( "Content-type: application/json" );
				// we remove the \n from the resulting string as this is not allowed in json
				$message = str_replace("\n","",$message);
				$return = '{"result":"error", "message":"'.$message.'"}';
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
	 * Returns an array containing the information of the generic Piwik_DataTable_Filter 
	 * to be applied automatically to the data resulting from the API calls.
	 *
	 * Order to apply the filters:
	 * 1 - Filter that remove filtered rows
	 * 2 - Filter that sort the remaining rows
	 * 3 - Filter that keep only a subset of the results
	 * 4 - Presentation filters
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
			'ExactMatch' => array(
								'filter_exact_column'		=> array('string'),
								'filter_exact_pattern'		=> array('array'),
						),
			'ExcludeLowPopulation'	=> array(
								'filter_excludelowpop' 		=> array('string'), 
								'filter_excludelowpop_value'=> array('float'),
						),
			'AddColumnsWhenShowAllColumns'	=> array(
								'filter_add_columns_when_show_all_columns'	=> array('integer')
						),
			'Sort' => array(
								'filter_sort_column' 		=> array('string', Piwik_Archive::INDEX_NB_VISITS),
								'filter_sort_order' 		=> array('string', Zend_Registry::get('config')->General->dataTable_default_sort_order),
						),
			'Limit' => array(
								'filter_offset' 			=> array('integer', '0'),
								'filter_limit' 				=> array('integer', Zend_Registry::get('config')->General->dataTable_default_limit),
						),
			'SafeDecodeLabel' => array(
								'filter_safe_decode_label'	=> array('integer')
						),
		);
		
		return $genericFilters;
	}

	protected function handleDataTableGenericFilters($datatable)
	{
		if($datatable instanceof Piwik_DataTable)
		{
			$this->applyDataTableGenericFilters($datatable);
		}
		elseif($datatable instanceof Piwik_DataTable_Array)
		{
			$tables = $datatable->getArray();
			foreach($tables as $table)
			{
				$this->applyDataTableGenericFilters($table);
			}
		}
		return $datatable;
	}
	
	/**
	 * Returns true if the user requested to serialize the output data (&serialize=1 in the request)
	 *
	 * @param $defaultSerializeValue Default value in case the user hasn't specified a value
	 * @return bool
	 */	
	protected function caseRendererPHPSerialize($defaultSerializeValue = 1)
	{
		$serialize = Piwik_Common::getRequestVar('serialize', $defaultSerializeValue, 'int', $this->request);
		if($serialize)
		{
			return true;
		}
		return false;	
	}
	
	/**
	 * Apply the specified renderer to the DataTable
	 * 
	 * @param Piwik_DataTable
	 * @return string
	 */
	protected function getRenderedDataTable($dataTable)
	{
		$format = Piwik_Common::getRequestVar('format', 'php', 'string', $this->request);
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
			
			// by default "original" data is not serialized
			if($this->caseRendererPHPSerialize( $defaultSerialize = 0))
			{
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
		
		return $renderer->render();
	}
	
	
	/**
	 * Returns a success $message in the requested $format 
	 *
	 * @param string $format xml/json/php/csv
	 * @param string $message
	 * @return string
	 */
	protected function handleSuccess( $message = 'ok' )
	{
		switch($this->outputFormat)
		{
			case 'xml':
				@header("Content-Type: text/xml;charset=utf-8");
				$return = 
					"<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n" .
					"<result>\n".
					"\t<success message=\"".$message."\" />\n".
					"</result>";
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

	protected function handleScalar($scalar)
	{
		if( $scalar === true )
		{
			$response = 'true';
		}
		elseif( $scalar === false )
		{
			$response = 'false';
		}
		
		require_once "DataTable/Simple.php";
		$dataTable = new Piwik_DataTable_Simple();
		$dataTable->loadFromArray( array($scalar) );
		return $this->getRenderedDataTable($dataTable);
	}

	protected function handleDataTable($datatable)
	{
		// if the flag disable_generic_filters is defined we skip the generic filters
		if(Piwik_Common::getRequestVar('disable_generic_filters', 'false', 'string', $this->request) == 'false')
		{
			$datatable = $this->handleDataTableGenericFilters($datatable);
		}
		
		// if the flag disable_queued_filters is defined we skip the filters that were queued
		if(Piwik_Common::getRequestVar('disable_queued_filters', 'false', 'string', $this->request) == 'false')
		{
			$datatable->applyQueuedFilters();
		}
		return $this->getRenderedDataTable($datatable);
	}
	
	protected function handleArray($array)
	{
		if($this->outputFormat == 'original')
		{
			// we handle the serialization. Because some php array have a very special structure that 
			// couldn't be converted with the automatic DataTable->loadFromSimpleArray
			// the user may want to request the original PHP data structure serialized by the API
			// in case he has to setup serialize=1 in the URL
			if($this->caseRendererPHPSerialize( $defaultSerialize = 0))
			{
				return serialize($array);
			}
		}
		else
		{
			$dataTable = new Piwik_DataTable();
			$dataTable->loadFromSimpleArray($array);
			return $this->getRenderedDataTable($dataTable);
		}
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
		if($dataTable instanceof Piwik_DataTable_Array )
		{
			$tables = $dataTable->getArray();
			foreach($tables as $table)
			{
				$this->applyDataTableGenericFilters($table);
			}
			return;
		}
		
		$genericFilters = self::getGenericFiltersInformation();
		
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
					$value = Piwik_Common::getRequestVar($name, $defaultValue, $type, $this->request);
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
				// a generic filter class name must follow this pattern
				$class = "Piwik_DataTable_Filter_".$filterName;
				
				if($filterName == 'Limit')
				{
					$dataTable->setRowsCountBeforeLimitFilter();
				}
				
				// build the set of parameters for the filter					
				$filterParameters = array_merge(array($dataTable), $filterParameters);

				// use Reflection to create a new instance of the filter, given parameters $filterParameters
				$reflectionObj = new ReflectionClass($class);
				$filter = $reflectionObj->newInstanceArgs($filterParameters); 
			}
		}
	}
}
