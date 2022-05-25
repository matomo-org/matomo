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
use Piwik\Session;
use Piwik\SettingsPiwik;
use Piwik\Tracker\TrackerCodeGenerator;
use Piwik\Url;
use Piwik\Http;
use Piwik\Plugins\SitesManager\GtmSiteTypeGuesser;
use Matomo\Cache\Lazy;
use Psr\Log\LoggerInterface;

/**
 *
 */
class Controller extends \Piwik\Plugin\ControllerAdmin
{
    /** @var Lazy */
    private $cache;

    public function __construct(Lazy $cache) {
        $this->cache = $cache;

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

        $globalSettings = array();
        $globalSettings['keepURLFragmentsGlobal'] = API::getInstance()->getKeepURLFragmentsGlobal();
        $globalSettings['defaultCurrency'] = API::getInstance()->getDefaultCurrency();
        $globalSettings['searchKeywordParametersGlobal'] = API::getInstance()->getSearchKeywordParametersGlobal();
        $globalSettings['searchCategoryParametersGlobal'] = API::getInstance()->getSearchCategoryParametersGlobal();
        $globalSettings['defaultTimezone'] = API::getInstance()->getDefaultTimezone();
        $globalSettings['excludedIpsGlobal'] = API::getInstance()->getExcludedIpsGlobal();
        $globalSettings['excludedQueryParametersGlobal'] = API::getInstance()->getExcludedQueryParametersGlobal();
        $globalSettings['excludedUserAgentsGlobal'] = API::getInstance()->getExcludedUserAgentsGlobal();

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

        $jsTag = Request::processRequest('SitesManager.getJavascriptTag', array('idSite' => $this->idSite, 'piwikUrl' => $piwikUrl));

        // Strip off open and close <script> tag and comments so that JS will be displayed in ALL mail clients
        $rawJsTag = TrackerCodeGenerator::stripTags($jsTag);

        $showMatomoLinks = true;
        /**
         * @ignore
         */
        Piwik::postEvent('SitesManager.showMatomoLinksInTrackingCodeEmail', array(&$showMatomoLinks));

        $trackerCodeGenerator = new TrackerCodeGenerator();
        $trackingUrl = trim(SettingsPiwik::getPiwikUrl(), '/') . '/' . $trackerCodeGenerator->getPhpTrackerEndpoint();

        $emailContent = $this->renderTemplateAs('@SitesManager/_trackingCodeEmail', array(
            'jsTag' => $rawJsTag,
            'showMatomoLinks' => $showMatomoLinks,
            'trackingUrl' => $trackingUrl,
            'idSite' => $this->idSite
        ), $viewType = 'basic');

        return $this->renderTemplateAs('siteWithoutData', array(
            'siteName'      => $this->site->getName(),
            'idSite'        => $this->idSite,
            'piwikUrl'      => $piwikUrl,
            'emailBody'     => $emailContent,
        ), $viewType = 'basic');
    }

    public function siteWithoutDataTabs() {
        $this->checkSitePermission();

        $mainUrl = $this->site->getMainUrl();
        $typeCacheId = 'guessedtype_' . md5($mainUrl);
        $gtmCacheId = 'guessedgtm_' . md5($mainUrl);

        $siteType = $this->cache->fetch($typeCacheId);
        $gtmUsed = $this->cache->fetch($gtmCacheId);

        if (!$siteType) {
            try {
                $response = false;
                $parsedUrl = parse_url($mainUrl);

                // do not try to determine the site type for localhost or any IP
                if (!empty($parsedUrl['host']) && !Url::isLocalHost($parsedUrl['host']) && !filter_var($parsedUrl['host'], FILTER_VALIDATE_IP)) {
                    $response = Http::sendHttpRequest($mainUrl, 5, null, null, 0, false, false, true);
                }
            } catch (Exception $e) {
                StaticContainer::get(LoggerInterface::class)->info('Unable to fetch site type for host "{host}": {exception}', [
                    'host' => $parsedUrl['host'] ?? 'unknown',
                    'exception' => $e,
                ]);
            }

            $guesser = new GtmSiteTypeGuesser();
            $siteType = $guesser->guessSiteTypeFromResponse($response);
            $gtmUsed = $guesser->guessGtmFromResponse($response);

            $this->cache->save($typeCacheId, $siteType, 60 * 60 * 24);
            $this->cache->save($gtmCacheId, $gtmUsed, 60 * 60 * 24);
        }

        $instructionUrl = SitesManager::getInstructionUrlBySiteType($siteType);

        $piwikUrl = Url::getCurrentUrlWithoutFileName();
        $jsTag = Request::processRequest('SitesManager.getJavascriptTag', array('idSite' => $this->idSite, 'piwikUrl' => $piwikUrl));

        $showMatomoLinks = true;
        /**
         * @ignore
         */
        Piwik::postEvent('SitesManager.showMatomoLinksInTrackingCodeEmail', array(&$showMatomoLinks));

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

        return $this->renderTemplateAs('_siteWithoutDataTabs', array(
            'siteName'      => $this->site->getName(),
            'idSite'        => $this->idSite,
            'jsTag'         => $jsTag,
            'piwikUrl'      => $piwikUrl,
            'showMatomoLinks' => $showMatomoLinks,
            'siteType' => $siteType,
            'instructionUrl' => $instructionUrl,
            'gtmUsed' => $gtmUsed,
            'googleAnalyticsImporterMessage' => $googleAnalyticsImporterMessage,
            'tagManagerActive' => $tagManagerActive,
        ), $viewType = 'basic');
    }
}
