<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\MultiSites;

use Piwik\Piwik;


/**
 *
 */
class MultiSites extends \Piwik\Plugin
{
    public function getInformation()
    {
        $info = parent::getInformation();
        $info['authors'] = array(array('name' => 'Piwik PRO', 'homepage' => 'http://piwik.pro'));
        return $info;
    }

    /**
     * @see Piwik\Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
    {
        return array(
            'AssetManager.getStylesheetFiles' => 'getStylesheetFiles',
            'AssetManager.getJavaScriptFiles' => 'getJsFiles',
            'API.getReportMetadata'           => 'getReportMetadata',
            'Translate.getClientSideTranslationKeys' => 'getClientSideTranslationKeys',
        );
    }

    public function getClientSideTranslationKeys(&$translations)
    {
        $translations[] = 'General_Website';
        $translations[] = 'General_ColumnNbVisits';
        $translations[] = 'General_ColumnPageviews';
        $translations[] = 'General_ColumnRevenue';
        $translations[] = 'General_TotalVisitsPageviewsRevenue';
        $translations[] = 'General_EvolutionSummaryGeneric';
        $translations[] = 'General_AllWebsitesDashboard';
        $translations[] = 'General_NVisits';
        $translations[] = 'MultiSites_Evolution';
        $translations[] = 'SitesManager_AddSite';
        $translations[] = 'General_Next';
        $translations[] = 'General_Previous';
        $translations[] = 'General_GoTo';
        $translations[] = 'Dashboard_DashboardOf';
        $translations[] = 'Actions_SubmenuSitesearch';
        $translations[] = 'MultiSites_LoadingWebsites';
        $translations[] = 'General_ErrorRequest';
        $translations[] = 'MultiSites_Pagination';
    }

    public function getReportMetadata(&$reports)
    {
        $metadataMetrics = array();
        foreach (API::getApiMetrics($enhanced = true) as $metricName => $metricSettings) {
            $metadataMetrics[$metricName] =
                Piwik::translate($metricSettings[API::METRIC_TRANSLATION_KEY]);
            $metadataMetrics[$metricSettings[API::METRIC_EVOLUTION_COL_NAME_KEY]] =
                Piwik::translate($metricSettings[API::METRIC_TRANSLATION_KEY]) . " " . Piwik::translate('MultiSites_Evolution');
        }

        $reports[] = array(
            'category'          => Piwik::translate('General_MultiSitesSummary'),
            'name'              => Piwik::translate('General_AllWebsitesDashboard'),
            'module'            => 'MultiSites',
            'action'            => 'getAll',
            'dimension'         => Piwik::translate('General_Website'), // re-using translation
            'metrics'           => $metadataMetrics,
            'processedMetrics'  => false,
            'constantRowsCount' => false,
            'order'             => 4
        );

        $reports[] = array(
            'category'          => Piwik::translate('General_MultiSitesSummary'),
            'name'              => Piwik::translate('General_SingleWebsitesDashboard'),
            'module'            => 'MultiSites',
            'action'            => 'getOne',
            'dimension'         => Piwik::translate('General_Website'), // re-using translation
            'metrics'           => $metadataMetrics,
            'processedMetrics'  => false,
            'constantRowsCount' => false,
            'order'             => 5
        );
    }

    public function getJsFiles(&$jsFiles)
    {
        $jsFiles[] = "plugins/MultiSites/angularjs/dashboard/dashboard-model.js";
        $jsFiles[] = "plugins/MultiSites/angularjs/dashboard/dashboard-controller.js";
        $jsFiles[] = "plugins/MultiSites/angularjs/dashboard/dashboard-filter.js";
        $jsFiles[] = "plugins/MultiSites/angularjs/dashboard/dashboard-directive.js";
        $jsFiles[] = "plugins/MultiSites/angularjs/site/site-directive.js";
    }

    public function getStylesheetFiles(&$stylesheets)
    {
        $stylesheets[] = "plugins/MultiSites/angularjs/dashboard/dashboard.less";
    }
}
