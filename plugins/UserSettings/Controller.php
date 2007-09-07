<?php
require_once "API/Request.php";
class Piwik_UserSettings_Controller extends Piwik_Controller
{	
	function index()
	{
		$view = new Piwik_View('UserSettings/templates/index.tpl');
		/* To put in a Piwik_View_Report */
		
		$view->date = Piwik_Common::getRequestVar('date');
		$view->period = Piwik_Common::getRequestVar('period');
		$view->idSite = Piwik_Common::getRequestVar('idSite');
		
		
		/* General visits */
		$dataTableVisit = $this->getVisitsSummary();
		$view->nbUniqVisitors = $dataTableVisit->getColumn('nb_uniq_visitors');
		$view->nbVisits = $dataTableVisit->getColumn('nb_visits');
		$view->nbActions = $dataTableVisit->getColumn('nb_actions');
		$view->sumVisitLength = $dataTableVisit->getColumn('sum_visit_length');
		$view->bounceCount = $dataTableVisit->getColumn('bounce_count');
		$view->maxActions = $dataTableVisit->getColumn('max_actions');
		
		/* User settings */		
		$view->dataTablePlugin = $this->getPlugin( true );
		$view->dataTableResolution = $this->getResolution( true );
		$view->dataTableConfiguration = $this->getConfiguration( true );
		$view->dataTableOS = $this->getOS( true );
		$view->dataTableBrowser = $this->getBrowser( true );
		$view->dataTableBrowserType = $this->getBrowserType ( true );
		$view->dataTableWideScreen = $this->getWideScreen( true );
		
		/* VisitorTime */
		$view->dataTableVisitInformationPerLocalTime = $this->getVisitInformationPerLocalTime(true);
		$view->dataTableVisitInformationPerServerTime = $this->getVisitInformationPerServerTime(true);
		
		/* VisitFrequency */
		$dataTableFrequency = $this->getSummary(true);
		
		$view->nbVisitsReturning = $dataTableFrequency->getColumn('nb_visits_returning');
		$view->nbActionsReturning = $dataTableFrequency->getColumn('nb_actions_returning');
		$view->maxActionsReturning = $dataTableFrequency->getColumn('max_actions_returning');
		$view->sumVisitLengthReturning = $dataTableFrequency->getColumn('sum_visit_length_returning');
		$view->bounceCountReturning = $dataTableFrequency->getColumn('bounce_count_returning');
		
		/* Visitor Interest */
		$view->dataTableNumberOfVisitsPerVisitDuration = $this->getNumberOfVisitsPerVisitDuration(true);
		$view->dataTableNumberOfVisitsPerPage = $this->getNumberOfVisitsPerPage(true);
		
		/* Provider */
		$view->dataTableProvider = $this->getProvider(true);
		
		/* User Country */
		$view->dataTableCountry = $this->getCountry(true);
		$view->dataTableContinent = $this->getContinent(true);
		
		/* Referers */
		$view->dataTableRefererType = $this->getRefererType(true);
		$view->dataTableKeywords = $this->getKeywords(true);
		$view->dataTableSearchEngines = $this->getSearchEngines(true);
		$view->dataTableCampaigns = $this->getCampaigns(true);
		$view->dataTableWebsites = $this->getWebsites(true);
		$view->dataTablePartners = $this->getPartners(true);
		
		$view->numberDistinctSearchEngines = $this->getNumberOfDistinctSearchEngines(true);
		$view->numberDistinctKeywords = $this->getNumberOfDistinctKeywords(true);
		$view->numberDistinctCampaigns = $this->getNumberOfDistinctCampaigns(true);
		$view->numberDistinctWebsites = $this->getNumberOfDistinctWebsites(true);
		$view->numberDistinctWebsitesUrls = $this->getNumberOfDistinctWebsitesUrls(true);
		$view->numberDistinctPartners = $this->getNumberOfDistinctPartners(true);
		$view->numberDistinctPartnersUrls = $this->getNumberOfDistinctPartnersUrls(true);

		
		echo $view->render();		
	}
		
	protected function renderView($view, $fetch)
	{
		$rendered = $view->getView()->render();
		if($fetch)
		{
			return $rendered;
		}
		echo $rendered;
	}
	protected function getNumericValue( $methodToCall )
	{
		$requestString = 'method='.$methodToCall.'&format=original';
		$request = new Piwik_API_Request($requestString);
		return $request->process();
	}
		/*
		 * 

List of the public methods for the class Piwik_Actions_API
- getActions : [idSite, period, date, expanded = , idSubtable = ]
- getDownloads : [idSite, period, date, expanded = , idSubtable = ]
- getOutlinks : [idSite, period, date, expanded = , idSubtable = ]

		 */
	/**
	 * General visit
	 */
	function getVisitsSummary()
	{
		$requestString = 'method='."VisitsSummary.get".'&format=original';
		$request = new Piwik_API_Request($requestString);
		return $request->process();
	}
	/**
	 * VisitFrequency
	 */
	function getSummary( )
	{		
		$requestString = 'method='."VisitFrequency.getSummary".'&format=original';
		$request = new Piwik_API_Request($requestString);
		return $request->process();
	}
	
	/**
	 * VisitTime
	 */
	function getVisitInformationPerServerTime( $fetch = false)
	{
		$view = new Piwik_View_DataTable( __FUNCTION__, "VisitTime.getVisitInformationPerServerTime" );
		
		$view->setColumnsToDisplay( array(0,2) );
		$view->setSortedColumn( '0', 'asc' );
		$view->setDefaultLimit( 24 );
		$view->disableSearchBox();
		$view->disableExcludeLowPopulation();
		$view->disableOffsetInformation();
		
		return $this->renderView($view, $fetch);
	}
	
	function getVisitInformationPerLocalTime( $fetch = false)
	{
		$view = new Piwik_View_DataTable( __FUNCTION__, "VisitTime.getVisitInformationPerLocalTime" );
		
		$view->setColumnsToDisplay( array(0,2) );
		$view->setSortedColumn( '0', 'asc' );
		$view->setDefaultLimit( 24 );
		$view->disableSearchBox();
		$view->disableExcludeLowPopulation();
		$view->disableOffsetInformation();
		
		return $this->renderView($view, $fetch);
	}
	/**
	 * VisitorInterest
	 */
	function getNumberOfVisitsPerVisitDuration( $fetch = false)
	{
		$view = new Piwik_View_DataTable( __FUNCTION__, "VisitorInterest.getNumberOfVisitsPerVisitDuration" );
		
		$view->setColumnsToDisplay( array(0,1) );
		$view->setDefaultLimit( 5 );
		
		$view->disableSort();
		$view->disableExcludeLowPopulation();
		$view->disableOffsetInformation();
		$view->disableSearchBox();
		
		return $this->renderView($view, $fetch);
	}
	
	function getNumberOfVisitsPerPage( $fetch = false)
	{
		$view = new Piwik_View_DataTable( __FUNCTION__, "VisitorInterest.getNumberOfVisitsPerPage" );
		
		$view->setColumnsToDisplay( array(0,1) );
		$view->setSortedColumn( 'nb_visits' );
		$view->disableExcludeLowPopulation();
		$view->disableOffsetInformation();
		$view->disableSearchBox();
		$view->disableSort();
		$view->main();
//		echo $view->dataTable;
		return $this->renderView($view, $fetch);
	}
	
	/**
	 * Provider
	 */
	function getProvider( $fetch = false)
	{
		$view = new Piwik_View_DataTable( __FUNCTION__, "Provider.getProvider" );
		
		$view->setColumnsToDisplay( array(0,1) );
		$view->setSortedColumn( 1 );
		$view->setDefaultLimit( 5 );
		
		return $this->renderView($view, $fetch);
	}
	
	/**
	 * User Country
	 */
	function getCountry( $fetch = false)
	{
		$view = new Piwik_View_DataTable( __FUNCTION__, "UserCountry.getCountry" );
		$view->disableExcludeLowPopulation();
		
		$view->setColumnsToDisplay( array(0,1) );
		$view->setSortedColumn( 1 );
		$view->disableSearchBox();
		$view->setDefaultLimit( 5 );
		
		return $this->renderView($view, $fetch);
	}

	function getContinent( $fetch = false)
	{
		$view = new Piwik_View_DataTable( __FUNCTION__, "UserCountry.getContinent" );
		$view->disableExcludeLowPopulation();
		$view->disableSearchBox();
		$view->disableOffsetInformation();
		$view->setColumnsToDisplay( array(0,1) );
		$view->setSortedColumn( 1 );
		
		return $this->renderView($view, $fetch);
	}

	/**
	 * User settings
	 */
	function getStandardDataTableUserSettings( $currentControllerAction, 
												$APItoCall )
	{
		$view = new Piwik_View_DataTable( $currentControllerAction, $APItoCall );
		$view->disableSearchBox();
		$view->disableExcludeLowPopulation();
		
		$view->setColumnsToDisplay( array(0,1) );
		$view->setSortedColumn( 1 );
		$view->setDefaultLimit( 5 );
		
		return $view;
	}
	
	function getResolution( $fetch = false)
	{
		$view = $this->getStandardDataTableUserSettings(
										__FUNCTION__, 
										'UserSettings.getResolution'
									);
		return $this->renderView($view, $fetch);
	}
	
	function getConfiguration( $fetch = false)
	{
		$view =  $this->getStandardDataTableUserSettings(
										__FUNCTION__, 
										'UserSettings.getConfiguration'
									);
		$view->setDefaultLimit( 3 );
		return $this->renderView($view, $fetch);
	}
	
	function getOS( $fetch = false)
	{
		$view =  $this->getStandardDataTableUserSettings(
										__FUNCTION__, 
										'UserSettings.getOS'
									);
		return $this->renderView($view, $fetch);
	}
	
	function getBrowser( $fetch = false)
	{
		$view =  $this->getStandardDataTableUserSettings(
										__FUNCTION__, 
										'UserSettings.getBrowser'
									);
		return $this->renderView($view, $fetch);
	}
	
	function getBrowserType ( $fetch = false)
	{
		$view =  $this->getStandardDataTableUserSettings(
										__FUNCTION__, 
										'UserSettings.getBrowserType'
									);
		$view->disableOffsetInformation();
		return $this->renderView($view, $fetch);
	}
	
	function getWideScreen( $fetch = false)
	{
		$view =  $this->getStandardDataTableUserSettings(
										__FUNCTION__, 
										'UserSettings.getWideScreen'
									);
		$view->disableOffsetInformation();
		return $this->renderView($view, $fetch);
	}
	
	function getPlugin( $fetch = false)
	{
		$view = new Piwik_View_DataTable( __FUNCTION__, 'UserSettings.getPlugin' );
		$view->disableSearchBox();
		$view->disableExcludeLowPopulation();
		$view->disableSort();
		$view->disableOffsetInformation();
		
		$view->setColumnsToDisplay( array(0,1) );
		$view->setSortedColumn( 2 );
		$view->setDefaultLimit( 10 );
		
		return $this->renderView($view, $fetch);
	}



	/**
	 * Referers
	 */
	function getRefererType( $fetch = false)
	{
		$view = new Piwik_View_DataTable( 	'getRefererType', 
											'Referers.getRefererType'
								);
		$view->disableSearchBox();
		$view->disableOffsetInformation();
		$view->disableExcludeLowPopulation();
		
		$view->setColumnsToDisplay( array(0,1,2) );
		
		return $this->renderView($view, $fetch);
	}
	
	function getKeywords( $fetch = false)
	{
		$view = new Piwik_View_DataTable(	'getKeywords', 
											'Referers.getKeywords', 
											'getSearchEnginesFromKeywordId'
								);
		$view->disableExcludeLowPopulation();
		$view->setColumnsToDisplay( array(0,2));

		return $this->renderView($view, $fetch);
	}
	
	function getSearchEnginesFromKeywordId( $fetch = false )
	{
		$view = new Piwik_View_DataTable(	'getSearchEnginesFromKeywordId', 
											'Referers.getSearchEnginesFromKeywordId'
								);
		$view->disableSearchBox();
		$view->disableExcludeLowPopulation();
		$view->setColumnsToDisplay( array(0,2));

		return $this->renderView($view, $fetch);
	}
	
	
	function getSearchEngines( $fetch = false)
	{
		$view = new Piwik_View_DataTable( 	'getSearchEngines', 
											'Referers.getSearchEngines', 
											'getKeywordsFromSearchEngineId'
								);
		$view->disableSearchBox();
		$view->disableExcludeLowPopulation();
		
		$view->setColumnsToDisplay( array(0,2) );
		
		return $this->renderView($view, $fetch);
	}
	
	
	function getKeywordsFromSearchEngineId( $fetch = false )
	{
		$view = new Piwik_View_DataTable(	'getKeywordsFromSearchEngineId', 
											'Referers.getKeywordsFromSearchEngineId'
								);
		$view->disableSearchBox();
		$view->disableExcludeLowPopulation();
		$view->setColumnsToDisplay( array(0,2));

		return $this->renderView($view, $fetch);
	}
	
	function getWebsites( $fetch = false)
	{
		$view = new Piwik_View_DataTable( 	'getWebsites', 
											'Referers.getWebsites',
											'getUrlsFromWebsiteId'
								);
		$view->disableExcludeLowPopulation();
		
		$view->setColumnsToDisplay( array(0,2) );
		
		return $this->renderView($view, $fetch);
	}
	
	function getCampaigns( $fetch = false)
	{
		$view = new Piwik_View_DataTable( 	'getCampaigns', 
											'Referers.getCampaigns',
											'getKeywordsFromCampaignId'
								);

		$view->disableSearchBox();
		$view->disableExcludeLowPopulation();
		$view->setDefaultLimit( 5 );
		
		$view->setColumnsToDisplay( array(0,2) );
		
		return $this->renderView($view, $fetch);
	}
	
	function getKeywordsFromCampaignId( $fetch = false)
	{
		$view = new Piwik_View_DataTable(	'getKeywordsFromCampaignId', 
											'Referers.getKeywordsFromCampaignId'
								);

		$view->disableSearchBox();
		$view->disableExcludeLowPopulation();
		$view->setColumnsToDisplay( array(0,2));

		return $this->renderView($view, $fetch);
	}
	
	function getUrlsFromWebsiteId( $fetch = false)
	{
		$view = new Piwik_View_DataTable(	'getUrlsFromWebsiteId', 
											'Referers.getUrlsFromWebsiteId'
								);
		$view->disableSearchBox();
		$view->disableExcludeLowPopulation();
		$view->setColumnsToDisplay( array(0,2));

		return $this->renderView($view, $fetch);
	}
	
	function getPartners( $fetch = false)
	{
		$view = new Piwik_View_DataTable( 	'getPartners', 
											'Referers.getPartners',
											'getUrlsFromPartnerId'
								);
		$view->disableSearchBox();
		$view->disableExcludeLowPopulation();
		$view->setDefaultLimit( 5 );
		
		$view->setColumnsToDisplay( array(0,2) );
		
		return $this->renderView($view, $fetch);
	}
	
	function getUrlsFromPartnerId( $fetch = false)
	{
		$view = new Piwik_View_DataTable(	'getUrlsFromPartnerId', 
											'Referers.getUrlsFromPartnerId'
								);
		$view->disableSearchBox();
		$view->disableExcludeLowPopulation();
		$view->setColumnsToDisplay( array(0,2));

		return $this->renderView($view, $fetch);
	}
	
	
	function getNumberOfDistinctSearchEngines( $fetch = false)
	{
		return $this->getNumericValue('Referers.' . __FUNCTION__);
	}
	function getNumberOfDistinctKeywords( $fetch = false)
	{
		return $this->getNumericValue('Referers.' . __FUNCTION__);
	}
	function getNumberOfDistinctCampaigns( $fetch = false)
	{
		return $this->getNumericValue('Referers.' . __FUNCTION__);
	}
	function getNumberOfDistinctWebsites( $fetch = false)
	{
		return $this->getNumericValue('Referers.' . __FUNCTION__);
	}
	function getNumberOfDistinctWebsitesUrls( $fetch = false)
	{
		return $this->getNumericValue('Referers.' . __FUNCTION__);
	}
	function getNumberOfDistinctPartners( $fetch = false)
	{
		return $this->getNumericValue('Referers.' . __FUNCTION__);
	}
	function getNumberOfDistinctPartnersUrls ( $fetch = false)
	{
		return $this->getNumericValue('Referers.' . __FUNCTION__);
	}
	
	
	
	
	
	/*
	
	
	function getResolution( $fetch = false)
	{
		$view = $this->getTable(	'getResolution', 
									'UserSettings.getResolution'
								);
		//TODO setup a method for this
		$view->dataTableColumns = array(
					array('id' => 0, 'name' => 'label'),
					array('id' => Piwik_Archive::INDEX_NB_VISITS, 'name' => 'nb_visits'),
				);
		return $this->renderView($view, $fetch);
	}
	
	function getBrowser( $fetch = false)
	{
		$view = $this->getTable(	'getBrowser', 
									'UserSettings.getBrowser'
								);
		return $this->renderView($view, $fetch);
	}
	*/
}
class Piwik_View_DataTable
{
	protected $dataTableTemplate = 'UserSettings/templates/datatable.tpl';
	
	protected $currentControllerAction;
	protected $moduleNameAndMethod;
	protected $actionToLoadTheSubTable;
	
	protected $JSsearchBox 				= true;
	protected $JSoffsetInformation 		= true;
	protected $JSexcludeLowPopulation 	= true;
	protected $JSsortEnabled 			= true;
	
	protected $mainAlreadyExecuted = false;
	protected $columnsToDisplay = array();
	
	function __construct( $currentControllerAction, 
						$moduleNameAndMethod, 
						$actionToLoadTheSubTable = null)
	{
		$this->currentControllerAction = $currentControllerAction;
		$this->moduleNameAndMethod = $moduleNameAndMethod;
		$this->actionToLoadTheSubTable = $actionToLoadTheSubTable;
		
		$this->idSubtable = Piwik_Common::getRequestVar('idSubtable', false,'int');
	}
	
	function getView()
	{
		$this->main();
		return $this->view;
	}
	
	public function main()
	{
		if($this->mainAlreadyExecuted)
		{
			return;
		}
		$this->mainAlreadyExecuted = true;
		
//		$i=0;while($i<1500000){ $j=$i*$i;$i++;}
		
		// is there a Sub DataTable requested ? 
		// for example do we request the details for the search engine Google?
		
		
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
		
		$view->dataTable 	= $phpArray;
		
		$view->dataTableColumns = $this->getColumnsToDisplay($phpArray);
		
		
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
		
		$javascriptVariablesToSet['action'] = $this->currentControllerAction;
		
		if(!is_null($this->actionToLoadTheSubTable))
		{
			$javascriptVariablesToSet['actionToLoadTheSubTable'] = $this->actionToLoadTheSubTable;
		}
		
		$javascriptVariablesToSet['totalRows'] = $this->dataTable->getRowsCountBeforeLimitFilter();
		
		$javascriptVariablesToSet['show_search'] = $this->getSearchBox();
		$javascriptVariablesToSet['show_offset_information'] = $this->getOffsetInformation();
		$javascriptVariablesToSet['show_exclude_low_population'] = $this->getExcludeLowPopulation();
		$javascriptVariablesToSet['enable_sort'] = $this->getSort();
		
		return $javascriptVariablesToSet;
	}
	
	protected function loadDataTableFromAPI()
	{
		
		// we prepare the string to give to the API Request
		// we setup the method and format variable
		// - we request the method to call to get this specific DataTable
		// - the format = original specifies that we want to get the original DataTable structure itself, not rendered
		$requestString = 'method='.$this->moduleNameAndMethod.'&format=original';
		
		// if a subDataTable is requested we add the variable to the API request string
		if( $this->idSubtable != false)
		{
			$requestString .= '&this->idSubtable='.$this->idSubtable;
		}
		
		$toSetEventually = array(
			'filter_limit',
			'filter_sort_column',
			'filter_sort_order',
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

	protected function getPHPArrayFromDataTable( )
	{
		$renderer = Piwik_DataTable_Renderer::factory('php');
		$renderer->setTable($this->dataTable);
		$renderer->setSerialize( false );
		$phpArray = $renderer->render();
		return $phpArray;
	}
}

