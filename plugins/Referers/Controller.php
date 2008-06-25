<?php
require_once "ViewDataTable.php";

class Piwik_Referers_Controller extends Piwik_Controller 
{
	function index()
	{
		$view = new Piwik_View('Referers/index.tpl');
		
		$view->graphEvolutionReferers = $this->getLastDirectEntryGraph(true);
		$view->nameGraphEvolutionReferers = 'ReferersgetLastDirectEntryGraph'; // must be the function name used above
		
		$view->numberDistinctSearchEngines 	= $this->getNumberOfDistinctSearchEngines(true);
		$view->numberDistinctKeywords 		= $this->getNumberOfDistinctKeywords(true);
		$view->numberDistinctWebsites 		= $this->getNumberOfDistinctWebsites(true);
		$view->numberDistinctWebsitesUrls 	= $this->getNumberOfDistinctWebsitesUrls(true);
		$view->numberDistinctPartners 		= $this->getNumberOfDistinctPartners(true);
		$view->numberDistinctPartnersUrls 	= $this->getNumberOfDistinctPartnersUrls(true);
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
	
	function getSearchEnginesAndKeywords()
	{
		$view = new Piwik_View('Referers/searchEngines_Keywords.tpl');
		$view->searchEngines = $this->getSearchEngines(true) ;
		$view->keywords = $this->getKeywords(true);
		echo $view->render();
	}
	/**
	 * Referers
	 */
	function getRefererType( $fetch = false)
	{
		$view = Piwik_ViewDataTable::factory('cloud');
		$view->init( $this->pluginName,  	
									'getRefererType', 
									'Referers.getRefererType'
								);
		$view->disableSearchBox();
		$view->disableOffsetInformation();
		$view->disableExcludeLowPopulation();
		$view->doNotShowFooter();
		
		$view->setColumnsToDisplay( array('label','nb_uniq_visitors', 'nb_visits') );
		
		return $this->renderView($view, $fetch);
	}

	function getKeywords( $fetch = false)
	{
		$view = Piwik_ViewDataTable::factory();
		
		$view->init( $this->pluginName, 	'getKeywords', 
											'Referers.getKeywords', 
											'getSearchEnginesFromKeywordId'
								);

		$view->disableExcludeLowPopulation();
		
		$view->setColumnsToDisplay( array('label','nb_visits') );

		return $this->renderView($view, $fetch);
	}
	
	function getSearchEnginesFromKeywordId( $fetch = false )
	{
		$view = Piwik_ViewDataTable::factory();
		$view->init( $this->pluginName, 	'getSearchEnginesFromKeywordId', 
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
		$view->init( $this->pluginName,  	'getSearchEngines', 
											'Referers.getSearchEngines', 
											'getKeywordsFromSearchEngineId'
								);
		$view->disableSearchBox();
		$view->disableExcludeLowPopulation();
		
		$view->setColumnsToDisplay( array('label','nb_visits') );
		
		return $this->renderView($view, $fetch);
	}
	
	
	function getKeywordsFromSearchEngineId( $fetch = false )
	{
		$view = Piwik_ViewDataTable::factory();
		$view->init( $this->pluginName, 	'getKeywordsFromSearchEngineId', 
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
		$view->init( $this->pluginName,  	'getWebsites', 
											'Referers.getWebsites',
											'getUrlsFromWebsiteId'
								);
		$view->disableExcludeLowPopulation();
		
		$view->setColumnsToDisplay( array('label','nb_visits') );
		
		$view->setLimit(10);
		$view->setGraphLimit(12);
		
		return $this->renderView($view, $fetch);
	}
	
	function getCampaigns( $fetch = false)
	{
		$view = Piwik_ViewDataTable::factory();
		$view->init( $this->pluginName,  	'getCampaigns', 
											'Referers.getCampaigns',
											'getKeywordsFromCampaignId'
								);

		$view->disableSearchBox();
		$view->disableExcludeLowPopulation();
		$view->setLimit( 5 );
		
		$view->setColumnsToDisplay( array('label','nb_visits') );
		
		return $this->renderView($view, $fetch);
	}
	
	function getKeywordsFromCampaignId( $fetch = false)
	{
		$view = Piwik_ViewDataTable::factory();
		$view->init( $this->pluginName, 	'getKeywordsFromCampaignId', 
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
		$view->init( $this->pluginName, 	'getUrlsFromWebsiteId', 
											'Referers.getUrlsFromWebsiteId'
								);
		$view->disableSearchBox();
		$view->disableExcludeLowPopulation();
		$view->setColumnsToDisplay( array('label','nb_visits') );

		return $this->renderView($view, $fetch);
	}
	
	function getPartners( $fetch = false)
	{
		$view = Piwik_ViewDataTable::factory();
		$view->init( $this->pluginName,  	'getPartners', 
											'Referers.getPartners',
											'getUrlsFromPartnerId'
								);
		$view->disableSearchBox();
		$view->disableExcludeLowPopulation();
		$view->setLimit( 5 );
		
		$view->setColumnsToDisplay( array('label','nb_visits') );
		
		return $this->renderView($view, $fetch);
	}
	
	function getUrlsFromPartnerId( $fetch = false)
	{
		$view = Piwik_ViewDataTable::factory();
		$view->init( $this->pluginName, 	'getUrlsFromPartnerId', 
											'Referers.getUrlsFromPartnerId'
								);
		$view->disableSearchBox();
		$view->disableExcludeLowPopulation();
		$view->setColumnsToDisplay( array('label','nb_visits') );

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
	
	protected function getReferersVisitorsByType()
	{
		// this is raw data (no filters applied, on purpose) so we select the data using the magic integers ID 
		$dataTableReferersType = $this->getReferersType(true);
		
		$nameToColumnId = array(
			'visitorsFromSearchEngines' => Piwik_Common::REFERER_TYPE_SEARCH_ENGINE,
			'visitorsFromDirectEntry' =>  Piwik_Common::REFERER_TYPE_DIRECT_ENTRY,
			'visitorsFromWebsites'  => Piwik_Common::REFERER_TYPE_WEBSITE,
			'visitorsFromCampaigns' =>  Piwik_Common::REFERER_TYPE_CAMPAIGN,
			'visitorsFromNewsletters' =>  Piwik_Common::REFERER_TYPE_NEWSLETTER,
			'visitorsFromPartners' =>  Piwik_Common::REFERER_TYPE_PARTNER,
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
	function getLastNewslettersGraph( $fetch = false )
	{
		$view = $this->getLastUnitGraph($this->pluginName,__FUNCTION__, 'Referers.getRefererType');
		$view->setSearchPattern(Piwik_Common::REFERER_TYPE_NEWSLETTER, 'label');
		return $this->renderView($view, $fetch);
	}
	function getLastPartnersGraph( $fetch = false )
	{
		$view = $this->getLastUnitGraph($this->pluginName,__FUNCTION__, 'Referers.getRefererType');
		$view->setSearchPattern(Piwik_Common::REFERER_TYPE_PARTNER, 'label');
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
	function getLastDistinctPartnersGraph( $fetch = false )
	{
		$view = $this->getLastUnitGraph($this->pluginName,__FUNCTION__, "Referers.getNumberOfDistinctPartners");
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
	function getNumberOfDistinctPartners( $fetch = false)
	{
		return $this->getNumericValue('Referers.' . __FUNCTION__);
	}
	function getNumberOfDistinctPartnersUrls ( $fetch = false)
	{
		return $this->getNumericValue('Referers.' . __FUNCTION__);
	}
}
