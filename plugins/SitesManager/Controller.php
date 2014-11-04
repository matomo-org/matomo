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
use Piwik\Piwik;
use Piwik\SettingsPiwik;
use Piwik\Site;
use Piwik\Tracker\TrackerCodeGenerator;
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
        return $this->renderTemplate('index');
    }

    public function getGlobalSettings() {

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
}
