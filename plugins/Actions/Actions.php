<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Actions;

use Piwik\Columns\Dimension;
use Piwik\Site;
use Piwik\Plugin\ViewDataTable;
use Piwik\Tracker\Action;

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
     * @see \Piwik\Plugin::registerEvents
     */
    public function registerEvents()
    {
        return array(
            'ViewDataTable.configure'         => 'configureViewDataTable',
            'AssetManager.getJavaScriptFiles' => 'getJsFiles',
            'Insights.addReportToOverview'    => 'addReportToInsightsOverview',
            'Metrics.getDefaultMetricTranslations' => 'addMetricTranslations',
            'Metrics.getDefaultMetricSemanticTypes' => 'addMetricSemanticTypes',
            'Metrics.getDefaultMetricDocumentationTranslations' => 'addMetricDocumentationTranslations',
            'Actions.addActionTypes' => 'addActionTypes'
        );
    }

    public function addMetricSemanticTypes(array &$types): void
    {
        $metrics = array(
            'nb_pageviews'        => Dimension::TYPE_NUMBER,
            'nb_uniq_pageviews'   => Dimension::TYPE_NUMBER,
            'nb_downloads'        => Dimension::TYPE_NUMBER,
            'nb_uniq_downloads'   => Dimension::TYPE_NUMBER,
            'nb_outlinks'         => Dimension::TYPE_NUMBER,
            'nb_uniq_outlinks'    => Dimension::TYPE_NUMBER,
            'nb_searches'         => Dimension::TYPE_NUMBER,
            'nb_keywords'         => Dimension::TYPE_NUMBER,
            'entry_nb_visits'      => Dimension::TYPE_NUMBER,
            'entry_bounce_count'   => Dimension::TYPE_NUMBER,
            'exit_nb_visits'       => Dimension::TYPE_NUMBER,
            'nb_pages_per_search'      => Dimension::TYPE_NUMBER,
            'nb_hits_following_search' => Dimension::TYPE_NUMBER,
        );

        $types = array_merge($types, $metrics);
    }

    public function addMetricTranslations(&$translations)
    {
        $metrics = array(
            'nb_pageviews'        => 'General_ColumnPageviews',
            'nb_uniq_pageviews'   => 'General_ColumnUniquePageviews',
            'nb_downloads'        => 'General_Downloads',
            'nb_uniq_downloads'   => 'Actions_ColumnUniqueDownloads',
            'nb_outlinks'         => 'General_Outlinks',
            'nb_uniq_outlinks'    => 'Actions_ColumnUniqueOutlinks',
            'nb_searches'         => 'Actions_ColumnSearches',
            'nb_keywords'         => 'Actions_ColumnSiteSearchKeywords',
            'avg_time_generation' => 'General_ColumnAverageGenerationTime',
            'exit_rate'            => 'General_ColumnExitRate',
            'entry_nb_visits'      => 'General_ColumnEntrances',
            'entry_bounce_count'   => 'General_ColumnBounces',
            'exit_nb_visits'       => 'General_ColumnExits',
            'nb_pages_per_search'      => 'Actions_ColumnPagesPerSearch',
            'nb_hits_following_search' => 'General_ColumnViewedAfterSearch',
        );

        $translations = array_merge($translations, $metrics);
    }

    public function addMetricDocumentationTranslations(&$translations)
    {
        $metrics = array(
            'nb_pageviews'        => 'General_ColumnPageviewsDocumentation',
            'nb_uniq_pageviews'   => 'General_ColumnUniquePageviewsDocumentation',
            'nb_downloads'        => 'Actions_ColumnClicksDocumentation',
            'nb_uniq_downloads'   => 'Actions_ColumnUniqueClicksDocumentation',
            'nb_outlinks'         => 'Actions_ColumnClicksDocumentation',
            'nb_uniq_outlinks'    => 'Actions_ColumnUniqueClicksDocumentation',
            'nb_searches'         => 'Actions_ColumnSearchesDocumentation',
            'avg_time_generation' => 'General_ColumnAverageGenerationTimeDocumentation',
            'entry_nb_visits'     => 'General_ColumnEntrancesDocumentation',
            'entry_bounce_count'  => 'General_ColumnBouncesDocumentation',
            'exit_nb_visits'      => 'General_ColumnExitsDocumentation',
            'exit_rate'           => 'General_ColumnExitRateDocumentation'
        );

        $translations = array_merge($translations, $metrics);
    }

    public function addReportToInsightsOverview(&$reports)
    {
        $reports['Actions_getPageUrls']   = array();
        $reports['Actions_getPageTitles'] = array();
        $reports['Actions_getDownloads']  = array('flat' => 1);
    }

    public function getJsFiles(&$jsFiles)
    {
        $jsFiles[] = "plugins/Actions/javascripts/actionsDataTable.js";
        $jsFiles[] = "plugins/Actions/javascripts/rowactions.js";
    }

    public function addActionTypes(&$types)
    {
        $types[] = [
            'id' => Action::TYPE_PAGE_URL,
            'name' => 'pageviews'
        ];
        $types[] = [
            'id' => Action::TYPE_CONTENT,
            'name' => 'contents'
        ];
        $types[] = [
            'id' => Action::TYPE_SITE_SEARCH,
            'name' => 'sitesearches'
        ];
        $types[] = [
            'id' => Action::TYPE_EVENT,
            'name' => 'events'
        ];
        $types[] = [
            'id' => Action::TYPE_OUTLINK,
            'name' => 'outlinks'
        ];
        $types[] = [
            'id' => Action::TYPE_DOWNLOAD,
            'name' => 'downloads'
        ];
    }

    public function isSiteSearchEnabled($idSites, $idSite)
    {
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


    public function configureViewDataTable(ViewDataTable $view)
    {
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

}

