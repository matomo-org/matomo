<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @package Piwik_Home
 * 
 */


require_once "API/Request.php";
require_once "ViewDataTable.php";

/**
 * 
 * @package Piwik_Home
 */
class Piwik_Home_Controller extends Piwik_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->currentControllerName = 'Home';

		$this->strDate = Piwik_Common::getRequestVar('date', 'yesterday','string');
		
		// the date looks like YYYY-MM-DD we can build it
		try{
			$this->date = Piwik_Date::factory($this->strDate);
			$this->strDate = $this->date->toString();
		} catch(Exception $e){
		// the date looks like YYYY-MM-DD,YYYY-MM-DD or other format
			// case the date looks like a range
			$this->date = null;
		}
	}
	function getDefaultAction()
	{
		return 'redirectToIndex';
	}
	
	function redirectToIndex()
	{
		header("Location:?module=Home&action=index&idSite=1&period=day&date=yesterday");
	}
	function homepage()
	{		
		$view = new Piwik_View('Home/templates/homepage.tpl');
		$view->link = '?module=Home&action=index&idSite=1&period=day&date=yesterday';
		echo $view->render();
	}
	
	
	public function index()
	{
		$view = new Piwik_View('Home/templates/index.tpl');
		
		$view->date = $this->strDate;
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
		$view->graphEvolutionVisitsSummary = $this->getLastVisitsGraph( true );
		
		$view->urlSparklineNbVisits 		= $this->getUrlSparkline( 'getLastVisitsGraph');
		$view->urlSparklineNbUniqVisitors 	= $this->getUrlSparkline( 'getLastUniqueVisitorsGraph');
		$view->urlSparklineNbActions 		= $this->getUrlSparkline( 'getLastActionsGraph');
		$view->urlSparklineSumVisitLength 	= $this->getUrlSparkline( 'getLastSumVisitsLengthGraph');
		$view->urlSparklineMaxActions 		= $this->getUrlSparkline( 'getLastMaxActionsGraph');
		$view->urlSparklineBounceCount 		= $this->getUrlSparkline( 'getLastBounceCountGraph');
		
		$dataTableVisit = $this->getVisitsSummary();
		$view->nbUniqVisitors = $dataTableVisit->getColumn('nb_uniq_visitors');
		$view->nbVisits = $dataTableVisit->getColumn('nb_visits');
		$view->nbActions = $dataTableVisit->getColumn('nb_actions');
		$view->sumVisitLength = $dataTableVisit->getColumn('sum_visit_length');
		$view->bounceCount = $dataTableVisit->getColumn('bounce_count');
		$view->maxActions = $dataTableVisit->getColumn('max_actions');

		
		/* User Country */
		$view->urlSparklineCountries = $this->getUrlSparkline('getLastDistinctCountriesGraph');
		$view->numberDistinctCountries = $this->getNumberOfDistinctCountries(true);
		
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
//		$view->graphEvolutionVisitFrequency = $this->getLastVisitsGraph( true );
		
		$view->urlSparklineNbVisitsReturning 		= $this->getUrlSparkline( 'getLastVisitsReturningGraph');
		$view->urlSparklineNbActionsReturning 		= $this->getUrlSparkline( 'getLastActionsReturningGraph');
		$view->urlSparklineSumVisitLengthReturning 	= $this->getUrlSparkline( 'getLastSumVisitsLengthReturningGraph');
		$view->urlSparklineMaxActionsReturning 		= $this->getUrlSparkline( 'getLastMaxActionsReturningGraph');
		$view->urlSparklineBounceCountReturning 	= $this->getUrlSparkline( 'getLastBounceCountReturningGraph');
		
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
		$view->graphEvolutionReferers = $this->getLastDistinctKeywordsGraph(true);
			
		$view->dataTableKeywords = $this->getKeywords(true);
		$view->dataTableSearchEngines = $this->getSearchEngines(true);
		$view->dataTableWebsites = $this->getWebsites(true);
		$view->dataTablePartners = $this->getPartners(true);
		$view->dataTableCampaigns = $this->getCampaigns(true);
		
		$view->numberDistinctSearchEngines 	= $this->getNumberOfDistinctSearchEngines(true);
		$view->numberDistinctKeywords 		= $this->getNumberOfDistinctKeywords(true);
		$view->numberDistinctWebsites 		= $this->getNumberOfDistinctWebsites(true);
		$view->numberDistinctWebsitesUrls 	= $this->getNumberOfDistinctWebsitesUrls(true);
		$view->numberDistinctPartners 		= $this->getNumberOfDistinctPartners(true);
		$view->numberDistinctPartnersUrls 	= $this->getNumberOfDistinctPartnersUrls(true);
		$view->numberDistinctCampaigns 		= $this->getNumberOfDistinctCampaigns(true);
		
		// building the referers summary report 
		$view->dataTableRefererType = $this->getRefererType(true);
		
		// this is raw data (no filters applied, on purpose) so we select the data using the magic integers ID 
		$dataTableReferersType = $this->getReferersType(true);
//		echo $dataTableReferersType;exit;
		$view->visitorsFromSearchEngines	= $dataTableReferersType->getRowFromLabel(Piwik_Common::REFERER_TYPE_SEARCH_ENGINE)->getColumn(Piwik_Archive::INDEX_NB_UNIQ_VISITORS);
		$view->visitorsFromDirectEntry 		= $dataTableReferersType->getRowFromLabel(Piwik_Common::REFERER_TYPE_DIRECT_ENTRY)->getColumn(Piwik_Archive::INDEX_NB_UNIQ_VISITORS);
		$view->visitorsFromWebsites 		= $dataTableReferersType->getRowFromLabel(Piwik_Common::REFERER_TYPE_WEBSITE)->getColumn(Piwik_Archive::INDEX_NB_UNIQ_VISITORS);
		$view->visitorsFromCampaigns 		= (false !== ($row = $dataTableReferersType->getRowFromLabel(Piwik_Common::REFERER_TYPE_CAMPAIGN))) ? $row->getColumn(Piwik_Archive::INDEX_NB_UNIQ_VISITORS) : 0;
		$view->visitorsFromNewsletters		= $dataTableReferersType->getRowFromLabel(Piwik_Common::REFERER_TYPE_NEWSLETTER)->getColumn(Piwik_Archive::INDEX_NB_UNIQ_VISITORS);
		$view->visitorsFromPartners 		= $dataTableReferersType->getRowFromLabel(Piwik_Common::REFERER_TYPE_PARTNER)->getColumn(Piwik_Archive::INDEX_NB_UNIQ_VISITORS);
		
		// sparkline for the historical data of the above values
		$view->urlSparklineSearchEngines	= $this->getUrlSparkline('getLastSearchEnginesGraph');
		$view->urlSparklineDirectEntry 		= $this->getUrlSparkline('getLastDirectEntryGraph');
		$view->urlSparklineWebsites 		= $this->getUrlSparkline('getLastWebsitesGraph');
		$view->urlSparklineCampaigns 		= $this->getUrlSparkline('getLastCampaignsGraph');
		$view->urlSparklineNewsletters 		= $this->getUrlSparkline('getLastNewslettersGraph');
		$view->urlSparklinePartners 		= $this->getUrlSparkline('getLastPartnersGraph');
		
		// sparklines for the evolution of the distinct keywords count/websites count/ etc
		$view->urlSparklineDistinctSearchEngines 	= $this->getUrlSparkline('getLastDistinctSearchEnginesGraph');
		$view->urlSparklineDistinctKeywords 		= $this->getUrlSparkline('getLastDistinctKeywordsGraph');
		$view->urlSparklineDistinctWebsites 		= $this->getUrlSparkline('getLastDistinctWebsitesGraph');
		$view->urlSparklineDistinctPartners 		= $this->getUrlSparkline('getLastDistinctPartnersGraph');
		$view->urlSparklineDistinctCampaigns 		= $this->getUrlSparkline('getLastDistinctCampaignsGraph');
		
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
	
	/**
	 * 
	 * @param array  paramsToSet = array( 'date' => 'last50', 'viewDataTable' =>'sparkline' )
	 */
	protected function getGraphParamsModified($paramsToSet = array())
	{
		if(!isset($paramsToSet['range']))
		{
			$range = 'last30';
		}
		else
		{
			$range = $paramsToSet['range'];
		}
		
		if(!isset($paramsToSet['date']))
		{
			$endDate = $this->strDate;
		}
		else
		{
			$endDate = $paramsToSet['date'];
		}
		
		if(!isset($paramsToSet['period']))
		{
			$period = Piwik_Common::getRequestVar('period');
		}
		else
		{
			$period = $paramsToSet['period'];
		}
		
		$last30Relative = new Piwik_Period_Range($period, $range );
		
		$last30Relative->setDefaultEndDate(new Piwik_Date($endDate));
		
		$paramDate = $last30Relative->getDateStart()->toString() . "," . $last30Relative->getDateEnd()->toString();
		
		$params = array_merge($paramsToSet , array(	'date' => $paramDate ) );
		
		return $params;
	}
	
	protected function getUrlSparkline( $action )
	{
		$params = $this->getGraphParamsModified( 
					array(	'viewDataTable' => 'sparkline', 
							'action' => $action)
				);
		$url = Piwik_Url::getCurrentQueryStringWithParametersModified($params);
		return $url;
	}
	
	protected function getLastUnitGraph($currentControllerAction, $apiMethod)
	{
		require_once "ViewDataTable/Graph.php";
		$view = Piwik_ViewDataTable::factory(null, 'graphEvolution');
		$view->init( $this->currentControllerName, $currentControllerAction, $apiMethod );
		
		// if the date is not yet a nicely formatted date range ie. YYYY-MM-DD,YYYY-MM-DD we build it
		// otherwise the current controller action is being called with the good date format already so it's fine
		// see constructor
		if( !is_null($this->date))
		{
			$view->setParametersToModify( $this->getGraphParamsModified( array('date'=>$this->strDate)));
		}
		
		return $view;
	}
	
	
		/*
		 * 

List of the public methods for the class Piwik_Actions_API
- getActions : [idSite, period, date, expanded = , idSubtable = ]
- getDownloads : [idSite, period, date, expanded = , idSubtable = ]
- getOutlinks : [idSite, period, date, expanded = , idSubtable = ]

		 */
	protected function getActionsView($currentControllerName,
						$currentMethod,
						$methodToCall = 'Actions.getActions', 
						$subMethod = 'getActionsSubDataTable')
	{
		$view = Piwik_ViewDataTable::factory();
		$view->init(  	$currentControllerName,
						$currentMethod, 
						$methodToCall, 
						$subMethod );
		$view->setTemplate('Home/templates/datatable_actions.tpl');
		
		if(Piwik_Common::getRequestVar('idSubtable', -1) != -1)
		{
			$view->setTemplate('Home/templates/datatable_actions_subdatable.tpl');
		}
		$view->setSearchRecursive();
		
		$currentlySearching = $view->setRecursiveLoadDataTableIfSearchingForPattern();
		if($currentlySearching)
		{
			$view->setTemplate('Home/templates/datatable_actions_recursive.tpl');
		}
		$view->disableSort();
		
		$view->setSortedColumn( 'nb_hits', 'desc' );
		
		$view->disableOffsetInformation();
		
		$view->setColumnsToDisplay( array(0,1,2) );
		$view->setLimit( 100 );
		
		// computing minimum value to exclude
		$visitsInfo = $this->getVisitsSummary(); 
		$nbActions = $visitsInfo->getColumn('nb_actions');
		$nbActionsLowPopulationThreshold = floor(0.02 * $nbActions); // 2 percent of the total number of actions
		$view->setExcludeLowPopulation( $nbActionsLowPopulationThreshold, 'nb_hits' );
		
		$view->main();
		
		// we need to rewrite the phpArray so it contains all the recursive arrays
		if($currentlySearching)
		{
			$phpArrayRecursive = $this->getArrayFromRecursiveDataTable($view->dataTable);
//			var_dump($phpArrayRecursive);exit;
			$view->view->arrayDataTable = $phpArrayRecursive;
		}
//		var_dump( $view->view->arrayDataTable);exit;
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
//					$filter = new Piwik_DataTable_Filter_ReplaceColumnNames(
//									$subTable,
//									Piwik_Actions::getColumnsMap()
//								);				
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
		$view = $this->getActionsView( 	$this->currentControllerName, 
										__FUNCTION__,
										'Actions.getDownloads', 
										'getDownloadsSubDataTable' );
		
		return $this->renderView($view, $fetch);
	}
	function getDownloadsSubDataTable($fetch = false)
	{
		$view = $this->getActionsView( 	$this->currentControllerName, 
										__FUNCTION__,
										'Actions.getDownloads', 
										'getDownloadsSubDataTable' );
		
		return $this->renderView($view, $fetch);
	}
	function getActions($fetch = false)
	{
		$view = $this->getActionsView(	$this->currentControllerName, 
										__FUNCTION__,
										'Actions.getActions', 
										'getActionsSubDataTable' );
		
		return $this->renderView($view, $fetch);
	}
	function getActionsSubDataTable($fetch = false)
	{
		$view = $this->getActionsView( 	$this->currentControllerName, 
										__FUNCTION__,
										'Actions.getActions', 
										'getActionsSubDataTable'  );
		
		return $this->renderView($view, $fetch);
	}
	function getOutlinks($fetch = false)
	{
		$view = $this->getActionsView(	$this->currentControllerName, 
										__FUNCTION__,
										'Actions.getOutlinks', 
										'getOutlinksSubDataTable' );
		
		return $this->renderView($view, $fetch);
	}
	function getOutlinksSubDataTable($fetch = false)
	{
		$view = $this->getActionsView( 	$this->currentControllerName, 
										__FUNCTION__,
										'Actions.getOutlinks', 
										'getOutlinksSubDataTable'  );
		
		return $this->renderView($view, $fetch);
	}
	
	/**
	 * General visit
	 */
	function getVisitsSummary()
	{
		$requestString = 'method=' . "VisitsSummary.get" . '&format=original'.
			// we disable filters for example "search for pattern", in the case this method is called 
			// by a method that already calls the API with some generic filters applied 
			'&disable_generic_filters=true'; 
		$request = new Piwik_API_Request($requestString);
		return $request->process();
	}

	function getLastVisitsGraph( $fetch = false )
	{
		$view = $this->getLastUnitGraph(__FUNCTION__, "VisitsSummary.getVisits");
		return $this->renderView($view, $fetch);
	}
	
	function getLastUniqueVisitorsGraph( $fetch = false )
	{
		$view = $this->getLastUnitGraph(__FUNCTION__, "VisitsSummary.getUniqueVisitors");
		return $this->renderView($view, $fetch);
	}
	
	function getLastActionsGraph( $fetch = false )
	{
		$view = $this->getLastUnitGraph(__FUNCTION__, "VisitsSummary.getActions");
		return $this->renderView($view, $fetch);
	}
	
	function getLastSumVisitsLengthGraph( $fetch = false )
	{
		$view = $this->getLastUnitGraph(__FUNCTION__, "VisitsSummary.getSumVisitsLength");
		return $this->renderView($view, $fetch);
	}
	
	function getLastMaxActionsGraph( $fetch = false )
	{
		$view = $this->getLastUnitGraph(__FUNCTION__, "VisitsSummary.getMaxActions");
		return $this->renderView($view, $fetch);
	}
	
	function getLastBounceCountGraph( $fetch = false )
	{
		$view = $this->getLastUnitGraph(__FUNCTION__, "VisitsSummary.getBounceCount");
		return $this->renderView($view, $fetch);
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
	
	function getLastVisitsReturningGraph( $fetch = false )
	{
		$view = $this->getLastUnitGraph(__FUNCTION__, "VisitFrequency.getVisitsReturning");
		return $this->renderView($view, $fetch);
	}
		
	function getLastActionsReturningGraph( $fetch = false )
	{
		$view = $this->getLastUnitGraph(__FUNCTION__, "VisitFrequency.getActionsReturning");
		return $this->renderView($view, $fetch);
	}
	
	function getLastSumVisitsLengthReturningGraph( $fetch = false )
	{
		$view = $this->getLastUnitGraph(__FUNCTION__, "VisitFrequency.getSumVisitsLengthReturning");
		return $this->renderView($view, $fetch);
	}
	
	function getLastMaxActionsReturningGraph( $fetch = false )
	{
		$view = $this->getLastUnitGraph(__FUNCTION__, "VisitFrequency.getMaxActionsReturning");
		return $this->renderView($view, $fetch);
	}
	
	function getLastBounceCountReturningGraph( $fetch = false )
	{
		$view = $this->getLastUnitGraph(__FUNCTION__, "VisitFrequency.getBounceCountReturning");
		return $this->renderView($view, $fetch);
	}
	
	
	/**
	 * VisitTime
	 */
	function getVisitInformationPerServerTime( $fetch = false)
	{
		$view = Piwik_ViewDataTable::factory(null, 'graphVerticalBar');
		$view->init( $this->currentControllerName,  __FUNCTION__, 
								"VisitTime.getVisitInformationPerServerTime" );
		
		$view->setColumnsToDisplay( array(0,2) );
		$view->setSortedColumn( 0, 'asc' );
		$view->setLimit( 24 );
		$view->disableSearchBox();
		$view->disableExcludeLowPopulation();
		$view->disableOffsetInformation();
		
		return $this->renderView($view, $fetch);
	}
	
	function getVisitInformationPerLocalTime( $fetch = false)
	{
		$view = Piwik_ViewDataTable::factory(null, 'graphVerticalBar');
		$view->init( $this->currentControllerName,  __FUNCTION__, 
								"VisitTime.getVisitInformationPerLocalTime" );
		
		$view->setColumnsToDisplay( array(0,2) );
		$view->setSortedColumn( 0, 'asc' );
		$view->setLimit( 24 );
		$view->setGraphLimit( 24 );
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
		$view = Piwik_ViewDataTable::factory( null, 'cloud' );
		$view->init( $this->currentControllerName,  __FUNCTION__, 
									"VisitorInterest.getNumberOfVisitsPerVisitDuration" );
		
		$view->setColumnsToDisplay( array(0,1) );
		$view->disableSort();
		$view->disableExcludeLowPopulation();
		$view->disableOffsetInformation();
		$view->disableSearchBox();
		
		return $this->renderView($view, $fetch);
	}
	
	function getNumberOfVisitsPerPage( $fetch = false)
	{
		$view = Piwik_ViewDataTable::factory();
		$view->init( $this->currentControllerName,  __FUNCTION__, 
									"VisitorInterest.getNumberOfVisitsPerPage" );
		
		$view->setColumnsToDisplay( array(0,1) );
		$view->setSortedColumn( 'nb_visits' );
		$view->disableExcludeLowPopulation();
		$view->disableOffsetInformation();
		$view->disableSearchBox();
		$view->disableSort();
		$view->main();
		
		return $this->renderView($view, $fetch);
	}
	
	/**
	 * Provider
	 */
	function getProvider( $fetch = false)
	{
		$view = Piwik_ViewDataTable::factory();
		$view->init( $this->currentControllerName,  __FUNCTION__, "Provider.getProvider" );
		
		$view->setColumnsToDisplay( array(0,1) );
		$view->setSortedColumn( 1 );
		$view->setLimit( 5 );
		
		return $this->renderView($view, $fetch);
	}
	
	/**
	 * User Country
	 */
	function getCountry( $fetch = false)
	{
		$view = Piwik_ViewDataTable::factory();
		$view->init( $this->currentControllerName, __FUNCTION__, "UserCountry.getCountry" );
		$view->disableExcludeLowPopulation();
		
		$view->setColumnsToDisplay( array(0,1) );
		$view->setSortedColumn( 1 );
		$view->disableSearchBox();
		
		// sorting by label is not correct as the labels are the ISO codes before being
		// mapped to the country names
//		$view->disableSort();
		$view->setLimit( 5 );
		
		return $this->renderView($view, $fetch);
	}

	function getNumberOfDistinctCountries( $fetch = false)
	{
		return $this->getNumericValue('UserCountry.getNumberOfDistinctCountries');
	}

	function getLastDistinctCountriesGraph( $fetch = false )
	{
		$view = $this->getLastUnitGraph(__FUNCTION__, "UserCountry.getNumberOfDistinctCountries");
		return $this->renderView($view, $fetch);
	}
	
	function getContinent( $fetch = false)
	{
		$view = Piwik_ViewDataTable::factory();
		$view->init( $this->currentControllerName, __FUNCTION__, "UserCountry.getContinent" );
		$view->disableExcludeLowPopulation();
		$view->disableSearchBox();
		$view->disableOffsetInformation();
		$view->disableSort();
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
		$view->init( $this->currentControllerName,  $currentControllerAction, $APItoCall );
		$view->disableSearchBox();
		$view->disableExcludeLowPopulation();
		
		$view->setColumnsToDisplay( array(0,2) );
		$view->setSortedColumn( 1 );
		$view->setLimit( 5 );
		$view->setGraphLimit(5);
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
		$view->setLimit( 3 );
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
		$view->setGraphLimit(7);
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
		$view->init( $this->currentControllerName,  __FUNCTION__, 'UserSettings.getPlugin' );
		$view->disableSearchBox();
		$view->disableExcludeLowPopulation();
		$view->disableSort();
		$view->disableOffsetInformation();
		
		$view->setColumnsToDisplay( array(0,1) );
		$view->setSortedColumn( 2 );
		$view->setLimit( 10 );
		
		return $this->renderView($view, $fetch);
	}



	/**
	 * Referers
	 */
	function getRefererType( $fetch = false)
	{
		$view = Piwik_ViewDataTable::factory(null, 'cloud');
		$view->init( $this->currentControllerName,  	
									'getRefererType', 
									'Referers.getRefererType'
								);
		$view->disableSearchBox();
		$view->disableOffsetInformation();
		$view->disableExcludeLowPopulation();
		$view->doNotShowFooter();
		
		$view->setColumnsToDisplay( array(0,1,2) );
		
		return $this->renderView($view, $fetch);
	}

	function getKeywords( $fetch = false)
	{
		$view = Piwik_ViewDataTable::factory();
		$view->init( $this->currentControllerName, 	'getKeywords', 
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
		$view->init( $this->currentControllerName, 	'getSearchEnginesFromKeywordId', 
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
		$view->init( $this->currentControllerName,  	'getSearchEngines', 
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
		$view->init( $this->currentControllerName, 	'getKeywordsFromSearchEngineId', 
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
		$view->init( $this->currentControllerName,  	'getWebsites', 
											'Referers.getWebsites',
											'getUrlsFromWebsiteId'
								);
		$view->disableExcludeLowPopulation();
		
		$view->setColumnsToDisplay( array(0,2) );
		
		$view->setLimit(5);
		$view->setGraphLimit(12);
		
		return $this->renderView($view, $fetch);
	}
	
	function getCampaigns( $fetch = false)
	{
		$view = Piwik_ViewDataTable::factory();
		$view->init( $this->currentControllerName,  	'getCampaigns', 
											'Referers.getCampaigns',
											'getKeywordsFromCampaignId'
								);

		$view->disableSearchBox();
		$view->disableExcludeLowPopulation();
		$view->setLimit( 5 );
		
		$view->setColumnsToDisplay( array(0,2) );
		
		return $this->renderView($view, $fetch);
	}
	
	function getKeywordsFromCampaignId( $fetch = false)
	{
		$view = Piwik_ViewDataTable::factory();
		$view->init( $this->currentControllerName, 	'getKeywordsFromCampaignId', 
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
		$view->init( $this->currentControllerName, 	'getUrlsFromWebsiteId', 
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
		$view->init( $this->currentControllerName,  	'getPartners', 
											'Referers.getPartners',
											'getUrlsFromPartnerId'
								);
		$view->disableSearchBox();
		$view->disableExcludeLowPopulation();
		$view->setLimit( 5 );
		
		$view->setColumnsToDisplay( array(0,2) );
		
		return $this->renderView($view, $fetch);
	}
	
	function getUrlsFromPartnerId( $fetch = false)
	{
		$view = Piwik_ViewDataTable::factory();
		$view->init( $this->currentControllerName, 	'getUrlsFromPartnerId', 
											'Referers.getUrlsFromPartnerId'
								);
		$view->disableSearchBox();
		$view->disableExcludeLowPopulation();
		$view->setColumnsToDisplay( array(0,2));

		return $this->renderView($view, $fetch);
	}
	
	
	function getReferersType()
	{
		// we disable the queued filters because here we want to get the visits coming from search engines
		// if the filters were applied we would have to look up for a label looking like "Search Engines" 
		// which is not good when we have translations
		$requestString = 'method='."Referers.getRefererType".'&format=original'.'&disable_queued_filters=1';
		$request = new Piwik_API_Request($requestString);
		return $request->process();
	}

	function getLastSearchEnginesGraph( $fetch = false )
	{
		$view = $this->getLastUnitGraph(__FUNCTION__, 'Referers.getRefererType');
		$view->setSearchPattern(Piwik_Common::REFERER_TYPE_SEARCH_ENGINE, 'label');
		return $this->renderView($view, $fetch);
	}
	function getLastDirectEntryGraph( $fetch = false )
	{
		$view = $this->getLastUnitGraph(__FUNCTION__, 'Referers.getRefererType');
		$view->setSearchPattern(Piwik_Common::REFERER_TYPE_DIRECT_ENTRY, 'label');
		return $this->renderView($view, $fetch);
	}
	function getLastWebsitesGraph( $fetch = false )
	{
		$view = $this->getLastUnitGraph(__FUNCTION__, 'Referers.getRefererType');
		$view->setSearchPattern(Piwik_Common::REFERER_TYPE_WEBSITE, 'label');
		return $this->renderView($view, $fetch);
	}
	function getLastCampaignsGraph( $fetch = false )
	{
		$view = $this->getLastUnitGraph(__FUNCTION__, 'Referers.getRefererType');
		$view->setSearchPattern(Piwik_Common::REFERER_TYPE_CAMPAIGN, 'label');
		return $this->renderView($view, $fetch);
	}
	function getLastNewslettersGraph( $fetch = false )
	{
		$view = $this->getLastUnitGraph(__FUNCTION__, 'Referers.getRefererType');
		$view->setSearchPattern(Piwik_Common::REFERER_TYPE_NEWSLETTER, 'label');
		return $this->renderView($view, $fetch);
	}
	function getLastPartnersGraph( $fetch = false )
	{
		$view = $this->getLastUnitGraph(__FUNCTION__, 'Referers.getRefererType');
		$view->setSearchPattern(Piwik_Common::REFERER_TYPE_PARTNER, 'label');
		return $this->renderView($view, $fetch);
	}
	
	function getLastDistinctSearchEnginesGraph( $fetch = false )
	{
		$view = $this->getLastUnitGraph(__FUNCTION__, "Referers.getNumberOfDistinctSearchEngines");
		return $this->renderView($view, $fetch);
	}
	function getLastDistinctKeywordsGraph( $fetch = false )
	{
		$view = $this->getLastUnitGraph(__FUNCTION__, "Referers.getNumberOfDistinctKeywords");
		return $this->renderView($view, $fetch);
	}
	function getLastDistinctWebsitesGraph( $fetch = false )
	{
		$view = $this->getLastUnitGraph(__FUNCTION__, "Referers.getNumberOfDistinctWebsites");
		return $this->renderView($view, $fetch);
	}
	function getLastDistinctPartnersGraph( $fetch = false )
	{
		$view = $this->getLastUnitGraph(__FUNCTION__, "Referers.getNumberOfDistinctPartners");
		return $this->renderView($view, $fetch);
	}
	function getLastDistinctCampaignsGraph( $fetch = false )
	{
		$view = $this->getLastUnitGraph(__FUNCTION__, "Referers.getNumberOfDistinctCampaigns");
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