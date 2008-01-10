<?php

// TODO clean this unit should'nt read the requests parameters (via getRequestVar)
// This unit should be some sort of configuration manager that would
// redirect the config info on the renderer
// currently this is ugly, YES!!!
require_once "iView.php";
class Piwik_ViewDataTable
{
	protected $dataTableTemplate = null;
	
	protected $currentControllerAction;
	protected $moduleNameAndMethod;
	protected $actionToLoadTheSubTable;
	
	public $dataTable; // data table
	public $arrayDataTable; // phpArray
	
	// do we need all the children of the datatables?
	protected $recursiveDataTableLoad   = false;
	
	protected $JSsearchBox 				= true;
	protected $JSoffsetInformation 		= true;
	protected $JSexcludeLowPopulation 	= true;
	protected $JSsortEnabled 			= true;
	
	protected $mainAlreadyExecuted = false;
	protected $columnsToDisplay = array();
	protected $variablesDefault = array();
	
	const DEFAULT_COLUMN_EXCLUDE_LOW_POPULATION = 2;
	
	static public function factory( $type = null )
	{
		if(is_null($type))
		{
			$type = Piwik_Common::getRequestVar('viewDataTable', 'table', 'string');
		}
		
		// TODO: instead of giving the parameter to the constructor we should really
		// have only one class per type view renderer
		switch($type)
		{
			case 'cloud':
				require_once "ViewDataTable/Cloud.php";
				return new Piwik_ViewDataTable_Cloud($type);			
			break;
			
			case 'graphPie':
				require_once "ViewDataTable/Graph.php";
				return new Piwik_ViewDataTable_Graph_ChartPie();
			break;			
			
			case 'graphVerticalBar':
				require_once "ViewDataTable/Graph.php";
				return new Piwik_ViewDataTable_Graph_ChartVerticalBar();
			break;	
			
			case 'generateDataChartVerticalBar':
				require_once "ViewDataTable/GenerateGraphData.php";
				return new Piwik_ViewDataTable_GenerateGraphData_ChartVerticalBar();
			break;
						
			case 'generateDataChartPie':
				require_once "ViewDataTable/GenerateGraphData.php";
				return new Piwik_ViewDataTable_GenerateGraphData_ChartPie();
			break;
				
			case 'table':
			default:
				return new Piwik_ViewDataTable($type);
			break;
		}
	}
		
	function init( $currentControllerAction, 
						$moduleNameAndMethod, 
						$actionToLoadTheSubTable = null)
	{
		$this->currentControllerAction = $currentControllerAction;
		$this->moduleNameAndMethod = $moduleNameAndMethod;
		$this->actionToLoadTheSubTable = $actionToLoadTheSubTable;
		$this->dataTableTemplate = 'Home/templates/datatable.tpl';
		
		$this->idSubtable = Piwik_Common::getRequestVar('idSubtable', false,'int');
		
		$this->method = $moduleNameAndMethod;
		$this->variablesDefault['filter_excludelowpop_default'] = 'false';
		$this->variablesDefault['filter_excludelowpop_value_default'] = 'false';	
	}
	
	function getView()
	{
		//TODO check at some point that the class implements the interface iView
		return $this->view;
	}
	
	public function setTemplate( $tpl )
	{
		$this->dataTableTemplate = $tpl;
	}
	
	public function main()
	{
		if($this->mainAlreadyExecuted)
		{
			return;
		}
		$this->mainAlreadyExecuted = true;
		
//		$i=0;while($i<1500000){ $j=$i*$i;$i++;}
		
		$this->loadDataTableFromAPI();
	
		// We apply a filter to the DataTable, decoding the label column (useful for keywords for example)
		$filter = new Piwik_DataTable_Filter_ColumnCallbackReplace(
									$this->dataTable, 
									'label', 
									'urldecode'
								);
		
		
		$view = new Piwik_View($this->dataTableTemplate);
		
		$view->id 			= $this->getUniqIdTable();
		
		// We get the PHP array converted from the DataTable
		$phpArray = $this->getPHPArrayFromDataTable();
		
		
		$view->arrayDataTable 	= $phpArray;
		$view->method = $this->method;
		
		$columns = $this->getColumnsToDisplay($phpArray);
		$view->dataTableColumns = $columns;
		
		$nbColumns = count($columns);
		// case no data in the array we use the number of columns set to be displayed 
		if($nbColumns == 0)
		{
			$nbColumns = count($this->columnsToDisplay);
		}
		
		$view->nbColumns = $nbColumns;
		
		$view->javascriptVariablesToSet 
			= $this->getJavascriptVariablesToSet();
		
		
		$this->view = $view;
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
	
	public function setColumnsToDisplay( $arrayIds)
	{
		$this->columnsToDisplay = $arrayIds;
	}
	
	protected function isColumnToDisplay( $idColumn )
	{
		// we return true
		// - we didn't set any column to display (means we display all the columns)
		// - the column has been set as to display
		if( count($this->columnsToDisplay) == 0
			|| in_array($idColumn, $this->columnsToDisplay))
		{
			return true;
		}
		return false;
	}
	
	protected function getColumnsToDisplay($phpArray)
	{
		
		$dataTableColumns = array();
		if(count($phpArray) > 0)
		{
			// build column information
			$id = 0;
			foreach($phpArray[0]['columns'] as $columnName => $row)
			{
				if( $this->isColumnToDisplay( $id, $columnName) )
				{
					$dataTableColumns[]	= array('id' => $id, 'name' => $columnName);
				}
				$id++;
			}
		}
		return $dataTableColumns;
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
	
	public function setSearchRecursive()
	{
		$this->variablesDefault['search_recursive'] = true;
	}
	public function setRecursiveLoadDataTableIfSearchingForPattern()
	{
		try{
			$requestValue = Piwik_Common::getRequestVar('filter_column_recursive');
			$requestValue = Piwik_Common::getRequestVar('filter_pattern_recursive');
			// if the 2 variables are set we are searching for something.
			// we have to load all the children subtables in this case
			
			$this->recursiveDataTableLoad = true;
			return true;
		}
		catch(Exception $e) {
			$this->recursiveDataTableLoad = false;
			return false;
		}
		
	}
	
	public function setExcludeLowPopulation( $value = 30 )
	{
		$this->variablesDefault['filter_excludelowpop_default'] = 2;
		$this->variablesDefault['filter_excludelowpop_value_default'] = $value;	
		$this->variablesDefault['filter_excludelowpop'] = 2;
		$this->variablesDefault['filter_excludelowpop_value'] = $value;	
	}
	
	public function setDefaultLimit( $limit )
	{
		$this->variablesDefault['filter_limit'] = $limit;
	}
	
	public function setSortedColumn( $columnId, $order = 'desc')
	{
		$this->variablesDefault['filter_sort_column']= $columnId;
		$this->variablesDefault['filter_sort_order']= $order;
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
	
	public function disableExcludeLowPopulation()
	{
		$this->JSexcludeLowPopulation = 'false';
	}
	
	public function getExcludeLowPopulation()
	{
		return $this->JSexcludeLowPopulation;
	}
	
	protected function getJavascriptVariablesToSet(	)
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
					// so this will not be executed ( and the default sorted not be used as the sorted column might have changed in the meanwhile)
					if( false !== ($defaultValue = $this->getDefault($filterVariableName)))
					{
						$javascriptVariablesToSet[$filterVariableName] = $defaultValue;
					}
				}
			}
		}
		
//		var_dump($javascriptVariablesToSet);exit;
		//TODO check security of printing javascript variables; inject some JS code here??
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
		);
		foreach($toSetEventually as $varToSet)
		{
			$value = $this->getDefaultOrCurrent($varToSet);
			if( false !== $value )
			{
				$requestString .= '&'.$varToSet.'='.$value;
			}
		}
		// We finally make the request to the API
		$request = new Piwik_API_Request($requestString);
		
		// and get the DataTable structure
		$dataTable = $request->process();

//		echo $dataTable;exit;

		$this->dataTable = $dataTable;
	}

	protected function getPHPArrayFromDataTable( )
	{
		$renderer = Piwik_DataTable_Renderer::factory('php');
		$renderer->setTable($this->dataTable);
		$renderer->setSerialize( false );
		$phpArray = $renderer->render();
		return $phpArray;
	}
}