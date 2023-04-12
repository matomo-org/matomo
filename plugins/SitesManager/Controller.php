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
use Piwik\Piwik;
use Piwik\Plugin\Manager;
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
            'gtmUsed' => false
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

        $emailContent = $this->renderTemplateAs('@SitesManager/_trackingCodeEmail', $emailTemplateData, $viewType = 'basic');

        return $this->renderTemplateAs('siteWithoutData', [
            'siteName'           => $this->site->getName(),
            'idSite'             => $this->idSite,
            'piwikUrl'           => $piwikUrl,
            'emailBody'          => $emailContent,
        ], $viewType = 'basic');
    }

    public function siteWithoutDataTabs()
    {
        $this->checkSitePermission();

        $piwikUrl = Url::getCurrentUrlWithoutFileName();
        $jsTag = Request::processRequest('SitesManager.getJavascriptTag', ['idSite' => $this->idSite, 'piwikUrl' => $piwikUrl]);

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
        ];

        if ($this->siteContentDetector->consentManagerId) {
            $templateData['consentManagerName'] = $this->siteContentDetector->consentManagerName;
            $templateData['consentManagerUrl'] = $this->siteContentDetector->consentManagerUrl;
            $templateData['consentManagerIsConnected'] = $this->siteContentDetector->isConnected;
        }

        $templateData['activeTab'] = $this->getActiveTabOnLoad($templateData);

        return $this->renderTemplateAs('_siteWithoutDataTabs', $templateData, $viewType = 'basic');
    }

    private function getActiveTabOnLoad($templateData)
    {
        $tabToDisplay = '';

        if (!empty($templateData['cloudflare'])) {
            $tabToDisplay = 'cloudflare';
        }

        return $tabToDisplay;
    }
}
