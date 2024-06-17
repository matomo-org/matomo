<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\MultiSites;

use Piwik\Piwik;

class MultiSites extends \Piwik\Plugin
{
    /**
     * @see \Piwik\Plugin::registerEvents
     */
    public function registerEvents()
    {
        return array(
            'AssetManager.getStylesheetFiles' => 'getStylesheetFiles',
            'Translate.getClientSideTranslationKeys' => 'getClientSideTranslationKeys',
            'Metrics.getDefaultMetricTranslations'  => 'addMetricTranslations',
            'API.getPagesComparisonsDisabledFor'     => 'getPagesComparisonsDisabledFor',
        );
    }

    public function getPagesComparisonsDisabledFor(&$pages)
    {
        $pages[] = 'MultiSites.index';
    }

    public function addMetricTranslations(&$translations)
    {
        $appendix = " " . Piwik::translate('MultiSites_Evolution');
        $metrics = array(
            'visits_evolution'    => Piwik::translate('General_ColumnNbVisits') . $appendix,
            'actions_evolution'   => Piwik::translate('General_ColumnNbActions') . $appendix,
            'pageviews_evolution' => Piwik::translate('General_ColumnPageviews') . $appendix,
            'revenue_evolution'   => Piwik::translate('General_ColumnRevenue') . $appendix,
            'nb_conversions_evolution' => Piwik::translate('Goals_ColumnConversions') . $appendix,
            'orders_evolution'         => Piwik::translate('General_EcommerceOrders') . $appendix,
            'ecommerce_revenue_evolution' => Piwik::translate('General_ProductRevenue') . $appendix,
        );

        $translations = array_merge($translations, $metrics);
    }

    public function getClientSideTranslationKeys(&$translations)
    {
        $translations[] = 'General_Website';
        $translations[] = 'General_ColumnNbVisits';
        $translations[] = 'General_ColumnPageviews';
        $translations[] = 'General_ColumnRevenue';
        $translations[] = 'General_TotalVisitsPageviewsActionsRevenue';
        $translations[] = 'General_EvolutionSummaryGeneric';
        $translations[] = 'General_AllWebsitesDashboard';
        $translations[] = 'General_NVisits';
        $translations[] = 'General_TotalRevenue';
        $translations[] = 'MultiSites_Evolution';
        $translations[] = 'SitesManager_AddSite';
        $translations[] = 'General_Next';
        $translations[] = 'General_Previous';
        $translations[] = 'General_GoTo';
        $translations[] = 'Dashboard_DashboardOf';
        $translations[] = 'Actions_SubmenuSitesearch';
        $translations[] = 'MultiSites_LoadingWebsites';
        $translations[] = 'General_ErrorRequest';
        $translations[] = 'General_Pagination';
        $translations[] = 'General_ClickToSearch';
        $translations[] = 'General_NeedMoreHelp';
        $translations[] = 'General_Faq';
        $translations[] = 'Feedback_CommunityHelp';
        $translations[] = 'Feedback_ProfessionalHelp';
        $translations[] = 'MultiSites_AllWebsitesDashboardTitle';
        $translations[] = 'MultiSites_EvolutionComparisonIncomplete';
        $translations[] = 'MultiSites_EvolutionComparisonProportional';
        $translations[] = 'MultiSites_EvolutionComparisonDay';
        $translations[] = 'MultiSites_EvolutionComparisonWeek';
        $translations[] = 'MultiSites_EvolutionComparisonMonth';
        $translations[] = 'MultiSites_EvolutionComparisonYear';
        $translations[] = 'MultiSites_EvolutionFromPreviousDay';
        $translations[] = 'MultiSites_EvolutionFromPreviousMonth';
        $translations[] = 'MultiSites_EvolutionFromPreviousPeriod';
        $translations[] = 'MultiSites_EvolutionFromPreviousWeek';
        $translations[] = 'MultiSites_EvolutionFromPreviousYear';
        $translations[] = 'MultiSites_TotalHits';
        $translations[] = 'MultiSites_TotalPageviews';
        $translations[] = 'MultiSites_TotalVisits';
    }

    public function getStylesheetFiles(&$stylesheets)
    {
        $stylesheets[] = "plugins/MultiSites/vue/src/AllWebsitesDashboard/AllWebsitesDashboard.less";
        $stylesheets[] = "plugins/MultiSites/vue/src/Dashboard/Dashboard.less";
    }
}
