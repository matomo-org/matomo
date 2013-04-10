<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
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

        // building the referers summary report
        $view->dataTableRefererType = $this->getRefererType(true);

        $nameValues = $this->getReferersVisitorsByType();

        $totalVisits = array_sum($nameValues);
        foreach ($nameValues as $name => $value) {
            $view->$name = $value;

            // calculate percent of total, if there were any visits
            if ($value != 0
                && $totalVisits != 0
            ) {
                $percentName = $name . 'Percent';
                $view->$percentName = round(($value / $totalVisits) * 100, 0);
            }
        }

        // set distinct metrics
        $distinctMetrics = $this->getDistinctReferrersMetrics();
        foreach ($distinctMetrics as $name => $value) {
            $view->$name = $value;
        }

        // calculate evolution for visit metrics & distinct metrics
        list($lastPeriodDate, $ignore) = Piwik_Period_Range::getLastDate();
        if ($lastPeriodDate !== false) {
            $date = Piwik_Common::getRequestVar('date');
            $period = Piwik_Common::getRequestVar('period');

            $prettyDate = self::getPrettyDate($date, $period);
            $prettyLastPeriodDate = self::getPrettyDate($lastPeriodDate, $period);

            // visit metrics
            $previousValues = $this->getReferersVisitorsByType($lastPeriodDate);
            $this->addEvolutionPropertiesToView($view, $prettyDate, $nameValues, $prettyLastPeriodDate, $previousValues);

            // distinct metrics
            $previousValues = $this->getDistinctReferrersMetrics($lastPeriodDate);
            $this->addEvolutionPropertiesToView($view, $prettyDate, $distinctMetrics, $prettyLastPeriodDate, $previousValues);
        }

        // sparkline for the historical data of the above values
        $view->urlSparklineSearchEngines = $this->getReferrerUrlSparkline(Piwik_Common::REFERER_TYPE_SEARCH_ENGINE);
        $view->urlSparklineDirectEntry = $this->getReferrerUrlSparkline(Piwik_Common::REFERER_TYPE_DIRECT_ENTRY);
        $view->urlSparklineWebsites = $this->getReferrerUrlSparkline(Piwik_Common::REFERER_TYPE_WEBSITE);
        $view->urlSparklineCampaigns = $this->getReferrerUrlSparkline(Piwik_Common::REFERER_TYPE_CAMPAIGN);

        // sparklines for the evolution of the distinct keywords count/websites count/ etc
        $view->urlSparklineDistinctSearchEngines = $this->getUrlSparkline('getLastDistinctSearchEnginesGraph');
        $view->urlSparklineDistinctKeywords = $this->getUrlSparkline('getLastDistinctKeywordsGraph');
        $view->urlSparklineDistinctWebsites = $this->getUrlSparkline('getLastDistinctWebsitesGraph');
        $view->urlSparklineDistinctCampaigns = $this->getUrlSparkline('getLastDistinctCampaignsGraph');

        $view->totalVisits = $totalVisits;
        $view->referrersReportsByDimension = $this->getReferrersReportsByDimensionView($totalVisits);

        echo $view->render();
    }

    /**
     * Returns HTML for the Referrers Overview page that categorizes Referrer reports
     * & allows the user to switch between them.
     *
     * @param int $visits The number of visits for this period & site. If <= 0, the
     *                    reports are not shown, since they will have no data.
     * @return string The report viewer HTML.
     */
    private function getReferrersReportsByDimensionView($visits)
    {
        $result = '';

        // only display the reports by dimension view if there are visits
        if ($visits > 0) {
            $referrersReportsByDimension = new Piwik_View_ReportsByDimension();

            $referrersReportsByDimension->addReport(
                'Referers_ViewAllReferrers', 'Referers_WidgetGetAll', 'Referers.getAll');

            $byTypeCategory = Piwik_Translate('Referers_ViewReferrersBy', Piwik_Translate('Live_GoalType'));
            $referrersReportsByDimension->addReport(
                $byTypeCategory, 'Referers_WidgetKeywords', 'Referers.getKeywords');
            $referrersReportsByDimension->addReport($byTypeCategory, 'SitesManager_Sites', 'Referers.getWebsites');
            $referrersReportsByDimension->addReport($byTypeCategory, 'Referers_Campaigns', 'Referers.getCampaigns');

            $bySourceCategory = Piwik_Translate('Referers_ViewReferrersBy', Piwik_Translate('General_Source'));
            $referrersReportsByDimension->addReport($bySourceCategory, 'Referers_Socials', 'Referers.getSocials');
            $referrersReportsByDimension->addReport(
                $bySourceCategory, 'Referers_SearchEngines', 'Referers.getSearchEngines');

            $result = $referrersReportsByDimension->render();
        }

        return $result;
    }

    function getSearchEnginesAndKeywords()
    {
        $view = Piwik_View::factory('searchEngines_Keywords');
        $view->searchEngines = $this->getSearchEngines(true);
        $view->keywords = $this->getKeywords(true);
        echo $view->render();
    }

    function getRefererType($fetch = false)
    {
        $view = Piwik_ViewDataTable::factory('tableAllColumns');
        $view->init($this->pluginName,
            __FUNCTION__,
            'Referers.getRefererType',
            'getRefererType'
        );
        $view->disableSearchBox();
        $view->disableOffsetInformationAndPaginationControls();
        $view->disableExcludeLowPopulation();
        $view->disableSubTableWhenShowGoals();
        $view->enableShowGoals();
        $view->setLimit(10);
        $view->setColumnsToDisplay(array('label', 'nb_visits'));

        $idSubtable = Piwik_Common::getRequestVar('idSubtable', false);
        $labelColumnTitle = Piwik_Translate('Referers_ColumnRefererType');
        if ($idSubtable !== false) {
            switch ($idSubtable) {
                case Piwik_Common::REFERER_TYPE_SEARCH_ENGINE:
                    $labelColumnTitle = Piwik_Translate('Referers_ColumnSearchEngine');
                    break;
                case Piwik_Common::REFERER_TYPE_WEBSITE:
                    $labelColumnTitle = Piwik_Translate('Referers_ColumnWebsite');
                    break;
                case Piwik_Common::REFERER_TYPE_CAMPAIGN:
                    $labelColumnTitle = Piwik_Translate('Referers_ColumnCampaign');
                    break;
                default:
                    break;
            }
        }
        $view->setColumnTranslation('label', $labelColumnTitle);

        $this->setMetricsVariablesView($view);
        return $this->renderView($view, $fetch);
    }

    /**
     * Returns or echo's a report that shows all search keyword, website and campaign
     * referrer information in one report.
     *
     * @param bool $fetch True if the report HTML should be returned. If false, the
     *                    report is echo'd and nothing is returned.
     * @return string The report HTML or nothing if $fetch is set to false.
     */
    public function getAll($fetch = false)
    {
        $view = Piwik_ViewDataTable::factory();
        $view->init($this->pluginName, __FUNCTION__, 'Referers.getAll');
        $view->disableExcludeLowPopulation();
        $view->setColumnTranslation('label', Piwik_Translate('Referers_Referrer'));
        $view->setColumnsToDisplay(array('label', 'nb_visits'));
        $view->enableShowGoals();
        $view->setLimit(20);
        $view->setCustomParameter('disable_row_actions', '1');

        $setGetAllHtmlPrefix = array($this, 'setGetAllHtmlPrefix');
        $view->queueFilter(
            'MetadataCallbackAddMetadata', array('referrer_type', 'html_label_prefix', $setGetAllHtmlPrefix));

        $view->setMetricsVariablesView($view);

        return $this->renderView($view, $fetch);
    }

    /**
     * DataTable filter callback that returns the HTML prefix for a label in the
     * 'getAll' report based on the row's referrer type.
     *
     * @param int $referrerType The referrer type.
     * @return string
     */
    public function setGetAllHtmlPrefix($referrerType)
    {
        // get singular label for referrer type
        $indexTranslation = '';
        switch ($referrerType) {
            case Piwik_Common::REFERER_TYPE_DIRECT_ENTRY:
                $indexTranslation = 'Referers_DirectEntry';
                break;
            case Piwik_Common::REFERER_TYPE_SEARCH_ENGINE:
                $indexTranslation = 'Referers_ColumnKeyword';
                break;
            case Piwik_Common::REFERER_TYPE_WEBSITE:
                $indexTranslation = 'Referers_ColumnWebsite';
                break;
            case Piwik_Common::REFERER_TYPE_CAMPAIGN:
                $indexTranslation = 'Referers_ColumnCampaign';
                break;
            default:
                // case of newsletter, partners, before Piwik 0.2.25
                $indexTranslation = 'General_Others';
                break;
        }

        $label = strtolower(Piwik_Translate($indexTranslation));

        // return html that displays it as grey & italic
        return '<span style="color:#999"><em>(' . $label . ')</em></span>';
    }

    function getKeywords($fetch = false)
    {
        $view = Piwik_ViewDataTable::factory();
        $view->init($this->pluginName, __FUNCTION__,
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

    function getSearchEnginesFromKeywordId($fetch = false)
    {
        $view = Piwik_ViewDataTable::factory();
        $view->init($this->pluginName, __FUNCTION__,
            'Referers.getSearchEnginesFromKeywordId'
        );
        $view->disableSearchBox();
        $view->disableExcludeLowPopulation();
        $view->setColumnsToDisplay(array('label', 'nb_visits'));
        $view->setColumnTranslation('label', Piwik_Translate('Referers_ColumnSearchEngine'));
        return $this->renderView($view, $fetch);
    }


    function getSearchEngines($fetch = false)
    {
        $view = Piwik_ViewDataTable::factory();
        $view->init($this->pluginName, __FUNCTION__,
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

    function getKeywordsFromSearchEngineId($fetch = false)
    {
        $view = Piwik_ViewDataTable::factory();
        $view->init($this->pluginName, __FUNCTION__,
            'Referers.getKeywordsFromSearchEngineId'
        );
        $view->disableSearchBox();
        $view->disableExcludeLowPopulation();
        $view->setColumnsToDisplay(array('label', 'nb_visits'));
        $view->setColumnTranslation('label', Piwik_Translate('Referers_ColumnKeyword'));
        return $this->renderView($view, $fetch);
    }

    function indexWebsites($fetch = false)
    {
        $view = Piwik_View::factory('Websites_SocialNetworks');
        $view->websites = $this->getWebsites(true);
        $view->socials = $this->getSocials(true);
        if ($fetch) {
            return $view->render();
        } else {
            echo $view->render();
        }
    }

    function getWebsites($fetch = false)
    {
        $view = Piwik_ViewDataTable::factory();
        $view->init($this->pluginName, __FUNCTION__,
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

    function getSocials($fetch = false)
    {
        $view = Piwik_ViewDataTable::factory('graphPie');
        $view->init($this->pluginName, __FUNCTION__, 'Referers.getSocials', 'getUrlsForSocial');
        $view->disableExcludeLowPopulation();
        $view->setLimit(10);
        $view->enableShowGoals();
        $view->disableSubTableWhenShowGoals();
        $view->setColumnTranslation('label', Piwik_Translate('Referers_ColumnSocial'));

        if (empty($_REQUEST['widget'])) {
            $view->setFooterMessage(Piwik_Translate('Referers_SocialFooterMessage'));
        }

        $this->setMetricsVariablesView($view);

        return $this->renderView($view, $fetch);
    }

    function getUrlsForSocial($fetch = false)
    {
        $view = Piwik_ViewDataTable::factory();
        $view->init($this->pluginName, __FUNCTION__, 'Referers.getUrlsForSocial');
        $view->disableExcludeLowPopulation();
        $view->setLimit(10);
        $view->enableShowGoals();
        $view->setColumnTranslation('label', Piwik_Translate('Referers_ColumnWebsitePage'));

        $this->setMetricsVariablesView($view);

        return $this->renderView($view, $fetch);
    }

    function indexCampaigns($fetch = false)
    {
        return Piwik_View::singleReport(
            Piwik_Translate('Referers_Campaigns'),
            $this->getCampaigns(true), $fetch);
    }

    function getCampaigns($fetch = false)
    {
        $view = Piwik_ViewDataTable::factory();
        $view->init($this->pluginName, __FUNCTION__,
            'Referers.getCampaigns',
            'getKeywordsFromCampaignId'
        );
        $view->disableExcludeLowPopulation();
        $view->enableShowGoals();
        $view->setLimit(25);
        $view->setColumnsToDisplay(array('label', 'nb_visits'));
        $view->setColumnTranslation('label', Piwik_Translate('Referers_ColumnCampaign'));

        $help = Piwik_Translate('Referers_CampaignFooterHelp', array('<a target="_blank" href="http://piwik.org/docs/tracking-campaigns/">',
                                                                     '</a> - <a target="_blank" href="http://piwik.org/docs/tracking-campaigns/url-builder/">',
                                                                     '</a>'
                                                               ));
        $view->setFooterMessage($help);
        $this->setMetricsVariablesView($view);
        return $this->renderView($view, $fetch);
    }

    function getKeywordsFromCampaignId($fetch = false)
    {
        $view = Piwik_ViewDataTable::factory();
        $view->init($this->pluginName, __FUNCTION__,
            'Referers.getKeywordsFromCampaignId'
        );

        $view->disableSearchBox();
        $view->disableExcludeLowPopulation();
        $view->setColumnsToDisplay(array('label', 'nb_visits'));
        $view->setColumnTranslation('label', Piwik_Translate('Referers_ColumnKeyword'));

        return $this->renderView($view, $fetch);
    }

    function getUrlsFromWebsiteId($fetch = false)
    {
        $view = Piwik_ViewDataTable::factory();
        $view->init($this->pluginName, __FUNCTION__,
            'Referers.getUrlsFromWebsiteId'
        );
        $view->disableSearchBox();
        $view->disableExcludeLowPopulation();
        $view->setColumnsToDisplay(array('label', 'nb_visits'));
        $view->setColumnTranslation('label', Piwik_Translate('Referers_ColumnWebsitePage'));
        $view->setTooltipMetadataName('url');
        return $this->renderView($view, $fetch);
    }

    protected function getReferersVisitorsByType($date = false)
    {
        if ($date === false) {
            $date = Piwik_Common::getRequestVar('date', false);
        }

        // we disable the queued filters because here we want to get the visits coming from search engines
        // if the filters were applied we would have to look up for a label looking like "Search Engines"
        // which is not good when we have translations
        $dataTableReferersType = Piwik_API_Request::processRequest(
            "Referers.getRefererType", array('disable_queued_filters' => '1', 'date' => $date));

        $nameToColumnId = array(
            'visitorsFromSearchEngines' => Piwik_Common::REFERER_TYPE_SEARCH_ENGINE,
            'visitorsFromDirectEntry'   => Piwik_Common::REFERER_TYPE_DIRECT_ENTRY,
            'visitorsFromWebsites'      => Piwik_Common::REFERER_TYPE_WEBSITE,
            'visitorsFromCampaigns'     => Piwik_Common::REFERER_TYPE_CAMPAIGN,
        );
        $return = array();
        foreach ($nameToColumnId as $nameVar => $columnId) {
            $value = 0;
            $row = $dataTableReferersType->getRowFromLabel($columnId);
            if ($row !== false) {
                $value = $row->getColumn(Piwik_Archive::INDEX_NB_VISITS);
            }
            $return[$nameVar] = $value;
        }
        return $return;
    }

    protected $referrerTypeToLabel = array(
        Piwik_Common::REFERER_TYPE_DIRECT_ENTRY  => 'Referers_DirectEntry',
        Piwik_Common::REFERER_TYPE_SEARCH_ENGINE => 'Referers_SearchEngines',
        Piwik_Common::REFERER_TYPE_WEBSITE       => 'Referers_Websites',
        Piwik_Common::REFERER_TYPE_CAMPAIGN      => 'Referers_Campaigns',
    );

    public function getEvolutionGraph($fetch = false, $typeReferer = false, array $columns = array())
    {
        $view = $this->getLastUnitGraph($this->pluginName, __FUNCTION__, 'Referers.getRefererType');

        $view->addTotalRow();

        // configure displayed columns
        if (empty($columns)) {
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
        if ($visibleRows !== false) {
            // this happens when the row picker has been used
            $visibleRows = Piwik::getArrayFromApiParameter($visibleRows);

            // typeReferer is redundant if rows are defined, so make sure it's not used
            $view->setCustomParameter('typeReferer', false);
        } else {
            // use $typeReferer as default
            if ($typeReferer === false) {
                $typeReferer = Piwik_Common::getRequestVar('typeReferer', false);
            }
            $label = self::getTranslatedReferrerTypeLabel($typeReferer);
            $total = Piwik_Translate('General_Total');
            $visibleRows = array($label, $total);
            $view->setParametersToModify(array('rows' => $label . ',' . $total));
        }
        $view->addRowPicker($visibleRows);

        $view->setReportDocumentation(Piwik_Translate('Referers_EvolutionDocumentation') . '<br />'
            . Piwik_Translate('General_BrokenDownReportDocumentation') . '<br />'
            . Piwik_Translate('Referers_EvolutionDocumentationMoreInfo', '&quot;' . Piwik_Translate('Referers_DetailsByRefererType') . '&quot;'));

        return $this->renderView($view, $fetch);
    }

    function getLastDistinctSearchEnginesGraph($fetch = false)
    {
        $view = $this->getLastUnitGraph($this->pluginName, __FUNCTION__, "Referers.getNumberOfDistinctSearchEngines");
        $view->setColumnTranslation('Referers_distinctSearchEngines', ucfirst(Piwik_Translate('Referers_DistinctSearchEngines')));
        $view->setColumnsToDisplay(array('Referers_distinctSearchEngines'));
        return $this->renderView($view, $fetch);
    }

    function getLastDistinctKeywordsGraph($fetch = false)
    {
        $view = $this->getLastUnitGraph($this->pluginName, __FUNCTION__, "Referers.getNumberOfDistinctKeywords");
        $view->setColumnTranslation('Referers_distinctKeywords', ucfirst(Piwik_Translate('Referers_DistinctKeywords')));
        $view->setColumnsToDisplay(array('Referers_distinctKeywords'));
        return $this->renderView($view, $fetch);
    }

    function getLastDistinctWebsitesGraph($fetch = false)
    {
        $view = $this->getLastUnitGraph($this->pluginName, __FUNCTION__, "Referers.getNumberOfDistinctWebsites");
        $view->setColumnTranslation('Referers_distinctWebsites', ucfirst(Piwik_Translate('Referers_DistinctWebsites')));
        $view->setColumnsToDisplay(array('Referers_distinctWebsites'));
        return $this->renderView($view, $fetch);
    }

    function getLastDistinctCampaignsGraph($fetch = false)
    {
        $view = $this->getLastUnitGraph($this->pluginName, __FUNCTION__, "Referers.getNumberOfDistinctCampaigns");
        $view->setColumnTranslation('Referers_distinctCampaigns', ucfirst(Piwik_Translate('Referers_DistinctCampaigns')));
        $view->setColumnsToDisplay(array('Referers_distinctCampaigns'));
        return $this->renderView($view, $fetch);
    }

    function getKeywordsForPage()
    {
        Piwik::checkUserHasViewAccess($this->idSite);

        $requestUrl = '&date=previous1'
            . '&period=week'
            . '&idSite=' . $this->idSite;

        $topPageUrlRequest = $requestUrl
            . '&method=Actions.getPageUrls'
            . '&filter_limit=50'
            . '&format=original';
        $request = new Piwik_API_Request($topPageUrlRequest);
        $request = $request->process();
        $tables = $request->getArray();

        $topPageUrl = false;
        $first = key($tables);
        if (!empty($first)) {
            $topPageUrls = $tables[$first];
            $topPageUrls = $topPageUrls->getRowsMetadata('url');
            $tmpTopPageUrls = array_values($topPageUrls);
            $topPageUrl = current($tmpTopPageUrls);
        }
        if (empty($topPageUrl)) {
            $topPageUrl = $this->site->getMainUrl();
        }
        $url = $topPageUrl;

        // HTML
        $api = Piwik_Url::getCurrentUrlWithoutFileName()
            . '?module=API&method=Referers.getKeywordsForPageUrl'
            . '&format=php'
            . '&filter_limit=10'
            . '&token_auth=' . Piwik::getCurrentUserTokenAuth();

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
	$api = "' . $api . '&url=" . urlencode($url);
	$keywords = @unserialize(file_get_contents($api));
	if($keywords === false || isset($keywords["result"])) {
		// DEBUG ONLY: uncomment for troubleshooting an empty output (the URL output reveals the token_auth)
		// echo "Error while fetching the <a href=\'$api\'>Top Keywords from Piwik</a>";
		return;
	}

	// Display the list in HTML
	$url = htmlspecialchars($url, ENT_QUOTES);
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
		in format JSON: you would dynamically fetch the data using <a target='_blank' href='$jsonRequest&url=" . urlencode($topPageUrl) . "'>this API request URL</a>. Make sure you encode the 'url' parameter in the URL.</p>
		
		<p><b>PHP Function ready to use!</b> - If you use PHP on your website, we have prepared a small code snippet that you can copy paste in your Website PHP files. You can then simply call the function <code>DisplayTopKeywords();</code> anywhere in your template, at the bottom of the content or in your blog sidebar.
		If you run this code in your page $topPageUrl, it would output the following:";

        echo "<div style='width:400px;margin-left:20px;padding:10px;border:1px solid black;'>";
        function DisplayTopKeywords($url = "", $api)
        {
            // Do not spend more than 1 second fetching the data
            @ini_set("default_socket_timeout", $timeout = 1);
            // Get the Keywords data
            $url = empty($url) ? "http://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"] : $url;
            $api = $api . "&url=" . urlencode($url);
            $keywords = @unserialize(file_get_contents($api));
            if ($keywords === false || isset($keywords["result"])) {
                // DEBUG ONLY: uncomment for troubleshooting an empty output (the URL output reveals the token_auth)
                //echo "Error while fetching the <a href=\'".$api."\'>Top Keywords from Piwik</a>";
                return;
            }

            // Display the list in HTML
            $url = htmlspecialchars($url, ENT_QUOTES);
            $output = "<h2>Top Keywords for <a href=\'$url\'>$url</a></h2><ul>";
            foreach ($keywords as $keyword) {
                $output .= "<li>" . $keyword[0] . "</li>";
            }
            if (empty($keywords)) {
                $output .= "Nothing yet...";
            }
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

    /**
     * Returns the i18n-ized label for a referrer type.
     *
     * @param int $typeReferrer The referrer type. Referrer types are defined in Piwik_Common class.
     * @return string The i18n-ized label.
     */
    public static function getTranslatedReferrerTypeLabel($typeReferrer)
    {
        $label = Piwik_getRefererTypeLabel($typeReferrer);
        return Piwik_Translate($label);
    }

    /**
     * Returns the URL for the sparkline of visits with a specific referrer type.
     *
     * @param int $typeReferrer The referrer type. Referrer types are defined in Piwik_Common class.
     * @return string The URL that can be used to get a sparkline image.
     */
    private function getReferrerUrlSparkline($referrerType)
    {
        $totalRow = Piwik_Translate('General_Total');
        return $this->getUrlSparkline(
            'getEvolutionGraph',
            array('columns'     => array('nb_visits'),
                  'rows'        => array(self::getTranslatedReferrerTypeLabel($referrerType), $totalRow),
                  'typeReferer' => $referrerType)
        );
    }

    /**
     * Returns an array containing the number of distinct referrers for each
     * referrer type.
     *
     * @param string|false $date The date to use when getting metrics. If false, the
     *                           date query param is used.
     * @return array The metrics.
     */
    private function getDistinctReferrersMetrics($date = false)
    {
        $propertyToAccessorMapping = array(
            'numberDistinctSearchEngines' => 'getNumberOfDistinctSearchEngines',
            'numberDistinctKeywords'      => 'getNumberOfDistinctKeywords',
            'numberDistinctWebsites'      => 'getNumberOfDistinctWebsites',
            'numberDistinctWebsitesUrls'  => 'getNumberOfDistinctWebsitesUrls',
            'numberDistinctCampaigns'     => 'getNumberOfDistinctCampaigns',
        );

        $result = array();
        foreach ($propertyToAccessorMapping as $property => $method) {
            $result[$property] = $this->getNumericValue('Referers.' . $method, $date);
        }
        return $result;
    }

    /**
     * Utility method that calculates evolution values for a set of current & past values
     * and sets properties on a Piwik_View w/ HTML that displays the evolution percents.
     *
     * @param Piwik_View $view The view to set properties on.
     * @param string $date The date of the current values.
     * @param array $currentValues Array mapping view property names w/ present values.
     * @param string $lastPeriodDate The date of the period in the past.
     * @param array $previousValues Array mapping view property names w/ past values. Keys
     *                              in this array should be the same as keys in $currentValues.
     * @param bool $isVisits Whether the values are counting visits or something else.
     */
    private function addEvolutionPropertiesToView($view, $date, $currentValues, $lastPeriodDate, $previousValues)
    {
        foreach ($previousValues as $name => $pastValue) {
            $currentValue = $currentValues[$name];
            $evolutionName = $name . 'Evolution';

            $view->$evolutionName = $this->getEvolutionHtml($date, $currentValue, $lastPeriodDate, $pastValue);
        }
    }
}
