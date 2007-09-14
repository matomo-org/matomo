<?php
require_once "API/Request.php";
require_once "ViewDataTable.php";

class Piwik_UserSettings_Controller extends Piwik_Controller
{	
	function index()
	{
		$view = new Piwik_View('UserSettings/templates/index.tpl');

		$oDate = Piwik_Date::factory(Piwik_Common::getRequestVar('date'));
		$date = $oDate->toString();
		$view->date = $date;
		$view->period = Piwik_Common::getRequestVar('period');
		$view->idSite = Piwik_Common::getRequestVar('idSite');
		
		$view->userLogin = Piwik::getCurrentUserLogin();
		$view->sites = Piwik_SitesManager_API::getSitesWithAtLeastViewAccess();
		$view->url = Piwik_Url::getCurrentUrl();
		
		$site = new Piwik_Site($view->idSite);
		$minDate = $site->getCreationDate();
		
		$view->minDateYear = $minDate->toString('Y');
		$view->minDateMonth = $minDate->toString('m');
		$view->minDateDay = $minDate->toString('d');
		
		/* Actions / Downloads / Outlinks */
		$view->dataTableActions = $this->getActions( true );
		$view->dataTableDownloads = $this->getDownloads( true );
		$view->dataTableOutlinks = $this->getOutlinks( true );
		
		/* General visits */
		$dataTableVisit = $this->getVisitsSummary();
		$view->nbUniqVisitors = $dataTableVisit->getColumn('nb_uniq_visitors');
		$view->nbVisits = $dataTableVisit->getColumn('nb_visits');
		$view->nbActions = $dataTableVisit->getColumn('nb_actions');
		$view->sumVisitLength = $dataTableVisit->getColumn('sum_visit_length');
		$view->bounceCount = $dataTableVisit->getColumn('bounce_count');
		$view->maxActions = $dataTableVisit->getColumn('max_actions');

		
		/* User Country */
		$view->dataTableCountry = $this->getCountry(true);
		$view->dataTableContinent = $this->getContinent(true);
		
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
		$view->main();
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
	function getActionsView($currentMethod,
						$methodToCall = 'Actions.getActions', 
						$subMethod = 'getActionsSubDataTable')
	{
		$view = Piwik_ViewDataTable::factory();
		$view->init(  	$currentMethod, 
						$methodToCall, 
						$subMethod );
		$view->setTemplate('UserSettings/templates/datatable_actions.tpl');
		
		if(Piwik_Common::getRequestVar('idSubtable', -1) != -1)
		{
			$view->setTemplate('UserSettings/templates/datatable_actions_subdatable.tpl');
		}
		$view->setSearchRecursive();
		
		$currentlySearching = $view->setRecursiveLoadDataTableIfSearchingForPattern();
		if($currentlySearching)
		{
			$view->setTemplate('UserSettings/templates/datatable_actions_recursive.tpl');
		}
		$view->disableSort();
		$view->disableOffsetInformation();
		
		$view->setColumnsToDisplay( array(0,1) );
		$view->setDefaultLimit( 100 );
		$view->setExcludeLowPopulation( 5 );
		
		$view->main();
		// we need to rewrite the phpArray so it contains all the recursive arrays
		if($currentlySearching)
		{
			$phpArrayRecursive = $this->getArrayFromRecursiveDataTable($view->dataTable);
//			var_dump($phpArrayRecursive);exit;
			$view->view->arrayDataTable = $phpArrayRecursive;
		}
		
		return $view;
	}
	
	protected function getArrayFromRecursiveDataTable( $dataTable, $depth = 0 )
	{
		$table = array();
		foreach($dataTable->getRows() as $row)
		{
			$phpArray = array();
			if(($idSubtable = $row->getIdSubDataTable()) !== null)
			{
				$subTable = Piwik_DataTable_Manager::getInstance()->getTable( $idSubtable );
					
				if($subTable->getRowsCount() > 0)
				{
					$filter = new Piwik_DataTable_Filter_ReplaceColumnNames(
									$subTable,
									Piwik_Actions::getColumnsMap()
								);				
					$phpArray = $this->getArrayFromRecursiveDataTable( $subTable, $depth + 1 );
				}
			}
			
			$label = $row->getColumn('label');
			$newRow = array(
				'level' => $depth,
				'columns' => $row->getColumns(),
				'details' => $row->getDetails(),
				'idsubdatatable' => $row->getIdSubDataTable()
				);
			$table[] = $newRow;
			if(count($phpArray) > 0)
			{
				$table = array_merge( $table,  $phpArray);
			}
		}
		return $table;
	}
	function getDownloads($fetch = false)
	{
		$view = $this->getActionsView( 	__FUNCTION__,
										'Actions.getDownloads', 
										'getDownloadsSubDataTable' );
		
		return $this->renderView($view, $fetch);
	}
	function getDownloadsSubDataTable($fetch = false)
	{
		$view = $this->getActionsView( 	__FUNCTION__,
										'Actions.getDownloads', 
										'getDownloadsSubDataTable' );
		
		return $this->renderView($view, $fetch);
	}
	function getActions($fetch = false)
	{
		$view = $this->getActionsView(	__FUNCTION__,
										'Actions.getActions', 
										'getActionsSubDataTable' );
		
		return $this->renderView($view, $fetch);
	}
	function getActionsSubDataTable($fetch = false)
	{
		$view = $this->getActionsView( 	__FUNCTION__,
										'Actions.getActions', 
										'getActionsSubDataTable'  );
		
		return $this->renderView($view, $fetch);
	}
	function getOutlinks($fetch = false)
	{
		$view = $this->getActionsView(	__FUNCTION__,
										'Actions.getOutlinks', 
										'getOutlinksSubDataTable' );
		
		return $this->renderView($view, $fetch);
	}
	function getOutlinksSubDataTable($fetch = false)
	{
		$view = $this->getActionsView( 	__FUNCTION__,
										'Actions.getOutlinks', 
										'getOutlinksSubDataTable'  );
		
		return $this->renderView($view, $fetch);
	}
	
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
		$view = Piwik_ViewDataTable::factory();
		$view->init(  __FUNCTION__, 
								"VisitTime.getVisitInformationPerServerTime" );
		
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
		$view = Piwik_ViewDataTable::factory();
		$view->init(  __FUNCTION__, 
								"VisitTime.getVisitInformationPerLocalTime" );
		
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
		$view = Piwik_ViewDataTable::factory();
		$view->init(  __FUNCTION__, 
									"VisitorInterest.getNumberOfVisitsPerVisitDuration" );
		
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
		$view = Piwik_ViewDataTable::factory();
		$view->init(  __FUNCTION__, 
									"VisitorInterest.getNumberOfVisitsPerPage" );
		
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
		$view = Piwik_ViewDataTable::factory();
		$view->init(  __FUNCTION__, "Provider.getProvider" );
		
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
		$view = Piwik_ViewDataTable::factory();
		$view->init( __FUNCTION__, "UserCountry.getCountry" );
		$view->disableExcludeLowPopulation();
		
		$view->setColumnsToDisplay( array(0,1) );
		$view->setSortedColumn( 1 );
		$view->disableSearchBox();
		
		// sorting by label is not correct as the labels are the ISO codes before being
		// mapped to the country names
//		$view->disableSort();
		$view->setDefaultLimit( 5 );
		
		return $this->renderView($view, $fetch);
	}

	function getContinent( $fetch = false)
	{
		$view = Piwik_ViewDataTable::factory();
		$view->init( __FUNCTION__, "UserCountry.getContinent" );
		$view->disableExcludeLowPopulation();
		$view->disableSearchBox();
		$view->disableOffsetInformation();
		$view->disableSort();
		//TODO disable sort label column only
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
		$view = Piwik_ViewDataTable::factory();
		$view->init(  $currentControllerAction, $APItoCall );
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
		$view = Piwik_ViewDataTable::factory();
		$view->init(  __FUNCTION__, 'UserSettings.getPlugin' );
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
		$view = Piwik_ViewDataTable::factory();
		$view->init(  	'getRefererType', 
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
		$view = Piwik_ViewDataTable::factory();
		$view->init( 	'getKeywords', 
											'Referers.getKeywords', 
											'getSearchEnginesFromKeywordId'
								);
		$view->disableExcludeLowPopulation();
		$view->setColumnsToDisplay( array(0,2));

		return $this->renderView($view, $fetch);
	}
	
	function getSearchEnginesFromKeywordId( $fetch = false )
	{
		$view = Piwik_ViewDataTable::factory();
		$view->init( 	'getSearchEnginesFromKeywordId', 
											'Referers.getSearchEnginesFromKeywordId'
								);
		$view->disableSearchBox();
		$view->disableExcludeLowPopulation();
		$view->setColumnsToDisplay( array(0,2));

		return $this->renderView($view, $fetch);
	}
	
	
	function getSearchEngines( $fetch = false)
	{
		$view = Piwik_ViewDataTable::factory();
		$view->init(  	'getSearchEngines', 
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
		$view = Piwik_ViewDataTable::factory();
		$view->init( 	'getKeywordsFromSearchEngineId', 
											'Referers.getKeywordsFromSearchEngineId'
								);
		$view->disableSearchBox();
		$view->disableExcludeLowPopulation();
		$view->setColumnsToDisplay( array(0,2));

		return $this->renderView($view, $fetch);
	}
	
	function getWebsites( $fetch = false)
	{
		$view = Piwik_ViewDataTable::factory();
		$view->init(  	'getWebsites', 
											'Referers.getWebsites',
											'getUrlsFromWebsiteId'
								);
		$view->disableExcludeLowPopulation();
		
		$view->setColumnsToDisplay( array(0,2) );
		
		return $this->renderView($view, $fetch);
	}
	
	function getCampaigns( $fetch = false)
	{
		$view = Piwik_ViewDataTable::factory();
		$view->init(  	'getCampaigns', 
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
		$view = Piwik_ViewDataTable::factory();
		$view->init( 	'getKeywordsFromCampaignId', 
											'Referers.getKeywordsFromCampaignId'
								);

		$view->disableSearchBox();
		$view->disableExcludeLowPopulation();
		$view->setColumnsToDisplay( array(0,2));

		return $this->renderView($view, $fetch);
	}
	
	function getUrlsFromWebsiteId( $fetch = false)
	{
		$view = Piwik_ViewDataTable::factory();
		$view->init( 	'getUrlsFromWebsiteId', 
											'Referers.getUrlsFromWebsiteId'
								);
		$view->disableSearchBox();
		$view->disableExcludeLowPopulation();
		$view->setColumnsToDisplay( array(0,2));

		return $this->renderView($view, $fetch);
	}
	
	function getPartners( $fetch = false)
	{
		$view = Piwik_ViewDataTable::factory();
		$view->init(  	'getPartners', 
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
		$view = Piwik_ViewDataTable::factory();
		$view->init( 	'getUrlsFromPartnerId', 
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
	
}

