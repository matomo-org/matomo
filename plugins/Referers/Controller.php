<?php
class Piwik_Referers_Controller extends Piwik_Controller 
{
	function index()
	{
		$view = new Piwik_View('Referers/templates/index.tpl');
		
		$view->graphEvolutionReferers = $this->getEvolutionGraph(true, Piwik_Common::REFERER_TYPE_DIRECT_ENTRY, array('nb_visits'));
		$view->nameGraphEvolutionReferers = 'ReferersgetEvolutionGraph'; // must be the function name used above TODO why?
		
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
		$view->urlSparklineSearchEngines	= $this->getUrlSparkline('getEvolutionGraph', array('columns' => array('nb_visits'), 'typeReferer' => Piwik_Common::REFERER_TYPE_SEARCH_ENGINE));
		$view->urlSparklineDirectEntry 		= $this->getUrlSparkline('getEvolutionGraph', array('columns' => array('nb_visits'), 'typeReferer' => Piwik_Common::REFERER_TYPE_DIRECT_ENTRY));
		$view->urlSparklineWebsites 		= $this->getUrlSparkline('getEvolutionGraph', array('columns' => array('nb_visits'), 'typeReferer' => Piwik_Common::REFERER_TYPE_WEBSITE));
		$view->urlSparklineCampaigns 		= $this->getUrlSparkline('getEvolutionGraph', array('columns' => array('nb_visits'), 'typeReferer' => Piwik_Common::REFERER_TYPE_CAMPAIGN));
		
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
		$view = Piwik_ViewDataTable::factory('tableAllColumns');
		$view->init( $this->pluginName,  	
									__FUNCTION__, 
									'Referers.getRefererType'
								);
		$view->disableSearchBox();
		$view->disableOffsetInformation();
		$view->disableExcludeLowPopulation();
		$view->enableShowGoals();
		
		$view->setColumnsToDisplay( array('label', 'nb_visits') );
		
		return $this->renderView($view, $fetch);
	}

	function getKeywords( $fetch = false, $viewDataTable = null)
	{
		$view = Piwik_ViewDataTable::factory($viewDataTable);
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
				$value = $row->getColumn(Piwik_Archive::INDEX_NB_VISITS);
			}
			$return[$nameVar] = $value;
		}
		return $return;
	}

	protected $refererTypeToLabel = array(
		Piwik_Common::REFERER_TYPE_DIRECT_ENTRY => 'Referers_DirectEntry',
		Piwik_Common::REFERER_TYPE_SEARCH_ENGINE => 'Referers_SearchEngines',
		Piwik_Common::REFERER_TYPE_WEBSITE => 'Referers_Websites',
		Piwik_Common::REFERER_TYPE_CAMPAIGN => 'Referers_Campaigns',
	);
	
	public function getEvolutionGraph( $fetch = false, $typeReferer = false, $columns = false)
	{
		$view = $this->getLastUnitGraph($this->pluginName, __FUNCTION__, 'Referers.getRefererType');
		if(empty($columns))
		{
			$columns = Piwik_Common::getRequestVar('columns');
		}
		if(empty($typeReferer))
		{
			$typeReferer = Piwik_Common::getRequestVar('typeReferer');
		}
		$view->setColumnsToDisplay($columns);
		$view->setParametersToModify(array('typeReferer' => $typeReferer));
		foreach($columns as $columnName)
		{
			$columnTranslation = $this->standardColumnNameToTranslation[$columnName];
			$refererTypeTranslation = $this->refererTypeToLabel[$typeReferer];
			$view->setColumnTranslation(
				$columnName, 
				Piwik_Translate('Referers_MetricsFromRefererTypeGraphLegend', 
					array(	Piwik_Translate($columnTranslation), 
							Piwik_Translate($refererTypeTranslation)
						)
					)
				);
		}
		return $this->renderView($view, $fetch);
	}
	
	function getLastDistinctSearchEnginesGraph( $fetch = false )
	{
		$view = $this->getLastUnitGraph($this->pluginName,__FUNCTION__, "Referers.getNumberOfDistinctSearchEngines");
		$view->setColumnTranslation('Referers_distinctSearchEngines', ucfirst(Piwik_Translate('Referers_DistinctSearchEngines')));
		$view->setColumnsToDisplay(array('Referers_distinctSearchEngines'));
		return $this->renderView($view, $fetch);
	}
	function getLastDistinctKeywordsGraph( $fetch = false )
	{
		$view = $this->getLastUnitGraph($this->pluginName,__FUNCTION__, "Referers.getNumberOfDistinctKeywords");
		$view->setColumnTranslation('Referers_distinctKeywords', ucfirst(Piwik_Translate('Referers_DistinctKeywords')));
		$view->setColumnsToDisplay(array('Referers_distinctKeywords'));
		return $this->renderView($view, $fetch);
	}
	function getLastDistinctWebsitesGraph( $fetch = false )
	{
		$view = $this->getLastUnitGraph($this->pluginName,__FUNCTION__, "Referers.getNumberOfDistinctWebsites");
		$view->setColumnTranslation('Referers_distinctWebsites', ucfirst(Piwik_Translate('Referers_DistinctWebsites')));
		$view->setColumnsToDisplay(array('Referers_distinctWebsites'));
		return $this->renderView($view, $fetch);
	}
	function getLastDistinctCampaignsGraph( $fetch = false )
	{
		$view = $this->getLastUnitGraph($this->pluginName,__FUNCTION__, "Referers.getNumberOfDistinctCampaigns");
		$view->setColumnTranslation('Referers_distinctCampaigns', ucfirst(Piwik_Translate('Referers_DistinctCampaigns')));
		$view->setColumnsToDisplay(array('Referers_distinctCampaigns'));
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
