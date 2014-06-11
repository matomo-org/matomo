<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\MultiSites;

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
            'Translate.getClientSideTranslationKeys' => 'getClientSideTranslationKeys',
            'Metrics.getDefaultMetricTranslations'  => 'addMetricTranslations'
        );
    }

    public function addMetricTranslations(&$translations)
    {
        $metrics = array(
            'visits_evolution'    => 'General_ColumnNbVisits',
            'actions_evolution'   => 'General_ColumnNbActions',
            'pageviews_evolution' => 'General_ColumnPageviews',
            'revenue_evolution'   => 'General_ColumnRevenue',
            'nb_conversions_evolution' => 'Goals_ColumnConversions',
            'orders_evolution'         => 'General_EcommerceOrders',
            'ecommerce_revenue_evolution' => 'General_ProductRevenue',
        );

        $metrics = array_map(array('\\Piwik\\Piwik', 'translate'), $metrics);

        $translations = array_merge($translations, $metrics);
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
