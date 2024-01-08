<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\SitesManager;

use Piwik\Access;
use Piwik\API\Request;
use Piwik\Common;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\Option;
use Piwik\Piwik;
use Piwik\Plugins\CoreHome\SystemSummary;
use Piwik\Plugins\SitesManager\SiteContentDetection\SiteContentDetectionAbstract;
use Piwik\Settings\Storage\Backend\MeasurableSettingsTable;
use Piwik\SettingsPiwik;
use Piwik\SiteContentDetector;
use Piwik\Tracker\Cache;
use Piwik\Tracker\FingerprintSalt;
use Piwik\Tracker\Model as TrackerModel;
use Piwik\Session\SessionNamespace;
use Piwik\Tracker\TrackerCodeGenerator;
use Piwik\Url;
use Piwik\View;

/**
 *
 */
class SitesManager extends \Piwik\Plugin
{
    const KEEP_URL_FRAGMENT_USE_DEFAULT = 0;
    const KEEP_URL_FRAGMENT_YES = 1;
    const KEEP_URL_FRAGMENT_NO = 2;

    /**
     * @see \Piwik\Plugin::registerEvents
     */
    public function registerEvents()
    {
        return [
            'AssetManager.getStylesheetFiles'        => 'getStylesheetFiles',
            'Tracker.Cache.getSiteAttributes'        => ['function' => 'recordWebsiteDataInCache', 'before' => true],
            'Tracker.setTrackerCacheGeneral'         => 'setTrackerCacheGeneral',
            'Translate.getClientSideTranslationKeys' => 'getClientSideTranslationKeys',
            'SitesManager.deleteSite.end'            => 'onSiteDeleted',
            'System.addSystemSummaryItems'           => 'addSystemSummaryItems',
            'Request.dispatch'                       => 'redirectDashboardToWelcomePage',
        ];
    }

    public static function isSitesAdminEnabled()
    {
        return (bool) Config::getInstance()->General['enable_sites_admin'];
    }

    public static function dieIfSitesAdminIsDisabled()
    {
        Piwik::checkUserIsNotAnonymous();
        if (!self::isSitesAdminEnabled()) {
            throw new \Exception('Creating, updating, and deleting sites has been disabled.');
        }
    }

    public function addSystemSummaryItems(&$systemSummary)
    {
        if (self::isSitesAdminEnabled()) {
            $websites = Request::processRequest('SitesManager.getAllSites', ['filter_limit' => '-1']);
            $numWebsites = count($websites);
            $systemSummary[] = new SystemSummary\Item(
                'websites',
                Piwik::translate('CoreHome_SystemSummaryNWebsites', $numWebsites),
                null,
                ['module' => 'SitesManager', 'action' => 'index'],
                '',
                10
            );
        }
    }

    public function redirectDashboardToWelcomePage(&$module, &$action)
    {
        if ($module !== 'CoreHome' || $action !== 'index') {
            return;
        }

        $siteId = Common::getRequestVar('idSite', false, 'int');
        if (!$siteId) {
            return;
        }

        $shouldPerformEmptySiteCheck = self::shouldPerformEmptySiteCheck($siteId);
        if (!$shouldPerformEmptySiteCheck) {
            return;
        }

        $hadTrafficKey = 'SitesManagerHadTrafficInPast_' . (int) $siteId;
        $hadTrafficBefore = Option::get($hadTrafficKey);
        if (!empty($hadTrafficBefore)) {
            // user had traffic at some stage in the past... not needed to show tracking code
            return;
        } elseif (self::hasTrackedAnyTraffic($siteId)) {
            // remember the user had traffic in the past so we won't show the tracking screen again
            // if all visits are deleted for example
            Option::set($hadTrafficKey, 1);
            return;
        } else {
            // never had any traffic
            $session = new SessionNamespace('siteWithoutData');
            if (!empty($session->ignoreMessage)) {
                return;
            }

            $module = 'SitesManager';
            $action = 'siteWithoutData';
        }
    }

    public static function hasTrackedAnyTraffic($siteId)
    {
        $trackerModel = new TrackerModel();
        return !$trackerModel->isSiteEmpty($siteId);
    }

    public static function shouldPerformEmptySiteCheck($siteId)
    {
        $shouldPerformEmptySiteCheck = true;

        /**
         * Posted before checking to display the "No data has been recorded yet" message.
         * If your Measurable should never have visits, you can use this event to make
         * sure that message is never displayed.
         *
         * @param bool &$shouldPerformEmptySiteCheck Set this value to true to perform the
         *                                           check, false if otherwise.
         * @param int $siteId The ID of the site we would perform a check for.
         */
        Piwik::postEvent('SitesManager.shouldPerformEmptySiteCheck', [&$shouldPerformEmptySiteCheck, $siteId]);

        return $shouldPerformEmptySiteCheck;
    }

    public function onSiteDeleted($idSite)
    {
        // we do not delete logs here on purpose (you can run these queries on the log_ tables to delete all data)
        Cache::deleteCacheWebsiteAttributes($idSite);

        $archiveInvalidator = StaticContainer::get('Piwik\Archive\ArchiveInvalidator');
        $archiveInvalidator->forgetRememberedArchivedReportsToInvalidateForSite($idSite);

        MeasurableSettingsTable::removeAllSettingsForSite($idSite);
    }

    /**
     * Get CSS files
     */
    public function getStylesheetFiles(&$stylesheets)
    {
        $stylesheets[] = "plugins/SitesManager/stylesheets/SitesManager.less";
    }

    /**
     * Hooks when a website tracker cache is flushed (website updated, cache deleted, or empty cache)
     * Will record in the tracker config file all data needed for this website in Tracker.
     *
     * @param array $array
     * @param int $idSite
     * @return void
     */
    public function recordWebsiteDataInCache(&$array, $idSite)
    {
        $idSite = (int) $idSite;

        $website = API::getInstance()->getSiteFromId($idSite);
        $urls = API::getInstance()->getSiteUrlsFromId($idSite);

        // add the 'hosts' entry in the website array
        $array['urls']  = $urls;
        $array['hosts'] = $this->getTrackerHosts($urls);

        $array['exclude_unknown_urls'] = $website['exclude_unknown_urls'];
        $array['excluded_ips'] = $this->getTrackerExcludedIps($website);
        $array['excluded_parameters'] = self::getTrackerExcludedQueryParameters($website);
        $array['excluded_user_agents'] = self::getExcludedUserAgents($website);
        $array['excluded_referrers'] = self::getExcludedReferrers($website);
        $array['keep_url_fragment'] = self::shouldKeepURLFragmentsFor($website);
        $array['sitesearch'] = $website['sitesearch'];
        $array['sitesearch_keyword_parameters'] = $this->getTrackerSearchKeywordParameters($website);
        $array['sitesearch_category_parameters'] = $this->getTrackerSearchCategoryParameters($website);
        $array['timezone'] = $this->getTimezoneFromWebsite($website);
        $array['ts_created'] = $website['ts_created'];
        $array['type'] = $website['type'];

        // we make sure to have the fingerprint salts for the last 3 days incl tmrw in the cache so we don't need to
        // query the DB directly for these days
        $datesToGenerateSalt = [Date::now()->addDay(1), Date::now(), Date::now()->subDay(1), Date::now()->subDay(2)];

        $fingerprintSaltKey = new FingerprintSalt();
        foreach ($datesToGenerateSalt as $date) {
            $dateString = $fingerprintSaltKey->getDateString($date, $array['timezone']);
            $array[FingerprintSalt::OPTION_PREFIX . $dateString] = $fingerprintSaltKey->getSalt($dateString, $idSite);
        }
    }

    public function setTrackerCacheGeneral(&$cache)
    {
        Access::doAsSuperUser(function () use (&$cache) {
            $cache['global_excluded_user_agents'] = self::filterBlankFromCommaSepList(API::getInstance()->getExcludedUserAgentsGlobal());
            $cache['global_excluded_ips'] = self::filterBlankFromCommaSepList(API::getInstance()->getExcludedIpsGlobal());
            $cache['global_excluded_referrers'] = self::filterBlankFromCommaSepList(API::getInstance()->getExcludedReferrersGlobal());
        });
    }

    /**
     * Returns whether we should keep URL fragments for a specific site.
     *
     * @param array $site DB data for the site.
     * @return bool
     */
    private static function getTimezoneFromWebsite($site)
    {
        if (!empty($site['timezone'])) {
            return $site['timezone'];
        }
    }

    /**
     * Returns whether we should keep URL fragments for a specific site.
     *
     * @param array $site DB data for the site.
     * @return bool
     */
    private static function shouldKeepURLFragmentsFor($site)
    {
        if ($site['keep_url_fragment'] == self::KEEP_URL_FRAGMENT_YES) {
            return true;
        } elseif ($site['keep_url_fragment'] == self::KEEP_URL_FRAGMENT_NO) {
            return false;
        }

        return API::getInstance()->getKeepURLFragmentsGlobal();
    }

    private function getTrackerSearchKeywordParameters($website)
    {
        $searchParameters = $website['sitesearch_keyword_parameters'];
        if (empty($searchParameters)) {
            $searchParameters = API::getInstance()->getSearchKeywordParametersGlobal();
        }
        return explode(",", $searchParameters);
    }

    private function getTrackerSearchCategoryParameters($website)
    {
        $searchParameters = $website['sitesearch_category_parameters'];
        if (empty($searchParameters)) {
            $searchParameters = API::getInstance()->getSearchCategoryParametersGlobal();
        }
        return explode(",", $searchParameters);
    }

    /**
     * Returns the array of excluded IPs to save in the config file
     *
     * @param array $website
     * @return array
     */
    private function getTrackerExcludedIps($website)
    {
        $excludedIps = $website['excluded_ips'];
        $globalExcludedIps = API::getInstance()->getExcludedIpsGlobal();

        $excludedIps .= ',' . $globalExcludedIps;

        $ipRanges = [];
        foreach (explode(',', $excludedIps) as $ip) {
            $ipRange = API::getInstance()->getIpsForRange($ip);
            if ($ipRange !== false) {
                $ipRanges[] = $ipRange;
            }
        }
        return $ipRanges;
    }

    /**
     * Returns the array of excluded user agent substrings for a site. Filters out
     * any garbage data & trims each entry.
     *
     * @param array $website The full set of information for a site.
     * @return array
     */
    private static function getExcludedUserAgents($website)
    {
        $excludedUserAgents = API::getInstance()->getExcludedUserAgentsGlobal();
        $excludedUserAgents .= ',' . $website['excluded_user_agents'];
        return self::filterBlankFromCommaSepList($excludedUserAgents);
    }

    /**
     * Returns the array of excluded referrers. Filters out
     * any garbage data & trims each entry.
     *
     * @param array $website The full set of information for a site.
     * @return array
     */
    private static function getExcludedReferrers($website)
    {
        $excludedReferrers = API::getInstance()->getExcludedReferrersGlobal();
        $excludedReferrers .= ',' . $website['excluded_referrers'];
        return self::filterBlankFromCommaSepList($excludedReferrers);
    }

    /**
     * Returns the array of URL query parameters to exclude from URLs
     *
     * @param array $website
     * @return array
     */
    public static function getTrackerExcludedQueryParameters($website)
    {
        $excludedQueryParameters = $website['excluded_parameters'];
        $globalExcludedQueryParameters = API::getInstance()->getExcludedQueryParametersGlobal();

        $excludedQueryParameters .= ',' . $globalExcludedQueryParameters;
        return self::filterBlankFromCommaSepList($excludedQueryParameters);
    }

    /**
     * Trims each element of a comma-separated list of strings, removes empty elements and
     * returns the result (as an array).
     *
     * @param string $parameters The unfiltered list.
     * @return array The filtered list of strings as an array.
     */
    private static function filterBlankFromCommaSepList($parameters)
    {
        $parameters = explode(',', $parameters);
        $parameters = array_filter($parameters, 'strlen');
        $parameters = array_unique($parameters);
        return $parameters;
    }

    /**
     * Returns the hosts alias URLs
     * @param int $idSite
     * @return array
     */
    private function getTrackerHosts($urls)
    {
        $hosts = [];
        foreach ($urls as $url) {
            $url = parse_url($url);
            if (isset($url['host'])) {
                $hosts[] = $url['host'];
            }
        }
        return $hosts;
    }

    public function getClientSideTranslationKeys(&$translationKeys)
    {
        $translationKeys[] = 'Actions_SubmenuSitesearch';
        $translationKeys[] = 'General_Actions';
        $translationKeys[] = 'General_Cancel';
        $translationKeys[] = 'General_ClickToSearch';
        $translationKeys[] = 'General_Loading';
        $translationKeys[] = 'General_Measurables';
        $translationKeys[] = 'General_Next';
        $translationKeys[] = 'General_OrCancel';
        $translationKeys[] = 'General_Pagination';
        $translationKeys[] = 'General_PaginationWithoutTotal';
        $translationKeys[] = 'General_Previous';
        $translationKeys[] = 'General_Save';
        $translationKeys[] = 'General_Search';
        $translationKeys[] = 'General_Share';
        $translationKeys[] = 'Goals_Ecommerce';
        $translationKeys[] = 'Goals_Optional';
        $translationKeys[] = 'SitesManager_AddMeasurable';
        $translationKeys[] = 'SitesManager_AddSite';
        $translationKeys[] = 'SitesManager_AdvancedTimezoneSupportNotFound';
        $translationKeys[] = 'SitesManager_AliasUrlHelp';
        $translationKeys[] = 'SitesManager_ChangingYourTimezoneWillOnlyAffectDataForward';
        $translationKeys[] = 'SitesManager_ChooseMeasurableTypeHeadline';
        $translationKeys[] = 'SitesManager_Currency';
        $translationKeys[] = 'SitesManager_CurrencySymbolWillBeUsedForGoals';
        $translationKeys[] = 'SitesManager_DefaultCurrencyForNewWebsites';
        $translationKeys[] = 'SitesManager_DefaultTimezoneForNewWebsites';
        $translationKeys[] = 'SitesManager_DeleteConfirm';
        $translationKeys[] = 'SitesManager_DeleteSiteExplanation';
        $translationKeys[] = 'SitesManager_DemoSiteButtonText';
        $translationKeys[] = 'SitesManager_DetectingYourSite';
        $translationKeys[] = 'SitesManager_DisableSiteSearch';
        $translationKeys[] = 'SitesManager_EcommerceHelp';
        $translationKeys[] = 'SitesManager_EmailInstructionsButton';
        $translationKeys[] = 'SitesManager_EmailInstructionsButtonText';
        $translationKeys[] = 'SitesManager_EmailInstructionsSubject';
        $translationKeys[] = 'SitesManager_EnableEcommerce';
        $translationKeys[] = 'SitesManager_EnableSiteSearch';
        $translationKeys[] = 'SitesManager_ExcludedIps';
        $translationKeys[] = 'SitesManager_ExcludedParameters';
        $translationKeys[] = 'SitesManager_ExcludedReferrers';
        $translationKeys[] = 'SitesManager_ExcludedReferrersHelp';
        $translationKeys[] = 'SitesManager_ExcludedReferrersHelpDetails';
        $translationKeys[] = 'SitesManager_ExcludedReferrersHelpExamples';
        $translationKeys[] = 'SitesManager_ExcludedReferrersHelpSubDomains';
        $translationKeys[] = 'SitesManager_ExcludedUserAgents';
        $translationKeys[] = 'SitesManager_GlobalExcludedUserAgentHelp1';
        $translationKeys[] = 'SitesManager_GlobalExcludedUserAgentHelp2';
        $translationKeys[] = 'SitesManager_GlobalExcludedUserAgentHelp3';
        $translationKeys[] = 'SitesManager_GlobalListExcludedIps';
        $translationKeys[] = 'SitesManager_GlobalListExcludedQueryParameters';
        $translationKeys[] = 'SitesManager_GlobalListExcludedReferrers';
        $translationKeys[] = 'SitesManager_GlobalListExcludedReferrersDesc';
        $translationKeys[] = 'SitesManager_GlobalListExcludedUserAgents';
        $translationKeys[] = 'SitesManager_GlobalListExcludedUserAgents_Desc';
        $translationKeys[] = 'SitesManager_GlobalWebsitesSettings';
        $translationKeys[] = 'SitesManager_HelpExcludedIpAddresses';
        $translationKeys[] = 'SitesManager_JsTrackingTagHelp';
        $translationKeys[] = 'SitesManager_KeepURLFragments';
        $translationKeys[] = 'SitesManager_KeepURLFragmentsHelp';
        $translationKeys[] = 'SitesManager_KeepURLFragmentsHelp2';
        $translationKeys[] = 'SitesManager_KeepURLFragmentsLong';
        $translationKeys[] = 'SitesManager_ListOfIpsToBeExcludedOnAllWebsites';
        $translationKeys[] = 'SitesManager_ListOfQueryParametersToBeExcludedOnAllWebsites';
        $translationKeys[] = 'SitesManager_ListOfQueryParametersToExclude';
        $translationKeys[] = 'SitesManager_MainDescription';
        $translationKeys[] = 'SitesManager_NotAnEcommerceSite';
        $translationKeys[] = 'SitesManager_NotFound';
        $translationKeys[] = 'SitesManager_OnlyMatchedUrlsAllowed';
        $translationKeys[] = 'SitesManager_OnlyMatchedUrlsAllowedHelp';
        $translationKeys[] = 'SitesManager_OnlyMatchedUrlsAllowedHelpExamples';
        $translationKeys[] = 'SitesManager_OnlyOneSiteAtTime';
        $translationKeys[] = 'SitesManager_PiwikOffersEcommerceAnalytics';
        $translationKeys[] = 'SitesManager_PiwikWillAutomaticallyExcludeCommonSessionParameters';
        $translationKeys[] = 'SitesManager_SearchCategoryDesc';
        $translationKeys[] = 'SitesManager_SearchCategoryLabel';
        $translationKeys[] = 'SitesManager_SearchCategoryParametersDesc';
        $translationKeys[] = 'SitesManager_SearchKeywordLabel';
        $translationKeys[] = 'SitesManager_SearchKeywordParametersDesc';
        $translationKeys[] = 'SitesManager_SearchParametersNote';
        $translationKeys[] = 'SitesManager_SearchParametersNote2';
        $translationKeys[] = 'SitesManager_SearchUseDefault';
        $translationKeys[] = 'SitesManager_SelectDefaultCurrency';
        $translationKeys[] = 'SitesManager_SelectDefaultTimezone';
        $translationKeys[] = 'SitesManager_ShowTrackingTag';
        $translationKeys[] = 'SitesManager_SiteSearchUse';
        $translationKeys[] = 'SitesManager_SiteWithoutDataChooseTrackingMethod';
        $translationKeys[] = 'SitesManager_SiteWithoutDataGoogleTagManager';
        $translationKeys[] = 'SitesManager_SiteWithoutDataGoogleTagManagerDescription';
        $translationKeys[] = 'SitesManager_SiteWithoutDataHidePageForHour';
        $translationKeys[] = 'SitesManager_SiteWithoutDataNotYetReady';
        $translationKeys[] = 'SitesManager_SiteWithoutDataOtherInstallMethodsIntro';
        $translationKeys[] = 'SitesManager_SiteWithoutDataReactDescription';
        $translationKeys[] = 'SitesManager_SiteWithoutDataTemporarilyHidePage';
        $translationKeys[] = 'SitesManager_SiteWithoutDataVueDescription';
        $translationKeys[] = 'SitesManager_SiteWithoutDataWordpressDescription';
        $translationKeys[] = 'SitesManager_Sites';
        $translationKeys[] = 'SitesManager_StepByStepGuide';
        $translationKeys[] = 'SitesManager_SuperUserAccessCan';
        $translationKeys[] = 'SitesManager_Timezone';
        $translationKeys[] = 'SitesManager_TrackingSiteSearch';
        $translationKeys[] = 'SitesManager_Type';
        $translationKeys[] = 'SitesManager_UTCTimeIs';
        $translationKeys[] = 'SitesManager_Urls';
        $translationKeys[] = 'SitesManager_WebsiteCreated';
        $translationKeys[] = 'SitesManager_WebsiteUpdated';
        $translationKeys[] = 'SitesManager_WebsitesManagement';
        $translationKeys[] = 'SitesManager_XManagement';
        $translationKeys[] = 'SitesManager_YouCurrentlyHaveAccessToNWebsites';
        $translationKeys[] = 'SitesManager_YourCurrentIpAddressIs';
        $translationKeys[] = 'UsersManager_InviteTeamMember';
        $translationKeys[] = 'SitesManager_SiteWithoutDataOtherInstallMethods';
        $translationKeys[] = 'Mobile_NavigationBack';
        $translationKeys[] = 'SitesManager_SiteWithoutDataInstallWithX';
    }

    public static function renderTrackingCodeEmail(int $idSite)
    {
        $javascriptGenerator = new TrackerCodeGenerator();
        $javascriptGenerator->forceMatomoEndpoint();
        $matomoUrl = Url::getCurrentUrlWithoutFileName();

        $jsTag = Request::processRequest(
            'SitesManager.getJavascriptTag',
            ['idSite' => $idSite, 'piwikUrl' => $matomoUrl]
        );

        // Strip off open and close <script> tag and comments so that JS will be displayed in ALL mail clients
        $rawJsTag = TrackerCodeGenerator::stripTags($jsTag);

        $showMatomoLinks = true;
        /**
         * @ignore
         */
        Piwik::postEvent('SitesManager.showMatomoLinksInTrackingCodeEmail', [&$showMatomoLinks]);

        $trackerCodeGenerator = new TrackerCodeGenerator();
        $trackingUrl = trim(SettingsPiwik::getPiwikUrl(), '/') . '/' . $trackerCodeGenerator->getPhpTrackerEndpoint();

        $emailTemplateData = [
            'jsTag' => $rawJsTag,
            'showMatomoLinks' => $showMatomoLinks,
            'trackingUrl' => $trackingUrl,
            'idSite' => $idSite,
            'consentManagerName' => false,
        ];

        $siteContentDetector = StaticContainer::get(SiteContentDetector::class);

        $siteContentDetector->detectContent([], $idSite);
        $detectedConsentManagers = $siteContentDetector->getDetectsByType(SiteContentDetectionAbstract::TYPE_CONSENT_MANAGER);
        if (!empty($detectedConsentManagers)) {
            $consentManagerId = reset($detectedConsentManagers);
            $consentManager = $siteContentDetector->getSiteContentDetectionById($consentManagerId);
            $emailTemplateData['consentManagerName'] = $consentManager::getName();
            $emailTemplateData['consentManagerUrl'] = $consentManager::getInstructionUrl();
        }
        $emailTemplateData['cms'] = $siteContentDetector->getDetectsByType(SiteContentDetectionAbstract::TYPE_CMS);
        $emailTemplateData['jsFrameworks'] = $siteContentDetector->getDetectsByType(SiteContentDetectionAbstract::TYPE_JS_FRAMEWORK);
        $emailTemplateData['trackers'] = $siteContentDetector->getDetectsByType(SiteContentDetectionAbstract::TYPE_TRACKER);

        $view = new View('@SitesManager/_trackingCodeEmail');
        $view->assign($emailTemplateData);

        return $view->render();
    }
}
