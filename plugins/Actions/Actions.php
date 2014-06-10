<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Actions;

use Piwik\API\Request;
use Piwik\ArchiveProcessor;
use Piwik\Common;
use Piwik\Db;
use Piwik\MetricsFormatter;
use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\CoreVisualizations\Visualizations\HtmlTable;
use Piwik\Site;

/**
 * Actions plugin
 *
 * Reports about the page views, the outlinks and downloads.
 *
 */
class Actions extends \Piwik\Plugin
{
    const ACTIONS_REPORT_ROWS_DISPLAY = 100;

    /**
     * @see Piwik\Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
    {
        $hooks = array(
            'API.getReportMetadata'           => 'getReportMetadata',
            'API.getSegmentDimensionMetadata' => 'getSegmentsMetadata',
            'ViewDataTable.configure'         => 'configureViewDataTable',
            'AssetManager.getStylesheetFiles' => 'getStylesheetFiles',
            'AssetManager.getJavaScriptFiles' => 'getJsFiles',
            'Insights.addReportToOverview'    => 'addReportToInsightsOverview'
        );
        return $hooks;
    }

    public function addReportToInsightsOverview(&$reports)
    {
        $reports['Actions_getPageUrls']   = array();
        $reports['Actions_getPageTitles'] = array();
        $reports['Actions_getDownloads']  = array('flat' => 1);
    }

    public function getStylesheetFiles(&$stylesheets)
    {
        $stylesheets[] = "plugins/Actions/stylesheets/dataTableActions.less";
    }

    public function getJsFiles(&$jsFiles)
    {
        $jsFiles[] = "plugins/Actions/javascripts/actionsDataTable.js";
    }

    public function getSegmentsMetadata(&$segments)
    {
        $sqlFilter = '\\Piwik\\Tracker\\TableLogAction::getIdActionFromSegment';

        // entry and exit pages of visit
        $segments[] = array(
            'type'       => 'dimension',
            'category'   => 'General_Actions',
            'name'       => 'Actions_ColumnEntryPageURL',
            'segment'    => 'entryPageUrl',
            'sqlSegment' => 'log_visit.visit_entry_idaction_url',
            'sqlFilter'  => $sqlFilter,
        );
        $segments[] = array(
            'type'       => 'dimension',
            'category'   => 'General_Actions',
            'name'       => 'Actions_ColumnEntryPageTitle',
            'segment'    => 'entryPageTitle',
            'sqlSegment' => 'log_visit.visit_entry_idaction_name',
            'sqlFilter'  => $sqlFilter,
        );
        $segments[] = array(
            'type'       => 'dimension',
            'category'   => 'General_Actions',
            'name'       => 'Actions_ColumnExitPageURL',
            'segment'    => 'exitPageUrl',
            'sqlSegment' => 'log_visit.visit_exit_idaction_url',
            'sqlFilter'  => $sqlFilter,
        );
        $segments[] = array(
            'type'       => 'dimension',
            'category'   => 'General_Actions',
            'name'       => 'Actions_ColumnExitPageTitle',
            'segment'    => 'exitPageTitle',
            'sqlSegment' => 'log_visit.visit_exit_idaction_name',
            'sqlFilter'  => $sqlFilter,
        );

        // single pages
        $segments[] = array(
            'type'           => 'dimension',
            'category'       => 'General_Actions',
            'name'           => 'Actions_ColumnPageURL',
            'segment'        => 'pageUrl',
            'sqlSegment'     => 'log_link_visit_action.idaction_url',
            'sqlFilter'      => $sqlFilter,
            'acceptedValues' => "All these segments must be URL encoded, for example: " . urlencode('http://example.com/path/page?query'),
        );
        $segments[] = array(
            'type'       => 'dimension',
            'category'   => 'General_Actions',
            'name'       => 'Actions_ColumnPageName',
            'segment'    => 'pageTitle',
            'sqlSegment' => 'log_link_visit_action.idaction_name',
            'sqlFilter'  => $sqlFilter,
        );
        $segments[] = array(
            'type'       => 'dimension',
            'category'   => 'General_Actions',
            'name'       => 'Actions_SiteSearchKeyword',
            'segment'    => 'siteSearchKeyword',
            'sqlSegment' => 'log_link_visit_action.idaction_name',
            'sqlFilter'  => $sqlFilter,
        );
    }

    public function getReportMetadata(&$reports)
    {
        $reports[] = array(
            'category'             => Piwik::translate('General_Actions'),
            'name'                 => Piwik::translate('General_Actions') . ' - ' . Piwik::translate('General_MainMetrics'),
            'module'               => 'Actions',
            'action'               => 'get',
            'metrics'              => array(
                'nb_pageviews'        => Piwik::translate('General_ColumnPageviews'),
                'nb_uniq_pageviews'   => Piwik::translate('General_ColumnUniquePageviews'),
                'nb_downloads'        => Piwik::translate('General_Downloads'),
                'nb_uniq_downloads'   => Piwik::translate('Actions_ColumnUniqueDownloads'),
                'nb_outlinks'         => Piwik::translate('General_Outlinks'),
                'nb_uniq_outlinks'    => Piwik::translate('Actions_ColumnUniqueOutlinks'),
                'nb_searches'         => Piwik::translate('Actions_ColumnSearches'),
                'nb_keywords'         => Piwik::translate('Actions_ColumnSiteSearchKeywords'),
                'avg_time_generation' => Piwik::translate('General_ColumnAverageGenerationTime'),
            ),
            'metricsDocumentation' => array(
                'nb_pageviews'        => Piwik::translate('General_ColumnPageviewsDocumentation'),
                'nb_uniq_pageviews'   => Piwik::translate('General_ColumnUniquePageviewsDocumentation'),
                'nb_downloads'        => Piwik::translate('Actions_ColumnClicksDocumentation'),
                'nb_uniq_downloads'   => Piwik::translate('Actions_ColumnUniqueClicksDocumentation'),
                'nb_outlinks'         => Piwik::translate('Actions_ColumnClicksDocumentation'),
                'nb_uniq_outlinks'    => Piwik::translate('Actions_ColumnUniqueClicksDocumentation'),
                'nb_searches'         => Piwik::translate('Actions_ColumnSearchesDocumentation'),
                'avg_time_generation' => Piwik::translate('General_ColumnAverageGenerationTimeDocumentation'),
//				'nb_keywords' => Piwik::translate('Actions_ColumnSiteSearchKeywords'),
            ),
            'processedMetrics'     => false,
            'order'                => 1
        );

        $metrics = array(
            'nb_hits'             => Piwik::translate('General_ColumnPageviews'),
            'nb_visits'           => Piwik::translate('General_ColumnUniquePageviews'),
            'bounce_rate'         => Piwik::translate('General_ColumnBounceRate'),
            'avg_time_on_page'    => Piwik::translate('General_ColumnAverageTimeOnPage'),
            'exit_rate'           => Piwik::translate('General_ColumnExitRate'),
            'avg_time_generation' => Piwik::translate('General_ColumnAverageGenerationTime')
        );

        $documentation = array(
            'nb_hits'             => Piwik::translate('General_ColumnPageviewsDocumentation'),
            'nb_visits'           => Piwik::translate('General_ColumnUniquePageviewsDocumentation'),
            'bounce_rate'         => Piwik::translate('General_ColumnPageBounceRateDocumentation'),
            'avg_time_on_page'    => Piwik::translate('General_ColumnAverageTimeOnPageDocumentation'),
            'exit_rate'           => Piwik::translate('General_ColumnExitRateDocumentation'),
            'avg_time_generation' => Piwik::translate('General_ColumnAverageGenerationTimeDocumentation'),
        );

        // pages report
        $reports[] = array(
            'category'              => Piwik::translate('General_Actions'),
            'name'                  => Piwik::translate('Actions_PageUrls'),
            'module'                => 'Actions',
            'action'                => 'getPageUrls',
            'dimension'             => Piwik::translate('Actions_ColumnPageURL'),
            'metrics'               => $metrics,
            'metricsDocumentation'  => $documentation,
            'documentation'         => Piwik::translate('Actions_PagesReportDocumentation', '<br />')
                . '<br />' . Piwik::translate('General_UsePlusMinusIconsDocumentation'),
            'processedMetrics'      => false,
            'actionToLoadSubTables' => 'getPageUrls',
            'order'                 => 2
        );

        // entry pages report
        $reports[] = array(
            'category'              => Piwik::translate('General_Actions'),
            'name'                  => Piwik::translate('Actions_SubmenuPagesEntry'),
            'module'                => 'Actions',
            'action'                => 'getEntryPageUrls',
            'dimension'             => Piwik::translate('Actions_ColumnPageURL'),
            'metrics'               => array(
                'entry_nb_visits'    => Piwik::translate('General_ColumnEntrances'),
                'entry_bounce_count' => Piwik::translate('General_ColumnBounces'),
                'bounce_rate'        => Piwik::translate('General_ColumnBounceRate'),
            ),
            'metricsDocumentation'  => array(
                'entry_nb_visits'    => Piwik::translate('General_ColumnEntrancesDocumentation'),
                'entry_bounce_count' => Piwik::translate('General_ColumnBouncesDocumentation'),
                'bounce_rate'        => Piwik::translate('General_ColumnBounceRateForPageDocumentation')
            ),
            'documentation'         => Piwik::translate('Actions_EntryPagesReportDocumentation', '<br />')
                . ' ' . Piwik::translate('General_UsePlusMinusIconsDocumentation'),
            'processedMetrics'      => false,
            'actionToLoadSubTables' => 'getEntryPageUrls',
            'order'                 => 3
        );

        // exit pages report
        $reports[] = array(
            'category'              => Piwik::translate('General_Actions'),
            'name'                  => Piwik::translate('Actions_SubmenuPagesExit'),
            'module'                => 'Actions',
            'action'                => 'getExitPageUrls',
            'dimension'             => Piwik::translate('Actions_ColumnPageURL'),
            'metrics'               => array(
                'exit_nb_visits' => Piwik::translate('General_ColumnExits'),
                'nb_visits'      => Piwik::translate('General_ColumnUniquePageviews'),
                'exit_rate'      => Piwik::translate('General_ColumnExitRate')
            ),
            'metricsDocumentation'  => array(
                'exit_nb_visits' => Piwik::translate('General_ColumnExitsDocumentation'),
                'nb_visits'      => Piwik::translate('General_ColumnUniquePageviewsDocumentation'),
                'exit_rate'      => Piwik::translate('General_ColumnExitRateDocumentation')
            ),
            'documentation'         => Piwik::translate('Actions_ExitPagesReportDocumentation', '<br />')
                . ' ' . Piwik::translate('General_UsePlusMinusIconsDocumentation'),
            'processedMetrics'      => false,
            'actionToLoadSubTables' => 'getExitPageUrls',
            'order'                 => 4
        );

        // page titles report
        $reports[] = array(
            'category'              => Piwik::translate('General_Actions'),
            'name'                  => Piwik::translate('Actions_SubmenuPageTitles'),
            'module'                => 'Actions',
            'action'                => 'getPageTitles',
            'dimension'             => Piwik::translate('Actions_ColumnPageName'),
            'metrics'               => $metrics,
            'metricsDocumentation'  => $documentation,
            'documentation'         => Piwik::translate('Actions_PageTitlesReportDocumentation', array('<br />', htmlentities('<title>'))),
            'processedMetrics'      => false,
            'actionToLoadSubTables' => 'getPageTitles',
            'order'                 => 5,

        );

        // entry page titles report
        $reports[] = array(
            'category'              => Piwik::translate('General_Actions'),
            'name'                  => Piwik::translate('Actions_EntryPageTitles'),
            'module'                => 'Actions',
            'action'                => 'getEntryPageTitles',
            'dimension'             => Piwik::translate('Actions_ColumnPageName'),
            'metrics'               => array(
                'entry_nb_visits'    => Piwik::translate('General_ColumnEntrances'),
                'entry_bounce_count' => Piwik::translate('General_ColumnBounces'),
                'bounce_rate'        => Piwik::translate('General_ColumnBounceRate'),
            ),
            'metricsDocumentation'  => array(
                'entry_nb_visits'    => Piwik::translate('General_ColumnEntrancesDocumentation'),
                'entry_bounce_count' => Piwik::translate('General_ColumnBouncesDocumentation'),
                'bounce_rate'        => Piwik::translate('General_ColumnBounceRateForPageDocumentation')
            ),
            'documentation'         => Piwik::translate('Actions_ExitPageTitlesReportDocumentation', '<br />')
                . ' ' . Piwik::translate('General_UsePlusMinusIconsDocumentation'),
            'processedMetrics'      => false,
            'actionToLoadSubTables' => 'getEntryPageTitles',
            'order'                 => 6
        );

        // exit page titles report
        $reports[] = array(
            'category'              => Piwik::translate('General_Actions'),
            'name'                  => Piwik::translate('Actions_ExitPageTitles'),
            'module'                => 'Actions',
            'action'                => 'getExitPageTitles',
            'dimension'             => Piwik::translate('Actions_ColumnPageName'),
            'metrics'               => array(
                'exit_nb_visits' => Piwik::translate('General_ColumnExits'),
                'nb_visits'      => Piwik::translate('General_ColumnUniquePageviews'),
                'exit_rate'      => Piwik::translate('General_ColumnExitRate')
            ),
            'metricsDocumentation'  => array(
                'exit_nb_visits' => Piwik::translate('General_ColumnExitsDocumentation'),
                'nb_visits'      => Piwik::translate('General_ColumnUniquePageviewsDocumentation'),
                'exit_rate'      => Piwik::translate('General_ColumnExitRateDocumentation')
            ),
            'documentation'         => Piwik::translate('Actions_EntryPageTitlesReportDocumentation', '<br />')
                . ' ' . Piwik::translate('General_UsePlusMinusIconsDocumentation'),
            'processedMetrics'      => false,
            'actionToLoadSubTables' => 'getExitPageTitles',
            'order'                 => 7
        );

        $documentation = array(
            'nb_visits' => Piwik::translate('Actions_ColumnUniqueClicksDocumentation'),
            'nb_hits'   => Piwik::translate('Actions_ColumnClicksDocumentation')
        );

        // outlinks report
        $reports[] = array(
            'category'              => Piwik::translate('General_Actions'),
            'name'                  => Piwik::translate('General_Outlinks'),
            'module'                => 'Actions',
            'action'                => 'getOutlinks',
            'dimension'             => Piwik::translate('Actions_ColumnClickedURL'),
            'metrics'               => array(
                'nb_visits' => Piwik::translate('Actions_ColumnUniqueClicks'),
                'nb_hits'   => Piwik::translate('Actions_ColumnClicks')
            ),
            'metricsDocumentation'  => $documentation,
            'documentation'         => Piwik::translate('Actions_OutlinksReportDocumentation') . ' '
                . Piwik::translate('Actions_OutlinkDocumentation') . '<br />'
                . Piwik::translate('General_UsePlusMinusIconsDocumentation'),
            'processedMetrics'      => false,
            'actionToLoadSubTables' => 'getOutlinks',
            'order'                 => 8,
        );

        // downloads report
        $reports[] = array(
            'category'              => Piwik::translate('General_Actions'),
            'name'                  => Piwik::translate('General_Downloads'),
            'module'                => 'Actions',
            'action'                => 'getDownloads',
            'dimension'             => Piwik::translate('Actions_ColumnDownloadURL'),
            'metrics'               => array(
                'nb_visits' => Piwik::translate('Actions_ColumnUniqueDownloads'),
                'nb_hits'   => Piwik::translate('General_Downloads')
            ),
            'metricsDocumentation'  => $documentation,
            'documentation'         => Piwik::translate('Actions_DownloadsReportDocumentation', '<br />'),
            'processedMetrics'      => false,
            'actionToLoadSubTables' => 'getDownloads',
            'order'                 => 9,
        );

        if ($this->isSiteSearchEnabled()) {
            // Search Keywords
            $reports[] = array(
                'category'             => Piwik::translate('Actions_SubmenuSitesearch'),
                'name'                 => Piwik::translate('Actions_WidgetSearchKeywords'),
                'module'               => 'Actions',
                'action'               => 'getSiteSearchKeywords',
                'dimension'            => Piwik::translate('General_ColumnKeyword'),
                'metrics'              => array(
                    'nb_visits'           => Piwik::translate('Actions_ColumnSearches'),
                    'nb_pages_per_search' => Piwik::translate('Actions_ColumnPagesPerSearch'),
                    'exit_rate'           => Piwik::translate('Actions_ColumnSearchExits'),
                ),
                'metricsDocumentation' => array(
                    'nb_visits'           => Piwik::translate('Actions_ColumnSearchesDocumentation'),
                    'nb_pages_per_search' => Piwik::translate('Actions_ColumnPagesPerSearchDocumentation'),
                    'exit_rate'           => Piwik::translate('Actions_ColumnSearchExitsDocumentation'),
                ),
                'documentation'        => Piwik::translate('Actions_SiteSearchKeywordsDocumentation') . '<br/><br/>' . Piwik::translate('Actions_SiteSearchIntro') . '<br/><br/>'
                    . '<a href="http://piwik.org/docs/site-search/" target="_blank">' . Piwik::translate('Actions_LearnMoreAboutSiteSearchLink') . '</a>',
                'processedMetrics'     => false,
                'order'                => 15
            );
            // No Result Search Keywords
            $reports[] = array(
                'category'             => Piwik::translate('Actions_SubmenuSitesearch'),
                'name'                 => Piwik::translate('Actions_WidgetSearchNoResultKeywords'),
                'module'               => 'Actions',
                'action'               => 'getSiteSearchNoResultKeywords',
                'dimension'            => Piwik::translate('Actions_ColumnNoResultKeyword'),
                'metrics'              => array(
                    'nb_visits' => Piwik::translate('Actions_ColumnSearches'),
                    'exit_rate' => Piwik::translate('Actions_ColumnSearchExits'),
                ),
                'metricsDocumentation' => array(
                    'nb_visits' => Piwik::translate('Actions_ColumnSearchesDocumentation'),
                    'exit_rate' => Piwik::translate('Actions_ColumnSearchExitsDocumentation'),
                ),
                'documentation'        => Piwik::translate('Actions_SiteSearchIntro') . '<br /><br />' . Piwik::translate('Actions_SiteSearchKeywordsNoResultDocumentation'),
                'processedMetrics'     => false,
                'order'                => 16
            );

            if (self::isCustomVariablesPluginsEnabled()) {
                // Search Categories
                $reports[] = array(
                    'category'             => Piwik::translate('Actions_SubmenuSitesearch'),
                    'name'                 => Piwik::translate('Actions_WidgetSearchCategories'),
                    'module'               => 'Actions',
                    'action'               => 'getSiteSearchCategories',
                    'dimension'            => Piwik::translate('Actions_ColumnSearchCategory'),
                    'metrics'              => array(
                        'nb_visits'           => Piwik::translate('Actions_ColumnSearches'),
                        'nb_pages_per_search' => Piwik::translate('Actions_ColumnPagesPerSearch'),
                        'exit_rate'           => Piwik::translate('Actions_ColumnSearchExits'),
                    ),
                    'metricsDocumentation' => array(
                        'nb_visits'           => Piwik::translate('Actions_ColumnSearchesDocumentation'),
                        'nb_pages_per_search' => Piwik::translate('Actions_ColumnPagesPerSearchDocumentation'),
                        'exit_rate'           => Piwik::translate('Actions_ColumnSearchExitsDocumentation'),
                    ),
                    'documentation'        => Piwik::translate('Actions_SiteSearchCategories1') . '<br/>' . Piwik::translate('Actions_SiteSearchCategories2'),
                    'processedMetrics'     => false,
                    'order'                => 17
                );
            }

            $documentation = Piwik::translate('Actions_SiteSearchFollowingPagesDoc') . '<br/>' . Piwik::translate('General_UsePlusMinusIconsDocumentation');
            // Pages URLs following Search
            $reports[] = array(
                'category'             => Piwik::translate('Actions_SubmenuSitesearch'),
                'name'                 => Piwik::translate('Actions_WidgetPageUrlsFollowingSearch'),
                'module'               => 'Actions',
                'action'               => 'getPageUrlsFollowingSiteSearch',
                'dimension'            => Piwik::translate('General_ColumnDestinationPage'),
                'metrics'              => array(
                    'nb_hits_following_search' => Piwik::translate('General_ColumnViewedAfterSearch'),
                    'nb_hits'                  => Piwik::translate('General_ColumnTotalPageviews'),
                ),
                'metricsDocumentation' => array(
                    'nb_hits_following_search' => Piwik::translate('General_ColumnViewedAfterSearchDocumentation'),
                    'nb_hits'                  => Piwik::translate('General_ColumnPageviewsDocumentation'),
                ),
                'documentation'        => $documentation,
                'processedMetrics'     => false,
                'order'                => 18
            );
            // Pages Titles following Search
            $reports[] = array(
                'category'             => Piwik::translate('Actions_SubmenuSitesearch'),
                'name'                 => Piwik::translate('Actions_WidgetPageTitlesFollowingSearch'),
                'module'               => 'Actions',
                'action'               => 'getPageTitlesFollowingSiteSearch',
                'dimension'            => Piwik::translate('General_ColumnDestinationPage'),
                'metrics'              => array(
                    'nb_hits_following_search' => Piwik::translate('General_ColumnViewedAfterSearch'),
                    'nb_hits'                  => Piwik::translate('General_ColumnTotalPageviews'),
                ),
                'metricsDocumentation' => array(
                    'nb_hits_following_search' => Piwik::translate('General_ColumnViewedAfterSearchDocumentation'),
                    'nb_hits'                  => Piwik::translate('General_ColumnPageviewsDocumentation'),
                ),
                'documentation'        => $documentation,
                'processedMetrics'     => false,
                'order'                => 19
            );
        }
    }

    public function isSiteSearchEnabled()
    {
        $idSite  = Common::getRequestVar('idSite', 0, 'int');
        $idSites = Common::getRequestVar('idSites', '', 'string');
        $idSites = Site::getIdSitesFromIdSitesString($idSites, true);

        if (!empty($idSite)) {
            $idSites[] = $idSite;
        }

        if (empty($idSites)) {
            return false;
        }

        foreach ($idSites as $idSite) {
            if (!Site::isSiteSearchEnabledFor($idSite)) {
                return false;
            }
        }

        return true;
    }

    static public function checkCustomVariablesPluginEnabled()
    {
        if (!self::isCustomVariablesPluginsEnabled()) {
            throw new \Exception("To Track Site Search Categories, please ask the Piwik Administrator to enable the 'Custom Variables' plugin in Settings > Plugins.");
        }
    }

    static public function isCustomVariablesPluginsEnabled()
    {
        return \Piwik\Plugin\Manager::getInstance()->isPluginActivated('CustomVariables');
    }

    public function configureViewDataTable(ViewDataTable $view)
    {
        switch ($view->requestConfig->apiMethodToRequestDataTable) {
            case 'Actions.getPageUrls':
                $this->configureViewForPageUrls($view);
                break;
            case 'Actions.getEntryPageUrls':
                $this->configureViewForEntryPageUrls($view);
                break;
            case 'Actions.getExitPageUrls':
                $this->configureViewForExitPageUrls($view);
                break;
            case 'Actions.getSiteSearchKeywords':
                $this->configureViewForSiteSearchKeywords($view);
                break;
            case 'Actions.getSiteSearchNoResultKeywords':
                $this->configureViewForSiteSearchNoResultKeywords($view);
                break;
            case 'Actions.getSiteSearchCategories':
                $this->configureViewForSiteSearchCategories($view);
                break;
            case 'Actions.getPageUrlsFollowingSiteSearch':
                $this->configureViewForGetPageUrlsOrTitlesFollowingSiteSearch($view, false);
                break;
            case 'Actions.getPageTitlesFollowingSiteSearch':
                $this->configureViewForGetPageUrlsOrTitlesFollowingSiteSearch($view, true);
                break;
            case 'Actions.getPageTitles':
                $this->configureViewForGetPageTitles($view);
                break;
            case 'Actions.getEntryPageTitles':
                $this->configureViewForGetEntryPageTitles($view);
                break;
            case 'Actions.getExitPageTitles':
                $this->configureViewForGetExitPageTitles($view);
                break;
            case 'Actions.getDownloads':
                $this->configureViewForGetDownloads($view);
                break;
            case 'Actions.getOutlinks':
                $this->configureViewForGetOutlinks($view);
                break;
        }

        if ($this->pluginName == $view->requestConfig->getApiModuleToRequest()) {
            if ($view->isRequestingSingleDataTable()) {
                // make sure custom visualizations are shown on actions reports
                $view->config->show_all_views_icons = true;
                $view->config->show_bar_chart = false;
                $view->config->show_pie_chart = false;
                $view->config->show_tag_cloud = false;
            }
        }
    }

    private function addBaseDisplayProperties(ViewDataTable $view)
    {
        $view->config->datatable_js_type      = 'ActionsDataTable';
        $view->config->search_recursive       = true;
        $view->config->show_table_all_columns = false;
        $view->requestConfig->filter_limit    = self::ACTIONS_REPORT_ROWS_DISPLAY;
        $view->config->show_all_views_icons = false;

        if ($view->isViewDataTableId(HtmlTable::ID)) {
            $view->config->show_embedded_subtable = true;
        }

        if (Request::shouldLoadExpanded()) {

            if ($view->isViewDataTableId(HtmlTable::ID)) {
                $view->config->show_expanded = true;
            }

            $view->config->filters[] = function ($dataTable) {
                Actions::setDataTableRowLevels($dataTable);
            };
        }

        $view->config->filters[] = function ($dataTable) use ($view) {
            if ($view->isViewDataTableId(HtmlTable::ID)) {
                $view->config->datatable_css_class = 'dataTableActions';
            }
        };
    }

    /**
     * @param \Piwik\DataTable $dataTable
     * @param int $level
     */
    public static function setDataTableRowLevels($dataTable, $level = 0)
    {
        foreach ($dataTable->getRows() as $row) {
            $row->setMetadata('css_class', 'level' . $level);

            $subtable = $row->getSubtable();
            if ($subtable) {
                self::setDataTableRowLevels($subtable, $level + 1);
            }
        }
    }

    private function addExcludeLowPopDisplayProperties(ViewDataTable $view)
    {
        if (Common::getRequestVar('enable_filter_excludelowpop', '0', 'string') != '0') {
            $view->requestConfig->filter_excludelowpop = 'nb_hits';
            $view->requestConfig->filter_excludelowpop_value = function () {
                // computing minimum value to exclude (2 percent of the total number of actions)
                $visitsInfo = \Piwik\Plugins\VisitsSummary\Controller::getVisitsSummary()->getFirstRow();
                $nbActions = $visitsInfo->getColumn('nb_actions');
                $nbActionsLowPopulationThreshold = floor(0.02 * $nbActions);

                // we remove 1 to make sure some actions/downloads are displayed in the case we have a very few of them
                // and each of them has 1 or 2 hits...
                return min($visitsInfo->getColumn('max_actions') - 1, $nbActionsLowPopulationThreshold - 1);
            };
        }
    }

    private function addPageDisplayProperties(ViewDataTable $view)
    {
        $view->config->addTranslations(array(
            'nb_hits'             => Piwik::translate('General_ColumnPageviews'),
            'nb_visits'           => Piwik::translate('General_ColumnUniquePageviews'),
            'avg_time_on_page'    => Piwik::translate('General_ColumnAverageTimeOnPage'),
            'bounce_rate'         => Piwik::translate('General_ColumnBounceRate'),
            'exit_rate'           => Piwik::translate('General_ColumnExitRate'),
            'avg_time_generation' => Piwik::translate('General_ColumnAverageGenerationTime'),
        ));

        // prettify avg_time_on_page column
        $getPrettyTimeFromSeconds = '\Piwik\MetricsFormatter::getPrettyTimeFromSeconds';
        $view->config->filters[] = array('ColumnCallbackReplace', array('avg_time_on_page', $getPrettyTimeFromSeconds));

        // prettify avg_time_generation column
        $avgTimeCallback = function ($time) {
            return $time ? MetricsFormatter::getPrettyTimeFromSeconds($time, true, true, false) : "-";
        };
        $view->config->filters[] = array('ColumnCallbackReplace', array('avg_time_generation', $avgTimeCallback));

        // add avg_generation_time tooltip
        $tooltipCallback = function ($hits, $min, $max) {
            if (!$hits) {
                return false;
            }

            return Piwik::translate("Actions_AvgGenerationTimeTooltip", array(
                                                                            $hits,
                                                                            "<br />",
                                                                            MetricsFormatter::getPrettyTimeFromSeconds($min),
                                                                            MetricsFormatter::getPrettyTimeFromSeconds($max)
                                                                       ));
        };
        $view->config->filters[] = array('ColumnCallbackAddMetadata',
                                     array(
                                         array('nb_hits_with_time_generation', 'min_time_generation', 'max_time_generation'),
                                         'avg_time_generation_tooltip',
                                         $tooltipCallback
                                     )
        );

        $this->addExcludeLowPopDisplayProperties($view);
    }

    public function configureViewForPageUrls(ViewDataTable $view)
    {
        $view->config->addTranslation('label', Piwik::translate('Actions_ColumnPageURL'));
        $view->config->columns_to_display = array('label', 'nb_hits', 'nb_visits', 'bounce_rate',
                                                  'avg_time_on_page', 'exit_rate', 'avg_time_generation');

        $this->addPageDisplayProperties($view);
        $this->addBaseDisplayProperties($view);
    }

    public function configureViewForEntryPageUrls(ViewDataTable $view)
    {
        // link to the page, not just the report, but only if not a widget
        $widget    = Common::getRequestVar('widget', false);

        $view->config->self_url = Request::getCurrentUrlWithoutGenericFilters(array(
            'module' => 'Actions',
            'action' => $widget === false ? 'indexEntryPageUrls' : 'getEntryPageUrls'
        ));

        $view->config->addTranslations(array(
            'label'              => Piwik::translate('Actions_ColumnEntryPageURL'),
            'entry_bounce_count' => Piwik::translate('General_ColumnBounces'),
            'entry_nb_visits'    => Piwik::translate('General_ColumnEntrances'))
        );

        $view->config->title = Piwik::translate('Actions_SubmenuPagesEntry');
        $view->config->addRelatedReport('Actions.getEntryPageTitles', Piwik::translate('Actions_EntryPageTitles'));
        $view->config->columns_to_display = array('label', 'entry_nb_visits', 'entry_bounce_count', 'bounce_rate');
        $view->requestConfig->filter_sort_column = 'entry_nb_visits';
        $view->requestConfig->filter_sort_order  = 'desc';

        $this->addPageDisplayProperties($view);
        $this->addBaseDisplayProperties($view);
    }

    public function configureViewForExitPageUrls(ViewDataTable $view)
    {
        // link to the page, not just the report, but only if not a widget
        $widget    = Common::getRequestVar('widget', false);

        $view->config->self_url = Request::getCurrentUrlWithoutGenericFilters(array(
            'module' => 'Actions',
            'action' => $widget === false ? 'indexExitPageUrls' : 'getExitPageUrls'
        ));

        $view->config->addTranslations(array(
                'label'          => Piwik::translate('Actions_ColumnExitPageURL'),
                'exit_nb_visits' => Piwik::translate('General_ColumnExits'))
        );

        $view->config->title = Piwik::translate('Actions_SubmenuPagesExit');
        $view->config->addRelatedReport('Actions.getExitPageTitles', Piwik::translate('Actions_ExitPageTitles'));

        $view->config->columns_to_display        = array('label', 'exit_nb_visits', 'nb_visits', 'exit_rate');
        $view->requestConfig->filter_sort_column = 'exit_nb_visits';
        $view->requestConfig->filter_sort_order  = 'desc';

        $this->addPageDisplayProperties($view);
        $this->addBaseDisplayProperties($view);
    }

    private function addSiteSearchDisplayProperties(ViewDataTable $view)
    {
        $view->config->addTranslations(array(
            'nb_visits'           => Piwik::translate('Actions_ColumnSearches'),
            'exit_rate'           => str_replace("% ", "%&nbsp;", Piwik::translate('Actions_ColumnSearchExits')),
            'nb_pages_per_search' => Piwik::translate('Actions_ColumnPagesPerSearch')
        ));

        $view->config->show_bar_chart         = false;
        $view->config->show_table_all_columns = false;
    }

    public function configureViewForSiteSearchKeywords(ViewDataTable $view)
    {
        $view->config->addTranslation('label', Piwik::translate('General_ColumnKeyword'));
        $view->config->columns_to_display = array('label', 'nb_visits', 'nb_pages_per_search', 'exit_rate');

        $this->addSiteSearchDisplayProperties($view);
    }

    public function configureViewForSiteSearchNoResultKeywords(ViewDataTable $view)
    {
        $view->config->addTranslation('label', Piwik::translate('Actions_ColumnNoResultKeyword'));
        $view->config->columns_to_display = array('label', 'nb_visits', 'exit_rate');

        $this->addSiteSearchDisplayProperties($view);
    }

    public function configureViewForSiteSearchCategories(ViewDataTable $view)
    {
        $view->config->addTranslations(array(
            'label'               => Piwik::translate('Actions_ColumnSearchCategory'),
            'nb_visits'           => Piwik::translate('Actions_ColumnSearches'),
            'nb_pages_per_search' => Piwik::translate('Actions_ColumnPagesPerSearch')
        ));

        $view->config->columns_to_display     = array('label', 'nb_visits', 'nb_pages_per_search');
        $view->config->show_table_all_columns = false;
        $view->config->show_bar_chart         = false;

        if ($view->isViewDataTableId(HtmlTable::ID)) {
            $view->config->disable_row_evolution = false;
        }
    }

    public function configureViewForGetPageUrlsOrTitlesFollowingSiteSearch(ViewDataTable $view, $isTitle)
    {
        $title = $isTitle ? Piwik::translate('Actions_WidgetPageTitlesFollowingSearch')
            : Piwik::translate('Actions_WidgetPageUrlsFollowingSearch');

        $relatedReports = array(
            'Actions.getPageTitlesFollowingSiteSearch' => Piwik::translate('Actions_WidgetPageTitlesFollowingSearch'),
            'Actions.getPageUrlsFollowingSiteSearch'   => Piwik::translate('Actions_WidgetPageUrlsFollowingSearch'),
        );

        $view->config->addRelatedReports($relatedReports);
        $view->config->addTranslations(array(
            'label'                    => Piwik::translate('General_ColumnDestinationPage'),
            'nb_hits_following_search' => Piwik::translate('General_ColumnViewedAfterSearch'),
            'nb_hits'                  => Piwik::translate('General_ColumnTotalPageviews')
        ));

        $view->config->title = $title;
        $view->config->columns_to_display          = array('label', 'nb_hits_following_search', 'nb_hits');
        $view->config->show_exclude_low_population = false;
        $view->requestConfig->filter_sort_column = 'nb_hits_following_search';
        $view->requestConfig->filter_sort_order  = 'desc';

        $this->addExcludeLowPopDisplayProperties($view);
        $this->addBaseDisplayProperties($view);
    }

    public function configureViewForGetPageTitles(ViewDataTable $view)
    {
        // link to the page, not just the report, but only if not a widget
        $widget = Common::getRequestVar('widget', false);

        $view->config->self_url = Request::getCurrentUrlWithoutGenericFilters(array(
            'module' => 'Actions',
            'action' => $widget === false ? 'indexPageTitles' : 'getPageTitles'
        ));

        $view->config->title = Piwik::translate('Actions_SubmenuPageTitles');
        $view->config->addRelatedReports(array(
            'Actions.getEntryPageTitles' => Piwik::translate('Actions_EntryPageTitles'),
            'Actions.getExitPageTitles'  => Piwik::translate('Actions_ExitPageTitles'),
        ));

        $view->config->addTranslation('label', Piwik::translate('Actions_ColumnPageName'));
        $view->config->columns_to_display = array('label', 'nb_hits', 'nb_visits', 'bounce_rate',
                                                  'avg_time_on_page', 'exit_rate', 'avg_time_generation');

        $this->addPageDisplayProperties($view);
        $this->addBaseDisplayProperties($view);
    }

    public function configureViewForGetEntryPageTitles(ViewDataTable $view)
    {
        $entryPageUrlAction =
            Common::getRequestVar('widget', false) === false ? 'indexEntryPageUrls' : 'getEntryPageUrls';

        $view->config->addTranslations(array(
            'label'              => Piwik::translate('Actions_ColumnEntryPageTitle'),
            'entry_bounce_count' => Piwik::translate('General_ColumnBounces'),
            'entry_nb_visits'    => Piwik::translate('General_ColumnEntrances'),
        ));
        $view->config->addRelatedReports(array(
            'Actions.getPageTitles'       => Piwik::translate('Actions_SubmenuPageTitles'),
            "Actions.$entryPageUrlAction" => Piwik::translate('Actions_SubmenuPagesEntry')
        ));

        $view->config->columns_to_display = array('label', 'entry_nb_visits', 'entry_bounce_count', 'bounce_rate');
        $view->config->title = Piwik::translate('Actions_EntryPageTitles');

        $view->requestConfig->filter_sort_column = 'entry_nb_visits';

        $this->addPageDisplayProperties($view);
        $this->addBaseDisplayProperties($view);
    }

    public function configureViewForGetExitPageTitles(ViewDataTable $view)
    {
        $exitPageUrlAction =
            Common::getRequestVar('widget', false) === false ? 'indexExitPageUrls' : 'getExitPageUrls';

        $view->config->addTranslations(array(
            'label'          => Piwik::translate('Actions_ColumnExitPageTitle'),
            'exit_nb_visits' => Piwik::translate('General_ColumnExits'),
        ));
        $view->config->addRelatedReports(array(
            'Actions.getPageTitles'      => Piwik::translate('Actions_SubmenuPageTitles'),
            "Actions.$exitPageUrlAction" => Piwik::translate('Actions_SubmenuPagesExit'),
        ));

        $view->config->title = Piwik::translate('Actions_ExitPageTitles');
        $view->config->columns_to_display = array('label', 'exit_nb_visits', 'nb_visits', 'exit_rate');

        $this->addPageDisplayProperties($view);
        $this->addBaseDisplayProperties($view);
    }

    public function configureViewForGetDownloads(ViewDataTable $view)
    {
        $view->config->addTranslations(array(
            'label'     => Piwik::translate('Actions_ColumnDownloadURL'),
            'nb_visits' => Piwik::translate('Actions_ColumnUniqueDownloads'),
            'nb_hits'   => Piwik::translate('General_Downloads'),
        ));

        $view->config->columns_to_display = array('label', 'nb_visits', 'nb_hits');
        $view->config->show_exclude_low_population = false;

        $this->addBaseDisplayProperties($view);
    }

    public function configureViewForGetOutlinks(ViewDataTable $view)
    {
        $view->config->addTranslations(array(
            'label'     => Piwik::translate('Actions_ColumnClickedURL'),
            'nb_visits' => Piwik::translate('Actions_ColumnUniqueClicks'),
            'nb_hits'   => Piwik::translate('Actions_ColumnClicks'),
        ));

        $view->config->columns_to_display          = array('label', 'nb_visits', 'nb_hits');
        $view->config->show_exclude_low_population = false;

        $this->addBaseDisplayProperties($view);
    }
}

