<?php

class Piwik_API_DataTableGenericFilter
{
	function __construct( $datatable, $request )
	{
		$this->table = $datatable;
		$this->request = $request;
	}

	public function filter()
	{
		$this->applyGenericFilters($this->table);
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
								'filter_excludelowpop_value'=> array('float', '0'),
						),
			'AddColumnsWhenShowAllColumns'	=> array(
								'filter_add_columns_when_show_all_columns'	=> array('integer')
						),
			'UpdateColumnsWhenShowAllGoals'	=> array(
								'filter_update_columns_when_show_all_goals'	=> array('integer')
						),
			'Sort' => array(
								'filter_sort_column' 		=> array('string', Piwik_Archive::INDEX_NB_VISITS),
								'filter_sort_order' 		=> array('string', Zend_Registry::get('config')->General->dataTable_default_sort_order),
						),
			'Limit' => array(
								'filter_offset' 			=> array('integer', '0'),
								'filter_limit' 				=> array('integer', Zend_Registry::get('config')->General->dataTable_default_limit),
						),
			'SafeDecodeLabel' => array(),
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
	protected function applyGenericFilters($datatable)
	{
		if($datatable instanceof Piwik_DataTable_Array )
		{
			$tables = $datatable->getArray();
			foreach($tables as $table)
			{
				$this->applyGenericFilters($table);
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
					$datatable->setRowsCountBeforeLimitFilter();
				}
				// build the set of parameters for the filter					
				$filterParameters = array_merge(array($datatable), $filterParameters);
				// use Reflection to create a new instance of the filter, given parameters $filterParameters
				$reflectionObj = new ReflectionClass($class);
				$filter = $reflectionObj->newInstanceArgs($filterParameters); 
			}
		}
	}
}

