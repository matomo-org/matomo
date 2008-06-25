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
 * This class is used to load (from the API) and customize the output of a given DataTable.
 * The main() method will create an object Piwik_iView
 * You can customize the dataTable using the disable* methods.
 * 
 * Example:
 * In the Controller of the plugin VisitorInterest
 * <pre>
 * 	function getNumberOfVisitsPerVisitDuration( $fetch = false)
 *  {
 * 		$view = Piwik_ViewDataTable::factory( 'cloud' );
 * 		$view->init( $this->pluginName,  __FUNCTION__, 'VisitorInterest.getNumberOfVisitsPerVisitDuration' );
 * 		$view->setColumnsToDisplay( array('label','nb_visits') );
 * 		$view->disableSort();
 * 		$view->disableExcludeLowPopulation();
 * 		$view->disableOffsetInformation();
 * 
 *		return $this->renderView($view, $fetch);
 * 	} 
 * </pre>
 * 
 * @see factory() for all the available output (cloud tags, html table, pie chart, vertical bar chart)
 * @package Piwik_ViewDataTable
 *
 */

abstract class Piwik_ViewDataTable
{
	/**
	 * Template file that will be loaded for this view.
	 * Usually set in the Piwik_ViewDataTable_*
	 *
	 * @var string eg. 'Home/templates/cloud.tpl'
	 */
	protected $dataTableTemplate = null;
	
	/**
	 * Flag used to make sure the main() is only executed once
	 *
	 * @var bool
	 */
	protected $mainAlreadyExecuted = false;
	
	/**
	 * Defines if we display the search box under the table
	 * 
	 * @see disableSearchBox()
	 * @see getSearchBox()
	 *
	 * @var bool
	 */
	protected $JSsearchBox 				= true;
	
	/**
	 * Defines if we display the "X-Y of Z" under the table
	 * 
	 * @see disableOffsetInformation()
	 * @see getOffsetInformation()
	 *
	 * @var bool
	 */
	protected $JSoffsetInformation 		= true;
	
	/**
	 * Defines if we display the "Include all population" link under the table
	 * 
	 * @see disableExcludeLowPopulation()
	 * @see getExcludeLowPopulation() 
	 *
	 * @var bool
	 */
	protected $JSexcludeLowPopulation 	= true;
	
	/**
	 * Defines if we include the footer after the dataTable output.
	 * The footer contains all the extra features like the search box, the links Next/Previous, the icons to export in several formats, etc.
	 * Not showing the footer is useful for example when you want to only display a graph without anything else.
	 * 
	 * @see doNotShowFooter()
	 * @see getShowFooter() 
	 *
	 * @var bool
	 */
	protected $showFooter				= true;
	
	/**
	 * Contains the values set for the parameters
	 * @see getJavascriptVariablesToSet()
	 *
	 * @var array
	 */
	protected $variablesDefault = array();
	
	/**
	 * If the current dataTable refers to a subDataTable (eg. keywordsBySearchEngineId for id=X) this variable is set to the Id
	 *
	 * @var bool|int
	 */
	protected $idSubtable = false;
	
	/**
	 * Set to true when the DataTable must be loaded along with all its children subtables
	 * Useful when searching for a pattern in the DataTable Actions (we display the full hierarchy)
	 * 
	 * @var bool
	 */
	protected $recursiveDataTableLoad   = false;
	
	/**
	 * DataTable loaded from the API for this ViewDataTable.
	 *  
	 * @var Piwik_DataTable
	 */
	protected $dataTable = null; 
		
	/**
	 * @see init()
	 *
	 * @var string
	 */
	protected $currentControllerAction;
	
	/**
	 * @see init()
	 *
	 * @var string
	 */
	protected $currentControllerName;
	
	/**
	 * @see init()
	 *
	 * @var string
	 */
	protected $actionToLoadTheSubTable = null;
	
	/**
	 * @see init()
	 *
	 * @var string
	 */
	protected $moduleNameAndMethod;
	
	/**
	 * This view should be an implementation of the Interface Piwik_iView
	 * The $view object should be created in the main() method.
	 * 
	 * @var Piwik_iView
	 */
	protected $view = null;
	
	/**
	 * Method to be implemented by the ViewDataTable_*.
	 * This method should create and initialize a $this->view object @see Piwik_iView
	 * 
	 * @return mixed either prints the result or returns the output string
	 */
	abstract public function main();
	
	/**
	 * Returns a Piwik_ViewDataTable_* object.
	 * By default it will return a ViewDataTable_Html
	 * If there is a viewDataTable parameter in the URL, a ViewDataTable of this 'viewDataTable' type will be returned.
	 * If defaultType is specified and if there is no 'viewDataTable' in the URL, a ViewDataTable of this $defaultType will be returned.
	 * If force is set to true, a ViewDataTable of the $defaultType will be returned in all cases.
	 * 
	 * @param string defaultType Any of these: table, cloud, graphPie, graphVerticalBar, graphEvolution, sparkline, generateDataChart* 
	 * @force bool If set to true, returns a ViewDataTable of the $defaultType
	 * 
	 * @return Piwik_ViewDataTable 
	 */
	static public function factory( $defaultType = null, $force = false)
	{
		if(is_null($defaultType))
		{
			$defaultType = 'table';	
		}
		
		if($force === true)
		{
			$type = $defaultType;
		}
		else
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
				require_once "ViewDataTable/GenerateGraphHTML/ChartPie.php";
				return new Piwik_ViewDataTable_GenerateGraphHTML_ChartPie();
			break;			
			
			case 'graphVerticalBar':
				require_once "ViewDataTable/GenerateGraphHTML/ChartVerticalBar.php";
				return new Piwik_ViewDataTable_GenerateGraphHTML_ChartVerticalBar();
			break;	
			
			case 'graphEvolution':
				require_once "ViewDataTable/GenerateGraphHTML/ChartEvolution.php";
				return new Piwik_ViewDataTable_GenerateGraphHTML_ChartEvolution();
			break;	
			
			case 'sparkline':
				require_once "ViewDataTable/Sparkline.php";
				return new Piwik_ViewDataTable_Sparkline();
			break;	
			
			case 'generateDataChartVerticalBar':
				require_once "ViewDataTable/GenerateGraphData/ChartVerticalBar.php";
				return new Piwik_ViewDataTable_GenerateGraphData_ChartVerticalBar();
			break;
						
			case 'generateDataChartPie':
				require_once "ViewDataTable/GenerateGraphData/ChartPie.php";
				return new Piwik_ViewDataTable_GenerateGraphData_ChartPie();
			break;
			
			case 'generateDataChartEvolution':
				require_once "ViewDataTable/GenerateGraphData/ChartEvolution.php";
				return new Piwik_ViewDataTable_GenerateGraphData_ChartEvolution();
				
			break;
				
			case 'table':
			default:
				require_once "ViewDataTable/Html.php";
				return new Piwik_ViewDataTable_Html();
			break;
		}
	}
	
	/**
	 * Inits the object given the $currentControllerName, $currentControllerAction of 
	 * the calling controller action, eg. 'Referers' 'getLongListOfKeywords'.
	 * The initialization also requires the $moduleNameAndMethod of the API method 
	 * to call in order to get the DataTable, eg. 'Referers.getKeywords'.
	 * The optional $actionToLoadTheSubTable defines the method name of the API to call when there is a idSubtable.
	 * This value would be used by the javascript code building the GET request to the API.
	 * 
	 * Example: 
	 * 	For the keywords listing, a click on the row loads the subTable of the Search Engines for this row.
	 *  In this case $actionToLoadTheSubTable = 'getSearchEnginesFromKeywordId'.
	 *  The GET request will hit 'Referers.getSearchEnginesFromKeywordId'.
	 *
	 * @param string $currentControllerName eg. 'Referers'
	 * @param string $currentControllerAction eg. 'getKeywords'
	 * @param string $moduleNameAndMethod eg. 'Referers.getKeywords'
	 * @param string $actionToLoadTheSubTable eg. 'getSearchEnginesFromKeywordId'
	 * 
	 * @return void
	 */
	public function init( $currentControllerName,
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
	
	/**
	 * Forces the View to use a given template.
	 * Usually the template to use is set in the specific ViewDataTable_* 
	 * eg. 'Home/templates/cloud.tpl'
	 *
	 * But some users may want to force this template to some other value
	 * 
	 * @param string $tpl eg .'MyPlugin/templates/templateToUse.tpl'
	 */
	public function setTemplate( $tpl )
	{
		$this->dataTableTemplate = $tpl;
	}
		
	/**
	 * Returns the iView.
	 * You can then call render() on this object.
	 *
	 * @return Piwik_iView
	 * @throws exception if the view object was not created
	 */
	public function getView()
	{
		if(is_null($this->view))
		{
			throw new Exception('The $this->view object has not been created. 
					It should be created in the main() method of the Piwik_ViewDataTable_* subclass you are using.');
		}
		return $this->view;
	}

	/**
	 * Returns the DataTable loaded from the API
	 *
	 * @return Piwik_DataTable
	 * @throws exception if not yet defined
	 */
	public function getDataTable()
	{
		if(is_null($this->dataTable))
		{
			throw new Exception("The DataTable requested has not been loaded yet.");
		}
		return $this->dataTable;
	}
	/**
	 * Function called by the ViewDataTable objects in order to fetch data from the API.
	 * The function init() must have been called before, so that the object knows which API module and action to call.
	 * It builds the API request string and uses Piwik_API_Request to call the API.
	 * The requested Piwik_DataTable object is stored in $this->dataTable.
	 * 
	 * @return void
	 */
	protected function loadDataTableFromAPI()
	{		
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
		
		$toSetEventually = array(
			'filter_limit',
			'filter_sort_column',
			'filter_sort_order',
			'filter_excludelowpop',
			'filter_excludelowpop_value',
			'filter_column', 
			'filter_pattern',
			'disable_generic_filters',
			'disable_queued_filters',
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

		$this->dataTable = $dataTable;
	}
	
	
	/**
	 * For convenience, the client code can call methods that are defined in a specific children class
	 * without testing the children class type, which would trigger an error with a different children class.
	 * 
	 * Example:
	 *  ViewDataTable/Html.php defines a setColumnsToDisplay(). The client code calls this methods even if
	 *  the ViewDataTable object is a ViewDataTable_Cloud instance (he doesn't know because of the factory()). 
	 *  But ViewDataTable_Cloud doesn't define the setColumnsToDisplay() method. 
	 *  Because we don't want to force users to test for the object type we simply catch these
	 *  calls when they are not defined in the child and do nothing.  
	 *
	 * @param string $function
	 * @param array $args
	 */
	public function __call($function, $args)
	{
	}
	
	/**
	 * Returns a unique ID for this ViewDataTable.
	 * This unique ID is used in the Javascript code: 
	 *  Any ajax loaded data is loaded within a DIV that has id=$unique_id 
	 *  The jquery code then replaces the existing html div id=$unique_id in the code with this data.
	 * 
	 * @see datatable.js
	 * @return string
	 */
	protected function getUniqIdTable()
	{
		// if we request a subDataTable the $this->currentControllerAction DIV ID is already there in the page
		// we make the DIV ID really unique by appending the ID of the subtable requested
		if( $this->idSubtable != 0 // parent DIV has a idSubtable = 0 but the html DIV must have the name of the module.action
			&&  $this->idSubtable !== false // case there is no idSubtable 
			)
		{
			// see also datatable.js (the ID has to match with the html ID created to be replaced by the result of the ajax call)
			$uniqIdTable = 'subDataTable_' . $this->idSubtable;
		}
		else
		{
			// the $uniqIdTable variable is used as the DIV ID in the rendered HTML
			// we use the current Controller action name as it is supposed to be unique in the rendered page 
			$uniqIdTable = $this->currentControllerName . $this->currentControllerAction;
		}
		return $uniqIdTable;
	}
	
	/**
	 * This functions reads the customization values for the DataTable and returns an array (name,value) to be printed in Javascript.
	 * This array defines things such as:
	 * - name of the module & action to call to request data for this table
	 * - display the search box under the table
	 * - display the links Next & Previous under the table
	 * - optional filters information, eg. filter_limit and filter_offset
	 * - etc.
	 *
	 * The values are loaded:
	 * - from the generic filters that are applied by default @see Piwik_API_Request::getGenericFiltersInformation()
	 * - from the values already available in the GET array
	 * - from the values set using methods from this class (eg. setSearchPattern(), setLimit(), etc.)
	 * 
	 * @return array eg. array('show_offset_information' => 0, 'show_
	 */
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
		
		// we escape the values that will be displayed in the javascript footer of each datatable
		// to make sure there is malicious code injected (the value are already htmlspecialchar'ed as they
		// are loaded with Piwik_Common::getRequestVar()
		foreach($javascriptVariablesToSet as &$value)
		{
			$value = addslashes($value);
		}
		
		return $javascriptVariablesToSet;
	}
	
	/**
	 * Returns, for a given parameter, the value of this parameter in the REQUEST array.
	 * If not set, returns the default value for this parameter @see getDefault()
	 *
	 * @param string $nameVar
	 * @return string|mixed Value of this parameter
	 */
	protected function getDefaultOrCurrent( $nameVar )
	{
		if(isset($_REQUEST[$nameVar]))
		{
			return $_REQUEST[$nameVar];
		}
		$default = $this->getDefault($nameVar);
		return $default;
	}

	/**
	 * Returns the default value for a given parameter.
	 * For example, these default values can be set using the disable* methods.
	 * 
	 * @param string $nameVar
	 * @return mixed
	 */
	protected function getDefault($nameVar)
	{
		if(!isset($this->variablesDefault[$nameVar]))
		{
			return false;
		}
		return $this->variablesDefault[$nameVar];
	}
	
	/**
	 * The generic filters (limit, offset, sort by visit desc) will not be applied to this datatable.
	 * 
	 * @return void
	 *
	 */
	public function disableGenericFilters()
	{
		$this->variablesDefault['disable_generic_filters'] = true;
	}
	/**
	 * The "X-Y of Z" won't be displayed under this table
	 * 
	 * @return void
	 *
	 */
	public function disableOffsetInformation()
	{
		$this->JSoffsetInformation = 'false';		
	}
	
	/**
	 * @see disableOffsetInformation()
	 * 
	 * @return bool|string If this parameter is enabled or not
	 *
	 */
	protected function getOffsetInformation()
	{
		return $this->JSoffsetInformation;
	}
	
	/**
	 * The search box won't be displayed under this table
	 *
	 * @return void
	 */
	public function disableSearchBox()
	{
		$this->JSsearchBox = 'false';
	}
	
	/**
	 * @see disableSearchBox()
	 * 
	 * @return bool|string If this parameter is enabled or not
	 *
	 */
	protected function getSearchBox()
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
	
	/**
	 * Returns true if the footer should be included in the template 
	 * 
	 * @return bool
	 *
	 */
	protected function getShowFooter()
	{
		return $this->showFooter;
	}
	
	/**
	 * The "Include low population" link won't be displayed under this table
	 *
	 * @return void
	 */
	public function disableExcludeLowPopulation()
	{
		$this->JSexcludeLowPopulation = 'false';
	}
	
	/**
	 * @see disableExcludeLowPopulation()
	 * 
	 * @return bool|string If this parameter is enabled or not
	 *
	 */
	protected function getExcludeLowPopulation()
	{
		return $this->JSexcludeLowPopulation;
	}
	
	
	/**
	 * Sets the value to use for the Exclude low population filter.
	 * 
	 * @param int|float If a row value is less than this value, it will be removed from the dataTable
	 * @param string The name of the column for which we compare the value to $minValue
	 *
	 * @return void
	 */
	public function setExcludeLowPopulation( $minValue = null, $columnName = null )
	{
		if( is_null( $minValue) ) 
		{
			throw new Exception("setExcludeLowPopulation() value shouldn't be null");
		}
		
		if(is_null($columnName))
		{
			$columnName = Piwik_Archive::INDEX_NB_VISITS;
		}
		
		// column to use to enable low population exclusion if != false
		$this->variablesDefault['filter_excludelowpop_default'] 
			= $this->variablesDefault['filter_excludelowpop']
			= $columnName;
		
		// the minimum value a row must have to be returned 
		$this->variablesDefault['filter_excludelowpop_value_default'] 
			= $this->variablesDefault['filter_excludelowpop_value']
			= $minValue;	
	}
	
	/**
	 * Sets the pattern to look for in the table (only rows matching the pattern will be kept)
	 *
	 * @param string $pattern to look for
	 * @param string $column to compare the pattern to
	 * 
	 * @return void
	 */
	public function setSearchPattern($pattern, $column)
	{
		$this->variablesDefault['filter_pattern'] = $pattern;
		$this->variablesDefault['filter_column'] = $column;
	}

	/**
	 * Sets the maximum number of rows of the table
	 *
	 * @param int $limit
	 * 
	 * @return void
	 */
	public function setLimit( $limit )
	{
		if($limit != 0)
		{
			$this->variablesDefault['filter_limit'] = $limit;
		}
	}
	
	/**
	 * Sets the dataTable column to sort by. This sorting will be applied before applying the (offset, limit) filter. 
	 *
	 * @param int|string $columnId eg. 'nb_visits' for some tables, or Piwik_Archive::INDEX_NB_VISITS for others
	 * @param string $order desc or asc
	 * 
	 * @return void
	 */
	public function setSortedColumn( $columnId, $order = 'desc')
	{
		$this->variablesDefault['filter_sort_column']= $columnId;
		$this->variablesDefault['filter_sort_order']= $order;
	}
	
	
	/**
	 * Given a Piwik_DataTable_Array made of DataTable_Simple rows, returns a php array with the structure:
	 * array(
	 * 	array( label => X, value => Y),
	 * 	array( label => A, value => B),
	 * ...
	 * )
	 *
	 * This is used for example for the evolution graph (last 30 days visits) or the sparklines.
	 * 
	 * @param Piwik_DataTable_Array $dataTableArray
	 * @return array
	 */
	protected function generateDataFromDataTableArray( Piwik_DataTable_Array $dataTableArray)
	{
		$data = array();
		foreach($dataTableArray->getArray() as $keyName => $table)
		{
			if($table instanceof Piwik_DataTable_Array)
			{
				throw new Exception("Operation not supported (yet)");
			}
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
					// - apply this filter to keep only the column called nb_uniq_visitors
					// - rename this column as 'value'
					// and at this point the getcolumn('value') would have worked
					// this code is executed eg. when displaying a sparkline for the last 30 days displaying the number of unique visitors coming from search engines
					
					//TODO solution: use a filter rename column etc.
					
					// another solution would be to add a method to the Referers API giving directly the integer 'visits from search engines'
					// and we would build automatically the dataTable_array of datatatble_simple from these integers
					// but we'd have to add this integer to be recorded during archiving etc.
					$value = $onlyRow->getColumn('nb_uniq_visitors');
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
	
}