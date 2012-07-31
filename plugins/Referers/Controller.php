<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 *
 * @category Piwik_Plugins
 * @package Piwik_Referers
 */

/**
 *
 * @package Piwik_Referers
 */
class Piwik_Referers_Controller extends Piwik_Controller
{
	function index()
	{
		$view = Piwik_View::factory('index');
		
		$view->graphEvolutionReferers = $this->getEvolutionGraph(true, Piwik_Common::REFERER_TYPE_DIRECT_ENTRY, array('nb_visits'));
		$view->nameGraphEvolutionReferers = 'ReferersgetEvolutionGraph';
		
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
		$view = Piwik_View::factory('searchEngines_Keywords');
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
		$view->disableOffsetInformationAndPaginationControls();
		$view->disableExcludeLowPopulation();
		$view->enableShowGoals();
		$view->setLimit(10);
		$view->setColumnsToDisplay( array('label', 'nb_visits') );
		$view->setColumnTranslation('label', Piwik_Translate('Referers_ColumnRefererType'));
		$this->setMetricsVariablesView($view);
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
		$view->setColumnTranslation('label', Piwik_Translate('Referers_ColumnKeyword'));
		$view->enableShowGoals();
		$view->setLimit(25);
		$view->disableSubTableWhenShowGoals();
		
		$this->setMetricsVariablesView($view);
		
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
		$view->setColumnTranslation('label', Piwik_Translate('Referers_ColumnSearchEngine'));
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
		$view->setLimit(25);
		$view->disableSubTableWhenShowGoals();
		$view->setColumnTranslation('label', Piwik_Translate('Referers_ColumnSearchEngine'));
		
		$this->setMetricsVariablesView($view);
		
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
		$view->setColumnTranslation('label', Piwik_Translate('Referers_ColumnKeyword'));
		return $this->renderView($view, $fetch);
	}
	
	function indexWebsites($fetch = false)
	{
		return Piwik_View::singleReport(
				Piwik_Translate('Referers_Websites'),
				$this->getWebsites(true), $fetch);
	}
	
	function getWebsites( $fetch = false)
	{
		$view = Piwik_ViewDataTable::factory();
		$view->init( $this->pluginName,  	__FUNCTION__,
											'Referers.getWebsites',
											'getUrlsFromWebsiteId'
								);
		$view->disableExcludeLowPopulation();
		$view->enableShowGoals();
		$view->setLimit(25);
		$view->disableSubTableWhenShowGoals();
		$view->setColumnTranslation('label', Piwik_Translate('Referers_ColumnWebsite'));
		
		$this->setMetricsVariablesView($view);
		
		return $this->renderView($view, $fetch);
	}
	
	function indexCampaigns($fetch = false)
	{
		return Piwik_View::singleReport(
				Piwik_Translate('Referers_Campaigns'),
				$this->getCampaigns(true), $fetch);
	}
	
	function getCampaigns( $fetch = false)
	{
		$view = Piwik_ViewDataTable::factory();
		$view->init( $this->pluginName,  	__FUNCTION__,
											'Referers.getCampaigns',
											'getKeywordsFromCampaignId'
								);
		$view->disableExcludeLowPopulation();
		$view->enableShowGoals();
		$view->setLimit(25);
		$view->setColumnsToDisplay( array('label','nb_visits') );
		$view->setColumnTranslation('label', Piwik_Translate('Referers_ColumnCampaign'));
		$view->setFooterMessage( 'Help: <a target="_blank" href="http://piwik.org/docs/tracking-campaigns/">Tracking Campaigns in Piwik</a> - <a target="_blank" href="http://piwik.org/docs/tracking-campaigns/url-builder/">URL Builder tool</a>');
		$this->setMetricsVariablesView($view);
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
		$view->setColumnTranslation('label', Piwik_Translate('Referers_ColumnKeyword'));

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
		$view->setColumnTranslation('label', Piwik_Translate('Referers_ColumnWebsitePage'));
		$view->setTooltipMetadataName('url');
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

	protected $referrerTypeToLabel = array(
		Piwik_Common::REFERER_TYPE_DIRECT_ENTRY => 'Referers_DirectEntry',
		Piwik_Common::REFERER_TYPE_SEARCH_ENGINE => 'Referers_SearchEngines',
		Piwik_Common::REFERER_TYPE_WEBSITE => 'Referers_Websites',
		Piwik_Common::REFERER_TYPE_CAMPAIGN => 'Referers_Campaigns',
	);
	
	public function getEvolutionGraph( $fetch = false, $typeReferer = false, $columns = false)
	{
		$view = $this->getLastUnitGraph($this->pluginName, __FUNCTION__, 'Referers.getRefererType');
		
		// configure displayed columns
		if(empty($columns))
		{
			$columns = Piwik_Common::getRequestVar('columns');
			$columns = Piwik::getArrayFromApiParameter($columns);
		}
		$columns = !is_array($columns) ? array($columns) : $columns;
		$view->setColumnsToDisplay($columns);
		
		// configure selectable columns
		if (Piwik_Common::getRequestVar('period', false) == 'day') {
			$selectable = array('nb_visits', 'nb_uniq_visitors', 'nb_actions');
		} else {
			$selectable = array('nb_visits', 'nb_actions');
		}
		$view->setSelectableColumns($selectable);
		
		// configure displayed rows
		$visibleRows = Piwik_Common::getRequestVar('rows', false);
		if ($visibleRows !== false)
		{
			// this happens when the row picker has been used
			$visibleRows = Piwik::getArrayFromApiParameter($visibleRows);
		}
		else
		{
			// use $typeReferer as default
			if($typeReferer === false)
			{
				$typeReferer = Piwik_Common::getRequestVar('typeReferer', false);
			}
			$label = Piwik_getRefererTypeLabel($typeReferer);
			$label = Piwik_Translate($label);
			$visibleRows = array($label);
			$view->setParametersToModify(array('rows' => $label));
		}
		$view->addRowPicker($visibleRows);
		
		$view->setReportDocumentation(Piwik_Translate('Referers_EvolutionDocumentation').'<br />'
				.Piwik_Translate('General_BrokenDownReportDocumentation').'<br />'
				.Piwik_Translate('Referers_EvolutionDocumentationMoreInfo', '&quot;'.Piwik_Translate('Referers_DetailsByRefererType').'&quot;'));
		
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

	function getKeywordsForPage()
	{
		Piwik::checkUserHasViewAccess($this->idSite);
		
		$requestUrl = '&date=previous1'
						.'&period=week'
						.'&idSite='.$this->idSite
						;
						
		$topPageUrlRequest = $requestUrl
							.'&method=Actions.getPageUrls'
							.'&filter_limit=50'
							.'&format=original';
		$request = new Piwik_API_Request($topPageUrlRequest);
		$request = $request->process();
		$tables = $request->getArray();
		
		$topPageUrl = false;
		$first = key($tables);
		if(!empty($first))
		{
			$topPageUrls = $tables[$first];
			$topPageUrls = $topPageUrls->getRowsMetadata('url');
			$tmpTopPageUrls = array_values($topPageUrls);
			$topPageUrl = current($tmpTopPageUrls);
		}
		if(empty($topPageUrl))
		{
			$topPageUrl = $this->site->getMainUrl();
		}
		$url = $topPageUrl;
						
		// HTML
		$api = Piwik_Url::getCurrentUrlWithoutFileName()
						.'?module=API&method=Referers.getKeywordsForPageUrl'
						.'&format=php'
						.'&filter_limit=10'
						.'&token_auth='.Piwik::getCurrentUserTokenAuth();
						
		$api .= $requestUrl;
		$code = '
// This function will call the API to get best keyword for current URL.
// Then it writes the list of best keywords in a HTML list
function DisplayTopKeywords($url = "")
{
	// Do not spend more than 1 second fetching the data
	@ini_set("default_socket_timeout", $timeout = 1);
	// Get the Keywords data
	$url = empty($url) ? "http://". $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"] : $url;
	$api = "'.$api.'&url=" . urlencode($url);
	$keywords = @unserialize(file_get_contents($api));
	if($keywords === false || isset($keywords["result"])) {
		// DEBUG ONLY: uncomment for troubleshooting an empty output (the URL output reveals the token_auth)
		// echo "Error while fetching the <a href=\'$api\'>Top Keywords from Piwik</a>";
		return;
	}

	// Display the list in HTML
	$output = "<h2>Top Keywords for <a href=\'$url\'>$url</a></h2><ul>";
	foreach($keywords as $keyword) {
		$output .= "<li>". $keyword[0]. "</li>";
	}
	if(empty($keywords)) { $output .= "Nothing yet..."; }
	$output .= "</ul>";
	echo $output;
}
';

		$jsonRequest = str_replace('format=php', 'format=json', $api);
		echo "<p>This widget is designed to work in your website directly.
		This widget makes it easy to use Piwik to <i>automatically display the list of Top Keywords</i>, for each of your website Page URLs.</p>
		<p>
		<b>Example API URL</b> - For example if you would like to get the top 10 keywords, used last week, to land on the page <a target='_blank' href='$topPageUrl'>$topPageUrl</a>,
		in format JSON: you would dynamically fetch the data using <a target='_blank' href='$jsonRequest&url=".urlencode($topPageUrl)."'>this API request URL</a>. Make sure you encode the 'url' parameter in the URL.</p>
		
		<p><b>PHP Function ready to use!</b> - If you use PHP on your website, we have prepared a small code snippet that you can copy paste in your Website PHP files. You can then simply call the function <code>DisplayTopKeywords();</code> anywhere in your template, at the bottom of the content or in your blog sidebar.
		If you run this code in your page $topPageUrl, it would output the following:";
		
		echo "<div style='width:400px;margin-left:20px;padding:10px;border:1px solid black;'>";
		function DisplayTopKeywords($url = "", $api)
		{
			// Do not spend more than 1 second fetching the data
			@ini_set("default_socket_timeout", $timeout = 1);
			// Get the Keywords data
			$url = empty($url) ? "http://". $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"] : $url;
			$api = $api."&url=" . urlencode($url);
			$keywords = @unserialize(file_get_contents($api));
			if($keywords === false || isset($keywords["result"])) {
				// DEBUG ONLY: uncomment for troubleshooting an empty output (the URL output reveals the token_auth)
				//echo "Error while fetching the <a href=\'".$api."\'>Top Keywords from Piwik</a>";
				return;
			}
		
			// Display the list in HTML
			$output = "<h2>Top Keywords for <a href=\'$url\'>$url</a></h2><ul>";
			foreach($keywords as $keyword) {
				$output .= "<li>". $keyword[0]. "</li>";
			}
			if(empty($keywords)) { $output .= "Nothing yet..."; }
			$output .= "</ul>";
			echo $output;
		}
		DisplayTopKeywords($topPageUrl, $api);
		
		echo "</div><br/>
		<p>Here is the PHP function that you can paste in your pages:</P>
		<textarea cols=60 rows=8>&lt;?php\n" . htmlspecialchars($code) . "\n DisplayTopKeywords();</textarea>
		";
		
		echo "
		<p><b>Notes</b>: You can for example edit the code to to make the Top search keywords link to your Website search result pages.
		<br/>On medium to large traffic websites, we recommend to cache this data, as to minimize the performance impact of calling the Piwik API on each page view.
		</p>
		";
				
	}
	
}
