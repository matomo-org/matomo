<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @package Piwik_ViewDataTable
 */


require_once "API/Request.php";
/**
 * 
 * @package Piwik_Visualization
 *
 */

abstract class Piwik_ViewDataTable
{
	protected $dataTableTemplate = null;
	
	protected $mainAlreadyExecuted = false;
	
	protected $JSsearchBox 				= true;
	protected $JSoffsetInformation 		= true;
	protected $JSexcludeLowPopulation 	= true;
	protected $JSsortEnabled 			= true;
	protected $showFooter				= true;
	
	protected $currentControllerAction;
	
	protected $actionToLoadTheSubTable = null;
	
	public $dataTable; // data table
	
	protected $moduleNameAndMethod;
	
	// do we need all the children of the datatables?
	protected $recursiveDataTableLoad   = false;
	
	protected $variablesDefault = array();
	
	protected $idSubtable = false;
	
	/**
	 * 
	 * @return Piwik_ViewDataTable Data table
	 */
	static public function factory( $type = null, $defaultType = 'table')
	{
		if(is_null($type))
		{
			$type = Piwik_Common::getRequestVar('viewDataTable', $defaultType, 'string');
		}
		
		switch($type)
		{
			case 'cloud':
				require_once "ViewDataTable/Cloud.php";
				return new Piwik_ViewDataTable_Cloud();			
			break;
			
			case 'graphPie':
				require_once "ViewDataTable/Graph.php";
				return new Piwik_ViewDataTable_Graph_ChartPie();
			break;			
			
			case 'graphVerticalBar':
				require_once "ViewDataTable/Graph.php";
				return new Piwik_ViewDataTable_Graph_ChartVerticalBar();
			break;	
			
			case 'graphEvolution':
				require_once "ViewDataTable/Graph.php";
				return new Piwik_ViewDataTable_Graph_ChartEvolution();
			break;	
			
			case 'sparkline':
				require_once "ViewDataTable/Sparkline.php";
				return new Piwik_ViewDataTable_Sparkline();
			break;	
			
			case 'generateDataChartVerticalBar':
				require_once "ViewDataTable/GenerateGraphData.php";
				return new Piwik_ViewDataTable_GenerateGraphData_ChartVerticalBar();
			break;
						
			case 'generateDataChartPie':
				require_once "ViewDataTable/GenerateGraphData.php";
				return new Piwik_ViewDataTable_GenerateGraphData_ChartPie();
			break;
			
			case 'generateDataChartEvolution':
				require_once "ViewDataTable/GenerateGraphData.php";
				return new Piwik_ViewDataTable_GenerateGraphData_ChartEvolution();
				
			break;
				
			case 'table':
			default:
				require_once "ViewDataTable/Html.php";
				return new Piwik_ViewDataTable_Html();
			break;
		}
	}
	
	//TODO comment
	function init( $currentControllerName,
						$currentControllerAction, 
						$moduleNameAndMethod, 
						$actionToLoadTheSubTable = null)
	{
		$this->currentControllerName = $currentControllerName;
		$this->currentControllerAction = $currentControllerAction;
		$this->moduleNameAndMethod = $moduleNameAndMethod;
		$this->actionToLoadTheSubTable = $actionToLoadTheSubTable;
		
		$this->idSubtable = Piwik_Common::getRequestVar('idSubtable', false, 'int');
		
		$this->method = $moduleNameAndMethod;
		
		$this->JSsearchBox = Piwik_Common::getRequestVar('show_search', true);
		$this->showFooter = Piwik_Common::getRequestVar('showDataTableFooter', true);
		$this->variablesDefault['filter_excludelowpop_default'] = 'false';
		$this->variablesDefault['filter_excludelowpop_value_default'] = 'false';	
	}
	
	
	abstract public function main();
	
	public function render()
	{
		return $this->getView()->render();
	}
	
	/**
	 * For convenience, the client code can call methods that are defined in a specific children class
	 * without testing the children class type, which would trigger an error with a different children class.
	 * For example, ViewDataTable/Html.php defines a setColumnsToDisplay(). The client code calls this methods even if
	 * the ViewDataTable object is a ViewDataTable_Cloud instance. But ViewDataTable_Cloud doesn't define the 
	 * setColumnsToDisplay() method. Because we don't want to force users to test for the object type we simply catch these
	 * calls when they are not defined in the child and do nothing.  
	 *
	 * @param string $function
	 * @param array $args
	 */
	public function __call($function, $args)
	{
	}
	
	
	
	// given a DataTable_Array made of DataTable_Simple
	// returns PHP array containing rows of array( label => X, value => Y)
	protected function generateDataFromDataTableArray( $dataTableArray)
	{
		// we have to fill a $data array with each row = array('label' => X, 'value' => y)
		$data = array();
		foreach($dataTableArray->getArray() as $keyName => $table)
		{
			$value = false;
			
			$onlyRow = $table->getFirstRow();
			if($onlyRow !== false)
			{
				$value = $onlyRow->getColumn('value');
				if($value == false)
				{
					// TEMP
					// quite a hack, useful in the case at this point we do have a normal row with nb_visits, nb_actions, nb_uniq_visitors, etc.
					// instead of the dataTable_Simple row (label, value) 
					// to do it properly we'd need to
					// - create a filter that removes columns
					// - apply this filter to keep only the column called nb_unique_visitors
					// - rename this column as 'value'
					// and at this point the getcolumn('value') would have worked
					// this code is executed eg. when displaying a sparkline for the last 30 days displaying the number of unique visitors coming from search engines
					
					// another solution would be to add a method to the Referers API giving directly the integer 'visits from search engines'
					// and we would build automatically the dataTable_array of datatatble_simple from these integers
					// but we'd have to add this integer to be recorded during archiving etc.
					$value = $onlyRow->getColumn('nb_unique_visitors');
				}
			}
		
			if($value === false)
			{
				$value = 0;
			}
			$data[] = array(
					'label' => $keyName,
					'value' => $value
				);
		}
		return $data;
	}
	
	public function getView()
	{
		return $this->view;
	}

	protected function getUniqIdTable()
	{
		// the $uniqIdTable variable is used as the DIV ID in the rendered HTML
		// we use the current Controller action name as it is supposed to be unique in the rendered page 
		$uniqIdTable = $this->currentControllerAction;

		// if we request a subDataTable the $this->currentControllerAction DIV ID is already there in the page
		// we make the DIV ID really unique by appending the ID of the subtable requested
		if( $this->idSubtable != false)
		{			
			$uniqIdTable = 'subDataTable_' . $this->idSubtable;
		}
		return $uniqIdTable;
	}
	
	protected function getJavascriptVariablesToSet()
	{
		// build javascript variables to set
		$javascriptVariablesToSet = array();
		
		$genericFilters = Piwik_API_Request::getGenericFiltersInformation();
		foreach($genericFilters as $filter)
		{
			foreach($filter as $filterVariableName => $filterInfo)
			{
				// if there is a default value for this filter variable we set it 
				// so that it is propagated to the javascript
				if(isset($filterInfo[1]))
				{
					$javascriptVariablesToSet[$filterVariableName] = $filterInfo[1];
					
					// we set the default specified column and Order to sort by
					// when this javascript variable is not set already
					// for example during an AJAX call this variable will be set in the URL
					// so this will not be executed (and the default sorted not be used as the sorted column might have changed in the meanwhile)
					if( false !== ($defaultValue = $this->getDefault($filterVariableName)))
					{
						$javascriptVariablesToSet[$filterVariableName] = $defaultValue;
					}
				}
			}
		}
		
//		var_dump($javascriptVariablesToSet);exit;
		// See 
		foreach($_GET as $name => $value)
		{
			try{
				$requestValue = Piwik_Common::getRequestVar($name);
			}
			catch(Exception $e) {
				$requestValue = '';
			}
			$javascriptVariablesToSet[$name] = $requestValue;
		}
		
		// at this point there are some filters values we  may have not set, 
		// case of the filter without default values and parameters set directly in this class
		// for example setExcludeLowPopulation
		// we go through all the $this->variablesDefault array and set the variables not set yet
		foreach($this->variablesDefault as $name => $value)
		{
			if(!isset($javascriptVariablesToSet[$name] ))
			{
				$javascriptVariablesToSet[$name] = $value;
			}
		}
		
		
		$javascriptVariablesToSet['module'] = $this->currentControllerName;
		$javascriptVariablesToSet['action'] = $this->currentControllerAction;
		
		if(!is_null($this->actionToLoadTheSubTable))
		{
			$javascriptVariablesToSet['actionToLoadTheSubTable'] = $this->actionToLoadTheSubTable;
		}
		
//		var_dump($this->variablesDefault);
//		var_dump($javascriptVariablesToSet); exit;
		
		if($this->dataTable)
		{
			$javascriptVariablesToSet['totalRows'] = $this->dataTable->getRowsCountBeforeLimitFilter();
		}	
		$javascriptVariablesToSet['show_search'] = $this->getSearchBox();
		$javascriptVariablesToSet['show_offset_information'] = $this->getOffsetInformation();
		$javascriptVariablesToSet['show_exclude_low_population'] = $this->getExcludeLowPopulation();
		$javascriptVariablesToSet['enable_sort'] = $this->getSort();
		
		return $javascriptVariablesToSet;
	}
	
	protected function loadDataTableFromAPI( $idSubtable = false)
	{
		if($idSubtable === false)
		{
			$idSubtable = $this->idSubtable;
		}
		// we prepare the string to give to the API Request
		// we setup the method and format variable
		// - we request the method to call to get this specific DataTable
		// - the format = original specifies that we want to get the original DataTable structure itself, not rendered
		$requestString = 'method='.$this->moduleNameAndMethod
						.'&format=original'
					;
		if( $this->recursiveDataTableLoad )
		{
			$requestString .= '&expanded=1';
		}
		
		// if a subDataTable is requested we add the variable to the API request string
		if( $idSubtable != false)
		{
			$requestString .= '&this->idSubtable='.$idSubtable;
		}
		
		$toSetEventually = array(
			'filter_limit',
			'filter_sort_column',
			'filter_sort_order',
			'filter_excludelowpop',
			'filter_excludelowpop_value',
			'filter_column', 
			'filter_pattern' 
		);
		foreach($toSetEventually as $varToSet)
		{
			$value = $this->getDefaultOrCurrent($varToSet);
			if( false !== $value )
			{
				$requestString .= '&'.$varToSet.'='.$value;
			}
		}
//		echo $requestString;exit;
		// We finally make the request to the API
		$request = new Piwik_API_Request($requestString);
		
		// and get the DataTable structure
		$dataTable = $request->process();

//		echo $dataTable;exit;

		$this->dataTable = $dataTable;
	}

	public function setTemplate( $tpl )
	{
		$this->dataTableTemplate = $tpl;
	}
	
	protected function getDefaultOrCurrent( $nameVar )
	{
		if(isset($_REQUEST[$nameVar]))
		{
			return $_REQUEST[$nameVar];
		}
		$default = $this->getDefault($nameVar);
		return $default;
	}

	
	protected function getDefault($nameVar)
	{
		if(!isset($this->variablesDefault[$nameVar]))
		{
			return false;
		}
		return $this->variablesDefault[$nameVar];
	}
	
	public function disableSort()
	{
		$this->JSsortEnabled = 'false';		
	}
	
	public function getSort()
	{
		return $this->JSsortEnabled;		
	}
	
	public function disableOffsetInformation()
	{
		$this->JSoffsetInformation = 'false';		
	}
	public function getOffsetInformation()
	{
		return $this->JSoffsetInformation;
	}
	
	public function disableSearchBox()
	{
		$this->JSsearchBox = 'false';
	}
	
	public function getSearchBox()
	{
		return $this->JSsearchBox;
	}
	
	/**
	 * When this method is called, the output will not contain the template datatable_footer.tpl
	 *
	 * @return void
	 */
	public function doNotShowFooter()
	{
		$this->showFooter = false;
	}
	
	public function disableExcludeLowPopulation()
	{
		$this->JSexcludeLowPopulation = 'false';
	}
	
	public function getExcludeLowPopulation()
	{
		return $this->JSexcludeLowPopulation;
	}
	
	
	public function setExcludeLowPopulation( $value = null, $columnId = null )
	{
		if( is_null( $value) ) 
		{
			throw new Exception("setExcludeLowPopulation() value shouldn't be null");
		}
		
		if(is_null($columnId))
		{
			$columnId = Piwik_Archive::INDEX_NB_VISITS;
		}
		
		// column to use to enable low population exclusion if != false
		$this->variablesDefault['filter_excludelowpop_default'] 
			= $this->variablesDefault['filter_excludelowpop']
			= $columnId;
		
		// the minimum value a row must have to be returned 
		$this->variablesDefault['filter_excludelowpop_value_default'] 
			= $this->variablesDefault['filter_excludelowpop_value']
			= $value;	
	}
	
	public function setSearchPattern($pattern, $column)
	{
		$this->variablesDefault['filter_pattern'] = $pattern;
		$this->variablesDefault['filter_column'] = $column;
	}

	public function setLimit( $limit )
	{
		if($limit != 0)
		{
			$this->variablesDefault['filter_limit'] = $limit;
		}
	}
	public function setSortedColumn( $columnId, $order = 'desc')
	{
		$this->variablesDefault['filter_sort_column']= $columnId;
		$this->variablesDefault['filter_sort_order']= $order;
	}
}