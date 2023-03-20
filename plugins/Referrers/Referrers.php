<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Referrers;

use Piwik\Columns\Dimension;
use Piwik\Common;
use Piwik\Piwik;
use Piwik\Plugins\SitesManager\SiteUrls;

/**
 * @see plugins/Referrers/functions.php
 */
require_once PIWIK_INCLUDE_PATH . '/plugins/Referrers/functions.php';

/**
 */
class Referrers extends \Piwik\Plugin
{
    /**
     * @see \Piwik\Plugin::registerEvents
     */
    public function registerEvents()
    {
        return array(
            'Insights.addReportToOverview'      => 'addReportToInsightsOverview',
            'Request.getRenamedModuleAndAction' => 'renameDeprecatedModuleAndAction',
            'Translate.getClientSideTranslationKeys' => 'getClientSideTranslationKeys',
            'Tracker.setTrackerCacheGeneral'    => 'setTrackerCacheGeneral',
            'AssetManager.getJavaScriptFiles'   => 'getJsFiles',
            'AssetManager.getStylesheetFiles'   => 'getStylesheetFiles',
            'API.getPagesComparisonsDisabledFor'     => 'getPagesComparisonsDisabledFor',
            'Metrics.getDefaultMetricTranslations' => 'getDefaultMetricTranslations',
            'Metrics.getDefaultMetricSemanticTypes'  => 'addMetricSemanticTypes',
        );
    }

    public function getDefaultMetricTranslations(&$translations)
    {
        $translations['Referrers_visitorsFromSearchEngines'] = Piwik::translate('Referrers_VisitorsFromSearchEngines');
        $translations['Referrers_visitorsFromSearchEngines_percent'] = Piwik::translate('Referrers_PercentOfX', $translations['Referrers_visitorsFromSearchEngines']);

        $translations['Referrers_visitorsFromSocialNetworks'] = Piwik::translate('Referrers_VisitorsFromSocialNetworks');
        $translations['Referrers_visitorsFromSocialNetworks_percent'] = Piwik::translate('Referrers_PercentOfX', $translations['Referrers_visitorsFromSocialNetworks']);

        $translations['Referrers_visitorsFromDirectEntry'] = Piwik::translate('Referrers_VisitorsFromDirectEntry');
        $translations['Referrers_visitorsFromDirectEntry_percent'] = Piwik::translate('Referrers_PercentOfX', $translations['Referrers_visitorsFromDirectEntry']);

        $translations['Referrers_visitorsFromWebsites'] = Piwik::translate('Referrers_VisitorsFromWebsites');
        $translations['Referrers_visitorsFromWebsites_percent'] = Piwik::translate('Referrers_PercentOfX', $translations['Referrers_visitorsFromWebsites']);

        $translations['Referrers_visitorsFromCampaigns'] = Piwik::translate('Referrers_VisitorsFromCampaigns');
        $translations['Referrers_visitorsFromCampaigns_percent'] = Piwik::translate('Referrers_PercentOfX', $translations['Referrers_visitorsFromCampaigns']);

        $translations[Archiver::METRIC_DISTINCT_SEARCH_ENGINE_RECORD_NAME] = ucfirst(Piwik::translate('Referrers_DistinctSearchEngines'));
        $translations[Archiver::METRIC_DISTINCT_SOCIAL_NETWORK_RECORD_NAME] = ucfirst(Piwik::translate('Referrers_DistinctSocialNetworks'));
        $translations[Archiver::METRIC_DISTINCT_WEBSITE_RECORD_NAME] = ucfirst(Piwik::translate('Referrers_DistinctWebsites'));
        $translations[Archiver::METRIC_DISTINCT_KEYWORD_RECORD_NAME] = ucfirst(Piwik::translate('Referrers_DistinctKeywords'));
        $translations[Archiver::METRIC_DISTINCT_CAMPAIGN_RECORD_NAME] = ucfirst(Piwik::translate('Referrers_DistinctCampaigns'));
    }

    public function addMetricSemanticTypes(array &$types): void
    {
        $types['Referrers_visitorsFromSearchEngines'] = Dimension::TYPE_NUMBER;
        $types['Referrers_visitorsFromSearchEngines_percent'] = Dimension::TYPE_PERCENT;

        $types['Referrers_visitorsFromSocialNetworks'] = Dimension::TYPE_NUMBER;
        $types['Referrers_visitorsFromSocialNetworks_percent'] = Dimension::TYPE_PERCENT;

        $types['Referrers_visitorsFromDirectEntry'] = Dimension::TYPE_NUMBER;
        $types['Referrers_visitorsFromDirectEntry_percent'] = Dimension::TYPE_PERCENT;

        $types['Referrers_visitorsFromWebsites'] = Dimension::TYPE_NUMBER;
        $types['Referrers_visitorsFromWebsites_percent'] = Dimension::TYPE_PERCENT;

        $types['Referrers_visitorsFromCampaigns'] = Dimension::TYPE_NUMBER;
        $types['Referrers_visitorsFromCampaigns_percent'] = Dimension::TYPE_PERCENT;

        $types[Archiver::METRIC_DISTINCT_SEARCH_ENGINE_RECORD_NAME] = Dimension::TYPE_NUMBER;
        $types[Archiver::METRIC_DISTINCT_SOCIAL_NETWORK_RECORD_NAME] = Dimension::TYPE_NUMBER;
        $types[Archiver::METRIC_DISTINCT_WEBSITE_RECORD_NAME] = Dimension::TYPE_NUMBER;
        $types[Archiver::METRIC_DISTINCT_KEYWORD_RECORD_NAME] = Dimension::TYPE_NUMBER;
        $types[Archiver::METRIC_DISTINCT_CAMPAIGN_RECORD_NAME] = Dimension::TYPE_NUMBER;
    }

    public function getPagesComparisonsDisabledFor(&$pages)
    {
        $pages[] = 'Referrers_Referrers.Referrers_URLCampaignBuilder';
    }

    public function getStylesheetFiles(&$stylesheets)
    {
        $stylesheets[] = 'plugins/Referrers/vue/src/CampaignBuilder/CampaignBuilder.less';
    }

    public function getClientSideTranslationKeys(&$translationKeys)
    {
        $translationKeys[] = 'General_Required2';
        $translationKeys[] = 'General_Clear';
        $translationKeys[] = 'Actions_ColumnPageURL';
        $translationKeys[] = 'CoreAdminHome_JSTracking_CampaignNameParam';
        $translationKeys[] = 'CoreAdminHome_JSTracking_CampaignKwdParam';
        $translationKeys[] = 'Referrers_CampaignSource';
        $translationKeys[] = 'Referrers_CampaignSourceHelp';
        $translationKeys[] = 'Referrers_CampaignContent';
        $translationKeys[] = 'Referrers_CampaignContentHelp';
        $translationKeys[] = 'Referrers_CampaignMedium';
        $translationKeys[] = 'Referrers_CampaignMediumHelp';
        $translationKeys[] = 'Referrers_CampaignGroup';
        $translationKeys[] = 'Referrers_CampaignGroupHelp';
        $translationKeys[] = 'Referrers_CampaignPlacement';
        $translationKeys[] = 'Referrers_CampaignPlacementHelp';
        $translationKeys[] = 'Referrers_CampaignId';
        $translationKeys[] = 'Referrers_CampaignIdHelp';
        $translationKeys[] = 'Referrers_CampaignPageUrlHelp';
        $translationKeys[] = 'Referrers_CampaignNameHelp';
        $translationKeys[] = 'Referrers_CampaignKeywordHelp';
        $translationKeys[] = 'Referrers_URLCampaignBuilderResult';
        $translationKeys[] = 'Referrers_GenerateUrl';
        $translationKeys[] = 'Goals_Optional';
    }

    public function getJsFiles(&$jsFiles)
    {
    }

    public function setTrackerCacheGeneral(&$cacheContent)
    {
        $siteUrls = new SiteUrls();
        $urls = $siteUrls->getAllCachedSiteUrls();

        return $cacheContent['allUrlsByHostAndIdSite'] = $siteUrls->groupUrlsByHost($urls);
    }

    public function renameDeprecatedModuleAndAction(&$module, &$action)
    {
        if($module == 'Referers') {
            $module = 'Referrers';
        }
    }

    public function addReportToInsightsOverview(&$reports)
    {
        $reports['Referrers_getWebsites']  = array();
        $reports['Referrers_getCampaigns'] = array();
        $reports['Referrers_getSocials']   = array();
        $reports['Referrers_getSearchEngines'] = array();
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
            case Common::REFERRER_TYPE_DIRECT_ENTRY:
                $indexTranslation = 'Referrers_DirectEntry';
                break;
            case Common::REFERRER_TYPE_SEARCH_ENGINE:
                $indexTranslation = 'General_ColumnKeyword';
                break;
            case Common::REFERRER_TYPE_SOCIAL_NETWORK:
                $indexTranslation = 'Referrers_ColumnSocial';
                break;
            case Common::REFERRER_TYPE_WEBSITE:
                $indexTranslation = 'Referrers_ColumnWebsite';
                break;
            case Common::REFERRER_TYPE_CAMPAIGN:
                $indexTranslation = 'Referrers_ColumnCampaign';
                break;
            default:
                // case of newsletter, partners, before Piwik 0.2.25
                $indexTranslation = 'General_Others';
                break;
        }

        $label = strtolower(Piwik::translate($indexTranslation));

        // return html that displays it as grey & italic
        return '<span class="datatable-label-category">(' . $label . ')</span>';
    }
}
