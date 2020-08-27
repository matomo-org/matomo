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
use Piwik\Exception\UnexpectedWebsiteFoundException;
use Piwik\Piwik;
use Piwik\Plugin\Manager;
use Piwik\Session;
use Piwik\Settings\Measurable\MeasurableSettings;
use Piwik\SettingsPiwik;
use Piwik\Site;
use Piwik\Tracker\TrackerCodeGenerator;
use Piwik\Url;
use Piwik\View;

/**
 *
 */
class Controller extends \Piwik\Plugin\ControllerAdmin
{
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
        $javascriptGenerator = new TrackerCodeGenerator();
        $javascriptGenerator->forceMatomoEndpoint();
        $piwikUrl = Url::getCurrentUrlWithoutFileName();

        if (!$this->site && Piwik::hasUserSuperUserAccess()) {
            throw new UnexpectedWebsiteFoundException('Invalid site ' . $this->idSite);
        } elseif (!$this->site) {
            // redirect to login form
            Piwik::checkUserHasViewAccess($this->idSite);
        }

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

        return $this->renderTemplateAs('siteWithoutData', array(
            'siteName'      => $this->site->getName(),
            'idSite'        => $this->idSite,
            'jsTag'         => $jsTag,
            'piwikUrl'      => $piwikUrl,
            'emailBody'     => $emailContent,
            'showMatomoLinks' => $showMatomoLinks,
            'googleAnalyticsImporterMessage' => $googleAnalyticsImporterMessage,
        ), $viewType = 'basic');
    }
}
