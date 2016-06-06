<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\SitesManager;

use Exception;
use Piwik\API\ResponseBuilder;
use Piwik\Common;
use Piwik\Exception\UnexpectedWebsiteFoundException;
use Piwik\Piwik;
use Piwik\Measurable\MeasurableSettings;
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

        return $this->renderTemplate('index');
    }

    public function getMeasurableTypeSettings()
    {
        $idSite = Common::getRequestVar('idSite', 0, 'int');
        $idType = Common::getRequestVar('idType', '', 'string');

        if ($idSite >= 1) {
            Piwik::checkUserHasAdminAccess($idSite);
        } else if ($idSite === 0) {
            Piwik::checkUserHasSomeAdminAccess();
        } else {
            throw new Exception('Invalid idSite parameter. IdSite has to be zero or higher');
        }

        $view = new View('@SitesManager/measurable_type_settings');

        $propSettings   = new MeasurableSettings($idSite, $idType);
        $view->settings = $propSettings->getSettingsForCurrentUser();

        return $view->render();
    }

    public function getGlobalSettings()
    {
        Piwik::checkUserHasSomeViewAccess();

        $response = new ResponseBuilder(Common::getRequestVar('format'));

        $globalSettings = array();
        $globalSettings['keepURLFragmentsGlobal'] = API::getInstance()->getKeepURLFragmentsGlobal();
        $globalSettings['siteSpecificUserAgentExcludeEnabled'] = API::getInstance()->isSiteSpecificUserAgentExcludeEnabled();
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
            $enableSiteUserAgentExclude = Common::getRequestVar('enableSiteUserAgentExclude', $default = 0);
            $keepURLFragments = Common::getRequestVar('keepURLFragments', $default = 0);

            $api = API::getInstance();
            $api->setDefaultTimezone($timezone);
            $api->setDefaultCurrency($currency);
            $api->setGlobalExcludedQueryParameters($excludedQueryParameters);
            $api->setGlobalExcludedIps($excludedIps);
            $api->setGlobalExcludedUserAgents($excludedUserAgents);
            $api->setGlobalSearchParameters($searchKeywordParameters, $searchCategoryParameters);
            $api->setSiteSpecificUserAgentExcludeEnabled($enableSiteUserAgentExclude == 1);
            $api->setKeepURLFragmentsGlobal($keepURLFragments);

            $toReturn = $response->getResponse();
        } catch (Exception $e) {
            $toReturn = $response->getResponseException($e);
        }

        return $toReturn;
    }

    /**
     * Displays the admin UI page showing all tracking tags
     * @return string
     */
    function displayJavascriptCode()
    {
        $idSite = Common::getRequestVar('idSite');
        Piwik::checkUserHasViewAccess($idSite);
        $javascriptGenerator = new TrackerCodeGenerator();
        $jsTag = $javascriptGenerator->generate($idSite, SettingsPiwik::getPiwikUrl());
        $site  = new Site($idSite);

        return $this->renderTemplate('displayJavascriptCode', array(
            'idSite' => $idSite,
            'displaySiteName' => $site->getName(),
            'jsTag' => $jsTag
        ));
    }

    /**
     *  User will download a file called PiwikTracker.php that is the content of the actual script
     */
    function downloadPiwikTracker()
    {
        $path = PIWIK_INCLUDE_PATH . '/libs/PiwikTracker/';
        $filename = 'PiwikTracker.php';
        header('Content-type: text/php');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        return file_get_contents($path . $filename);
    }

    public function siteWithoutData()
    {
        $javascriptGenerator = new TrackerCodeGenerator();
        $piwikUrl = Url::getCurrentUrlWithoutFileName();

        if (!$this->site) {
            throw new UnexpectedWebsiteFoundException('Invalid site ' . $this->idSite);
        }

        return $this->renderTemplate('siteWithoutData', array(
            'siteName'     => $this->site->getName(),
            'trackingHelp' => $this->renderTemplate('_displayJavascriptCode', array(
                'displaySiteName' => Common::unsanitizeInputValue($this->site->getName()),
                'jsTag'           => $javascriptGenerator->generate($this->idSite, $piwikUrl),
                'idSite'          => $this->idSite,
                'piwikUrl'        => $piwikUrl,
            )),
        ));
    }
}
