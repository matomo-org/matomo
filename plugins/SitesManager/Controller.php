<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\SitesManager;

use Exception;
use Piwik\API\Request;
use Piwik\API\ResponseBuilder;
use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\Piwik;
use Piwik\Plugin\Manager;
use Piwik\Plugin;
use Piwik\Plugins\CustomVariables\CustomVariables;
use Piwik\Plugins\PrivacyManager\DoNotTrackHeaderChecker;
use Piwik\Site;
use Piwik\SiteContentDetector;
use Piwik\Session;
use Piwik\SettingsPiwik;
use Piwik\Tracker\TrackerCodeGenerator;
use Piwik\Url;
use Matomo\Cache\Lazy;

/**
 *
 */
class Controller extends \Piwik\Plugin\ControllerAdmin
{
    /** @var Lazy */
    private $cache;

    /** @var SiteContentDetector */
    private $siteContentDetector;

    public function __construct(Lazy $cache, SiteContentDetector $siteContentDetector)
    {
        $this->cache = $cache;
        $this->siteContentDetector = $siteContentDetector;

        parent::__construct();
    }

    /**
     * Main view showing listing of websites and settings
     */
    public function index()
    {
        Piwik::checkUserHasSomeAdminAccess();
        SitesManager::dieIfSitesAdminIsDisabled();

        return $this->renderTemplate('index');
    }

    public function globalSettings()
    {
        Piwik::checkUserHasSuperUserAccess();

        return $this->renderTemplate('globalSettings');
    }

    public function getGlobalSettings()
    {
        Piwik::checkUserHasSomeViewAccess();

        $response = new ResponseBuilder(Common::getRequestVar('format'));

        $globalSettings = [];
        $globalSettings['keepURLFragmentsGlobal'] = API::getInstance()->getKeepURLFragmentsGlobal();
        $globalSettings['defaultCurrency'] = API::getInstance()->getDefaultCurrency();
        $globalSettings['searchKeywordParametersGlobal'] = API::getInstance()->getSearchKeywordParametersGlobal();
        $globalSettings['searchCategoryParametersGlobal'] = API::getInstance()->getSearchCategoryParametersGlobal();
        $globalSettings['defaultTimezone'] = API::getInstance()->getDefaultTimezone();
        $globalSettings['excludedIpsGlobal'] = API::getInstance()->getExcludedIpsGlobal();
        $globalSettings['excludedQueryParametersGlobal'] = API::getInstance()->getExcludedQueryParametersGlobal();
        $globalSettings['excludedUserAgentsGlobal'] = API::getInstance()->getExcludedUserAgentsGlobal();
        $globalSettings['excludedReferrersGlobal'] = API::getInstance()->getExcludedReferrersGlobal();

        return $response->getResponse($globalSettings);
    }

    /**
     * Records Global settings when user submit changes
     */
    public function setGlobalSettings()
    {
        $response = new ResponseBuilder(Common::getRequestVar('format'));

        try {
            $this->checkTokenInUrl();
            $timezone = Common::getRequestVar('timezone', false);
            $excludedIps = Common::getRequestVar('excludedIps', false);
            $excludedQueryParameters = Common::getRequestVar('excludedQueryParameters', false);
            $excludedUserAgents = Common::getRequestVar('excludedUserAgents', false);
            $excludedReferrers = Common::getRequestVar('excludedReferrers', false);
            $currency = Common::getRequestVar('currency', false);
            $searchKeywordParameters = Common::getRequestVar('searchKeywordParameters', $default = "");
            $searchCategoryParameters = Common::getRequestVar('searchCategoryParameters', $default = "");
            $keepURLFragments = Common::getRequestVar('keepURLFragments', $default = 0);

            $api = API::getInstance();
            $api->setDefaultTimezone($timezone);
            $api->setDefaultCurrency($currency);
            $api->setGlobalExcludedQueryParameters($excludedQueryParameters);
            $api->setGlobalExcludedIps($excludedIps);
            $api->setGlobalExcludedUserAgents($excludedUserAgents);
            $api->setGlobalExcludedReferrers($excludedReferrers);
            $api->setGlobalSearchParameters($searchKeywordParameters, $searchCategoryParameters);
            $api->setKeepURLFragmentsGlobal($keepURLFragments);

            $toReturn = $response->getResponse();
        } catch (Exception $e) {
            $toReturn = $response->getResponseException($e);
        }

        return $toReturn;
    }

    public function ignoreNoDataMessage()
    {
        Piwik::checkUserHasSomeViewAccess();

        $session = new Session\SessionNamespace('siteWithoutData');
        $session->ignoreMessage = true;
        $session->setExpirationSeconds($oneHour = 60 * 60);

        $url = Url::getCurrentUrlWithoutQueryString() . Url::getCurrentQueryStringWithParametersModified(array('module' => 'CoreHome', 'action' => 'index'));
        Url::redirectToUrl($url);
    }

    public function siteWithoutData()
    {
        $this->checkSitePermission();

        $javascriptGenerator = new TrackerCodeGenerator();
        $javascriptGenerator->forceMatomoEndpoint();
        $piwikUrl = Url::getCurrentUrlWithoutFileName();

        $jsTag = Request::processRequest('SitesManager.getJavascriptTag', ['idSite' => $this->idSite, 'piwikUrl' => $piwikUrl]);

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
            'idSite' => $this->idSite,
            'consentManagerName' => false,
            'cloudflare' => false,
            'ga3Used' => false,
            'ga4Used' => false,
            'gtmUsed' => false,
            'cms' => false,
            'jsFramework' => false,
        ];

        $this->siteContentDetector->detectContent([SiteContentDetector::ALL_CONTENT]);
        if ($this->siteContentDetector->consentManagerId) {
            $emailTemplateData['consentManagerName'] = $this->siteContentDetector->consentManagerName;
            $emailTemplateData['consentManagerUrl'] = $this->siteContentDetector->consentManagerUrl;
        }
        $emailTemplateData['ga3Used'] = $this->siteContentDetector->ga3;
        $emailTemplateData['ga4Used'] = $this->siteContentDetector->ga4;
        $emailTemplateData['gtmUsed'] = $this->siteContentDetector->gtm;
        $emailTemplateData['cloudflare'] = $this->siteContentDetector->cloudflare;
        $emailTemplateData['cms'] = $this->siteContentDetector->cms;
        $emailTemplateData['jsFramework'] = $this->siteContentDetector->jsFramework;

        $emailContent = $this->renderTemplateAs('@SitesManager/_trackingCodeEmail', $emailTemplateData, $viewType = 'basic');
        $inviteUserLink = $this->getInviteUserLink();

        return $this->renderTemplateAs('siteWithoutData', [
            'siteName'                                            => $this->site->getName(),
            'idSite'                                              => $this->idSite,
            'piwikUrl'                                            => $piwikUrl,
            'emailBody'                                           => $emailContent,
            'siteWithoutDataStartTrackingTranslationKey'          => StaticContainer::get('SitesManager.SiteWithoutDataStartTrackingTranslation'),
            'SiteWithoutDataVueFollowStepNote2Key'                => StaticContainer::get('SitesManager.SiteWithoutDataVueFollowStepNote2'),
            'inviteUserLink'                                      => $inviteUserLink
        ], $viewType = 'basic');
    }

    public function siteWithoutDataTabs()
    {
        $this->checkSitePermission();

        $piwikUrl = Url::getCurrentUrlWithoutFileName();
        $jsTag = Request::processRequest('SitesManager.getJavascriptTag', ['idSite' => $this->idSite, 'piwikUrl' => $piwikUrl]);
        $maxCustomVariables = 0;

        if (Plugin\Manager::getInstance()->isPluginActivated('CustomVariables')) {
            $maxCustomVariables = CustomVariables::getNumUsableCustomVariables();
        }

        $showMatomoLinks = true;
        /**
         * @ignore
         */
        Piwik::postEvent('SitesManager.showMatomoLinksInTrackingCodeEmail', [&$showMatomoLinks]);

        $googleAnalyticsImporterMessage = '';
        if (Manager::getInstance()->isPluginLoaded('GoogleAnalyticsImporter')) {
            $googleAnalyticsImporterMessage = '<h3>' . Piwik::translate('CoreAdminHome_ImportFromGoogleAnalytics') . '</h3>'
                . '<p>' . Piwik::translate('CoreAdminHome_ImportFromGoogleAnalyticsDescription', ['<a href="https://plugins.matomo.org/GoogleAnalyticsImporter" rel="noopener noreferrer" target="_blank">', '</a>']) . '</p>'
                . '<p></p>';

            /**
             * @ignore
             */
            Piwik::postEvent('SitesManager.siteWithoutData.customizeImporterMessage', [&$googleAnalyticsImporterMessage]);
        }

        $tagManagerActive = false;
        if (Manager::getInstance()->isPluginActivated('TagManager')) {
            $tagManagerActive = true;
        }
        $this->siteContentDetector->detectContent([SiteContentDetector::ALL_CONTENT], $this->idSite);
        $dntChecker = new DoNotTrackHeaderChecker();

        $templateData = [
            'siteName'      => $this->site->getName(),
            'idSite'        => $this->idSite,
            'jsTag'         => $jsTag,
            'piwikUrl'      => $piwikUrl,
            'showMatomoLinks' => $showMatomoLinks,
            'siteType' => $this->siteContentDetector->cms,
            'instruction' => SitesManager::getInstructionByCms($this->siteContentDetector->cms),
            'gtmUsed' => $this->siteContentDetector->gtm,
            'ga3Used' => $this->siteContentDetector->ga3,
            'ga4Used' => $this->siteContentDetector->ga4,
            'googleAnalyticsImporterMessage' => $googleAnalyticsImporterMessage,
            'tagManagerActive' => $tagManagerActive,
            'consentManagerName' => false,
            'cloudflare' => $this->siteContentDetector->cloudflare,
            'jsFramework' => $this->siteContentDetector->jsFramework,
            'cms' => $this->siteContentDetector->cms,
            'SiteWithoutDataVueFollowStepNote2Key' => StaticContainer::get('SitesManager.SiteWithoutDataVueFollowStepNote2'),
            'defaultSiteDecoded' => [
                'id' => $this->idSite,
                'name' => Common::unsanitizeInputValue(Site::getNameFor($this->idSite)),
            ],
            'maxCustomVariables' => $maxCustomVariables,
            'serverSideDoNotTrackEnabled' => $dntChecker->isActive()
        ];

        $templateData['showGAImportTab'] = $this->shouldShowGAImportTab($templateData);

        if ($this->siteContentDetector->consentManagerId) {
            $templateData['consentManagerName'] = $this->siteContentDetector->consentManagerName;
            $templateData['consentManagerUrl'] = $this->siteContentDetector->consentManagerUrl;
            $templateData['consentManagerIsConnected'] = $this->siteContentDetector->isConnected;
        }

        $templateData['activeTab'] = $this->getActiveTabOnLoad($templateData);

        if ($this->siteContentDetector->jsFramework === SitesManager::JS_FRAMEWORK_VUE) {
            $templateData['vue3Code'] = $this->getVueInitializeCode(3);
            $templateData['vue2Code'] = $this->getVueInitializeCode(2);
        }

        $this->mergeMultipleNotification($templateData);

        return $this->renderTemplateAs('_siteWithoutDataTabs', $templateData, $viewType = 'basic');
    }

    private function getActiveTabOnLoad($templateData)
    {
        $tabToDisplay = '';

        if (!empty($templateData['gtmUsed'])) {
            $tabToDisplay = 'gtm';
        } else if (!empty($templateData['cms']) && $templateData['cms'] === SitesManager::SITE_TYPE_WORDPRESS) {
            $tabToDisplay = 'wordpress';
        } else if (!empty($templateData['showGAImportTab'])) {
            $tabToDisplay = 'ga-import';
        } else if (!empty($templateData['cloudflare'])) {
            $tabToDisplay = 'cloudflare';
        } else if (!empty($templateData['jsFramework']) && $templateData['jsFramework'] === SitesManager::JS_FRAMEWORK_VUE) {
            $tabToDisplay = 'vue';
        } else if (!empty($templateData['jsFramework']) && $templateData['jsFramework'] === SitesManager::JS_FRAMEWORK_REACT && Manager::getInstance()->isPluginActivated('TagManager')) {
            $tabToDisplay = 'react';
        } else if (!empty($templateData['consentManagerName'])) {
            $tabToDisplay = 'consentManager';
        }

        return $tabToDisplay;
    }

    private function getInviteUserLink()
    {
        $idSite = Common::getRequestVar('idSite', 0, 'int');
        if (!$idSite || !Piwik::isUserHasAdminAccess($idSite)) {
            return 'https://matomo.org/faq/general/manage-users/#imanadmin-creating-users';
        }

        return SettingsPiwik::getPiwikUrl() . 'index.php?' . Url::getQueryStringFromParameters([
                'idSite' => $idSite,
                'module' => 'UsersManager',
                'action' => 'index',
            ]);
    }

    private function getVueInitializeCode($vueVersion = '3')
    {
        $piwikUrl = Url::getCurrentUrlWithoutFileName();
        $siteId = Common::getRequestVar('idSite', 1, 'int');
        $configureComment = Piwik::translate('SitesManager_SiteWithoutDataVueFollowStep2ExampleCodeCommentConfigureMatomo');
        $trackViewComment = Piwik::translate('SitesManager_SiteWithoutDataVueFollowStep2ExampleCodeCommentTrackPageView');
        if ($vueVersion == 2) {
            return <<<INST
import { createApp } from 'vue'
import VueMatomo from 'vue-matomo'
import App from './App.vue'

createApp(App)
  .use(VueMatomo, {
    // $configureComment
    host: '$piwikUrl',
    siteId: $siteId,
  })
  .mount('#app')

window._paq.push(['trackPageView']); // $trackViewComment
INST;
        }

        return <<<INST
import Vue from 'vue'
import App from './App.vue'
import VueMatomo from 'vue-matomo'

Vue.use(VueMatomo, {
  host: '$piwikUrl',
  siteId: $siteId
});

new Vue({
  el: '#app',
  router,
  components: {App},
  template: ''
})

window._paq.push(['trackPageView']); // $trackViewComment
INST;
    }

    private function mergeMultipleNotification(&$templateData)
    {
        $isNotificationsMerged = false;
        $bannerMessage = '';
        $guides = [];
        $message = [];

        if ($templateData['ga3Used'] || $templateData['ga4Used']) {
            $message[0] = 'Google Analytics ';
            $ga3GuideUrl =  '<a href="https://matomo.org/faq/how-to/migrate-from-google-analytics-3-to-matomo/" target="_blank" rel="noreferrer noopener">Google Analytics 3</a>';
            $ga4GuideUrl =  '<a href="https://matomo.org/faq/how-to/migrate-from-google-analytics-4-to-matomo/" target="_blank" rel="noreferrer noopener">Google Analytics 4</a>';
            if ($templateData['ga3Used'] && $templateData['ga4Used']) {
                $isNotificationsMerged = true;
                $guides[] = $ga3GuideUrl;
                $guides[] = $ga4GuideUrl;
                $message[0] .= '3 & 4';
            } else {
                $message[0] .= ($templateData['ga3Used'] ? 3 : 4);
                $guides[] = ($templateData['ga3Used'] ? $ga3GuideUrl : $ga4GuideUrl);
            }
        }

        if (!empty($message) && $templateData['consentManagerName']) {
            $isNotificationsMerged = true;
            $message[] = $templateData['consentManagerName'];
            $guides[] =  '<a href="' . $templateData['consentManagerUrl'] . '" target="_blank" rel="noreferrer noopener">' . $templateData['consentManagerName'] . '</a>';
        }

        if (!empty($message)) {
            $bannerMessage = implode(' ' . Piwik::translate('General_And') . ' ', $message);
        }

        if ($isNotificationsMerged && $bannerMessage) {
            $info = [
                'isNotificationsMerged' => $isNotificationsMerged,
                'notificationMessage' => '<p class="fw-bold">' . Piwik::translate('SitesManager_MergedNotificationLine1', [$bannerMessage]) . '</p><p>' . Piwik::translate('SitesManager_MergedNotificationLine2', [(implode(' / ', $guides))]) . '</p>'
            ];

            if (!empty($templateData['consentManagerIsConnected'])) {
                $info['notificationMessage'] .= '<p>' . Piwik::translate('SitesManager_ConsentManagerConnected', [$templateData['consentManagerName']]) . '</p>';
            }
        } else {
            $info = $this->getSingleNotifications($templateData);
        }

        $templateData = array_merge($templateData, $info);
    }

    private function getSingleNotifications(&$templateData)
    {
        $info = ['isNotificationsMerged' => false, 'notificationMessage' => ''];
        if (!empty($templateData['consentManagerName']) ) {
            $info['notificationMessage'] = '<p>' . Piwik::translate('PrivacyManager_ConsentManagerDetected', [$templateData['consentManagerName'], '<a href="' . $templateData['consentManagerUrl'] . '" target="_blank" rel="noreferrer noopener">', '</a>']) . '</p>';
            if (!empty($templateData['consentManagerIsConnected'])) {
                $info['notificationMessage'] .= '<p>' . Piwik::translate('SitesManager_ConsentManagerConnected', [$templateData['consentManagerName']]) . '</p>';
            }
        } else if (!empty($templateData['ga3Used'])) {
            $info['notificationMessage'] = '<p>' . Piwik::translate('SitesManager_GADetected', ['Google Analytics 3', 'GA', '', '', '<a href="https://matomo.org/faq/how-to/migrate-from-google-analytics-3-to-matomo/" target="_blank" rel="noreferrer noopener">', '</a>']) . '</p>';
        } else if (!empty($templateData['ga4Used'])) {
            $info['notificationMessage'] = '<p>' . Piwik::translate('SitesManager_GADetected', ['Google Analytics 4', 'GA', '', '', '<a href="https://matomo.org/faq/how-to/migrate-from-google-analytics-4-to-matomo/" target="_blank" rel="noreferrer noopener">', '</a>']) . '</p>';
        }

        return $info;
    }

    private function shouldShowGAImportTab($templateData)
    {
        if (Piwik::hasUserSuperUserAccess() && Manager::getInstance()->isPluginActivated('GoogleAnalyticsImporter') && (!empty($templateData['ga3Used']) || !empty($templateData['ga4Used']))) {
            return true;
        }

        return false;
    }
}
