<?php
/**
 * Piwik - Open source web analytics
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

    public function getReportMetadata(&$reports)
    {
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

}

