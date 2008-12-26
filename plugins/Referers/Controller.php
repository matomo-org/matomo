<?php
class Piwik_Referers_Controller extends Piwik_Controller 
{
	function index()
	{
		$view = new Piwik_View('Referers/templates/index.tpl');
		
		$view->graphEvolutionReferers = $this->getLastDirectEntryGraph(true);
		$view->nameGraphEvolutionReferers = 'ReferersgetLastDirectEntryGraph'; // must be the function name used above
		
		$view->numberDistinctSearchEngines 	= $this->getNumberOfDistinctSearchEngines(true);
		$view->numberDistinctKeywords 		= $this->getNumberOfDistinctKeywords(true);
		$view->numberDistinctWebsites 		= $this->getNumberOfDistinctWebsites(true);
		$view->numberDistinctWebsitesUrls 	= $this->getNumberOfDistinctWebsitesUrls(true);
		$view->numberDistinctCampaigns 		= $this->getNumberOfDistinctCampaigns(true);
		
		// building the referers summary report 
		$view->dataTableRefererType = $this->getRefererType(true);
				
		$nameValues = $this->getReferersVisitorsByType();
		foreach($nameValues as $name => $value)
		{	
			$view->$name = $value;
		}
		// sparkline for the historical data of the above values
		$view->urlSparklineSearchEngines	= $this->getUrlSparkline('getLastSearchEnginesGraph');
		$view->urlSparklineDirectEntry 		= $this->getUrlSparkline('getLastDirectEntryGraph');
		$view->urlSparklineWebsites 		= $this->getUrlSparkline('getLastWebsitesGraph');
		$view->urlSparklineCampaigns 		= $this->getUrlSparkline('getLastCampaignsGraph');
		
		// sparklines for the evolution of the distinct keywords count/websites count/ etc
		$view->urlSparklineDistinctSearchEngines 	= $this->getUrlSparkline('getLastDistinctSearchEnginesGraph');
		$view->urlSparklineDistinctKeywords 		= $this->getUrlSparkline('getLastDistinctKeywordsGraph');
		$view->urlSparklineDistinctWebsites 		= $this->getUrlSparkline('getLastDistinctWebsitesGraph');
		$view->urlSparklineDistinctCampaigns 		= $this->getUrlSparkline('getLastDistinctCampaignsGraph');
		
		echo $view->render();
	}
	
	function getSearchEnginesAndKeywords()
	{
		$view = new Piwik_View('Referers/templates/searchEngines_Keywords.tpl');
		$view->searchEngines = $this->getSearchEngines(true) ;
		$view->keywords = $this->getKeywords(true);
		echo $view->render();
	}
	
	function getRefererType( $fetch = false)
	{
		$view = Piwik_ViewDataTable::factory('cloud');
		$view->init( $this->pluginName,  	
									__FUNCTION__, 
									'Referers.getRefererType'
								);
		$view->disableSearchBox();
		$view->disableOffsetInformation();
		$view->disableExcludeLowPopulation();
		$view->doNotShowFooter();
		$view->enableShowGoals();
		
		$view->setColumnsToDisplay( array('label','nb_uniq_visitors', 'nb_visits') );
		
		return $this->renderView($view, $fetch);
	}

	function getKeywords( $fetch = false)
	{
		$view = Piwik_ViewDataTable::factory();
		$view->init( $this->pluginName, 	__FUNCTION__, 
											'Referers.getKeywords', 
											'getSearchEnginesFromKeywordId'
								);
		$view->disableExcludeLowPopulation();
		$view->setColumnsToDisplay( array('label','nb_visits') );
		$view->enableShowGoals();
		$view->disableSubTableWhenShowGoals();
		return $this->renderView($view, $fetch);
	}
	
	function getSearchEnginesFromKeywordId( $fetch = false )
	{
		$view = Piwik_ViewDataTable::factory();
		$view->init( $this->pluginName, 	__FUNCTION__, 
											'Referers.getSearchEnginesFromKeywordId'
								);
		$view->disableSearchBox();
		$view->disableExcludeLowPopulation();
		$view->setColumnsToDisplay( array('label','nb_visits') );

		return $this->renderView($view, $fetch);
	}
	
	
	function getSearchEngines( $fetch = false)
	{
		$view = Piwik_ViewDataTable::factory();
		$view->init( $this->pluginName,  	__FUNCTION__, 
											'Referers.getSearchEngines', 
											'getKeywordsFromSearchEngineId'
								);
		$view->disableSearchBox();
		$view->disableExcludeLowPopulation();
		$view->enableShowGoals();
		$view->disableSubTableWhenShowGoals();
		
		$view->setColumnsToDisplay( array('label','nb_visits') );
		
		return $this->renderView($view, $fetch);
	}
	
	public function getSearchEnginesEvolution($fetch = false)
	{		
		$view = Piwik_ViewDataTable::factory('graphEvolution');
		$view->init( $this->pluginName, __FUNCTION__, 'Referers.getSearchEngines' );
		
		$view->setColumnsToDisplay( 'nb_uniq_visitors' );
		$view->setExactPattern( array('Google','Yahoo!'), 'label');
		//$view->setExactPattern( array('Google'), 'label');
		
		return $this->renderView($view, $fetch);
	}	
	
	function getKeywordsFromSearchEngineId( $fetch = false )
	{
		$view = Piwik_ViewDataTable::factory();
		$view->init( $this->pluginName, 	__FUNCTION__, 
											'Referers.getKeywordsFromSearchEngineId'
								);
		$view->disableSearchBox();
		$view->disableExcludeLowPopulation();
		$view->setColumnsToDisplay( array('label','nb_visits') );

		return $this->renderView($view, $fetch);
	}
	
	function getWebsites( $fetch = false)
	{
		$view = Piwik_ViewDataTable::factory();
		$view->init( $this->pluginName,  	__FUNCTION__, 
											'Referers.getWebsites',
											'getUrlsFromWebsiteId'
								);
		$view->disableExcludeLowPopulation();
		$view->setColumnsToDisplay( array('label','nb_visits') );
		$view->setLimit(10);
		$view->enableShowGoals();
		$view->disableSubTableWhenShowGoals();
		
		return $this->renderView($view, $fetch);
	}
	
	function getCampaigns( $fetch = false)
	{
		$view = Piwik_ViewDataTable::factory();
		$view->init( $this->pluginName,  	__FUNCTION__, 
											'Referers.getCampaigns',
											'getKeywordsFromCampaignId'
								);

		$view->disableSearchBox();
		$view->disableExcludeLowPopulation();
		$view->setLimit( 5 );
		$view->enableShowGoals();
		$view->setColumnsToDisplay( array('label','nb_visits') );
		return $this->renderView($view, $fetch);
	}
	
	function getKeywordsFromCampaignId( $fetch = false)
	{
		$view = Piwik_ViewDataTable::factory();
		$view->init( $this->pluginName, 	__FUNCTION__, 
											'Referers.getKeywordsFromCampaignId'
								);

		$view->disableSearchBox();
		$view->disableExcludeLowPopulation();
		$view->setColumnsToDisplay( array('label','nb_visits') );

		return $this->renderView($view, $fetch);
	}
	
	function getUrlsFromWebsiteId( $fetch = false)
	{
		$view = Piwik_ViewDataTable::factory();
		$view->init( $this->pluginName, 	__FUNCTION__, 
											'Referers.getUrlsFromWebsiteId'
								);
		$view->disableSearchBox();
		$view->disableExcludeLowPopulation();
		$view->setColumnsToDisplay( array('label','nb_visits') );
		return $this->renderView($view, $fetch);
	}
	
	protected function getReferersVisitorsByType()
	{
		// we disable the queued filters because here we want to get the visits coming from search engines
		// if the filters were applied we would have to look up for a label looking like "Search Engines" 
		// which is not good when we have translations
		$requestString = "method=Referers.getRefererType
						&format=original
						&disable_queued_filters=1";
		$request = new Piwik_API_Request($requestString);
		$dataTableReferersType =  $request->process();
		
		$nameToColumnId = array(
			'visitorsFromSearchEngines' => Piwik_Common::REFERER_TYPE_SEARCH_ENGINE,
			'visitorsFromDirectEntry' =>  Piwik_Common::REFERER_TYPE_DIRECT_ENTRY,
			'visitorsFromWebsites'  => Piwik_Common::REFERER_TYPE_WEBSITE,
			'visitorsFromCampaigns' =>  Piwik_Common::REFERER_TYPE_CAMPAIGN,
		);
		$return = array();
		foreach($nameToColumnId as $nameVar => $columnId)
		{
			$value = 0;
			$row = $dataTableReferersType->getRowFromLabel($columnId);
			if($row !== false)
			{
				$value = $row->getColumn(Piwik_Archive::INDEX_NB_UNIQ_VISITORS);
			}
			$return[$nameVar] = $value;
		}
		
		return $return;
	}
	function getLastSearchEnginesGraph( $fetch = false )
	{
		$view = $this->getLastUnitGraph($this->pluginName,__FUNCTION__, 'Referers.getRefererType');
		$view->setSearchPattern(Piwik_Common::REFERER_TYPE_SEARCH_ENGINE, 'label');
		return $this->renderView($view, $fetch);
	}
	function getLastDirectEntryGraph( $fetch = false )
	{
		$view = $this->getLastUnitGraph($this->pluginName,__FUNCTION__, 'Referers.getRefererType');
		$view->setSearchPattern(Piwik_Common::REFERER_TYPE_DIRECT_ENTRY, 'label');
		return $this->renderView($view, $fetch);
	}
	function getLastWebsitesGraph( $fetch = false )
	{
		$view = $this->getLastUnitGraph($this->pluginName,__FUNCTION__, 'Referers.getRefererType');
		$view->setSearchPattern(Piwik_Common::REFERER_TYPE_WEBSITE, 'label');
		return $this->renderView($view, $fetch);
	}
	function getLastCampaignsGraph( $fetch = false )
	{
		$view = $this->getLastUnitGraph($this->pluginName,__FUNCTION__, 'Referers.getRefererType');
		$view->setSearchPattern(Piwik_Common::REFERER_TYPE_CAMPAIGN, 'label');
		return $this->renderView($view, $fetch);
	}
	function getLastDistinctSearchEnginesGraph( $fetch = false )
	{
		$view = $this->getLastUnitGraph($this->pluginName,__FUNCTION__, "Referers.getNumberOfDistinctSearchEngines");
		return $this->renderView($view, $fetch);
	}
	function getLastDistinctKeywordsGraph( $fetch = false )
	{
		$view = $this->getLastUnitGraph($this->pluginName,__FUNCTION__, "Referers.getNumberOfDistinctKeywords");
		return $this->renderView($view, $fetch);
	}
	function getLastDistinctWebsitesGraph( $fetch = false )
	{
		$view = $this->getLastUnitGraph($this->pluginName,__FUNCTION__, "Referers.getNumberOfDistinctWebsites");
		return $this->renderView($view, $fetch);
	}
	function getLastDistinctCampaignsGraph( $fetch = false )
	{
		$view = $this->getLastUnitGraph($this->pluginName,__FUNCTION__, "Referers.getNumberOfDistinctCampaigns");
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
}
