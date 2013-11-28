<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package SitesManager
 */
namespace Piwik\Plugins\SitesManager;

use Exception;
use Piwik\API\ResponseBuilder;
use Piwik\Common;
use Piwik\DataTable\Renderer\Json;
use Piwik\Date;
use Piwik\IP;
use Piwik\Piwik;
use Piwik\SettingsServer;
use Piwik\Site;
use Piwik\Url;
use Piwik\UrlHelper;
use Piwik\View;

/**
 *
 * @package SitesManager
 */
class Controller extends \Piwik\Plugin\ControllerAdmin
{
    /**
     * Main view showing listing of websites and settings
     */
    public function index()
    {
        $view = new View('@SitesManager/index');

        Site::clearCache();
        if (Piwik::isUserIsSuperUser()) {
            $sitesRaw = API::getInstance()->getAllSites();
        } else {
            $sitesRaw = API::getInstance()->getSitesWithAdminAccess();
        }
        // Gets sites after Site.setSite hook was called
        $sites = array_values( Site::getSites() );
        if(count($sites) != count($sitesRaw)) {
            throw new Exception("One or more website are missing or invalid.");
        }

        foreach ($sites as &$site) {
            $site['alias_urls'] = API::getInstance()->getSiteUrlsFromId($site['idsite']);
            $site['excluded_ips'] = explode(',', $site['excluded_ips']);
            $site['excluded_parameters'] = explode(',', $site['excluded_parameters']);
            $site['excluded_user_agents'] = explode(',', $site['excluded_user_agents']);
        }
        $view->adminSites = $sites;
        $view->adminSitesCount = count($sites);

        $timezones = API::getInstance()->getTimezonesList();
        $view->timezoneSupported = SettingsServer::isTimezoneSupportEnabled();
        $view->timezones = Common::json_encode($timezones);
        $view->defaultTimezone = API::getInstance()->getDefaultTimezone();

        $view->currencies = Common::json_encode(API::getInstance()->getCurrencyList());
        $view->defaultCurrency = API::getInstance()->getDefaultCurrency();

        $view->utcTime = Date::now()->getDatetime();
        $excludedIpsGlobal = API::getInstance()->getExcludedIpsGlobal();
        $view->globalExcludedIps = str_replace(',', "\n", $excludedIpsGlobal);
        $excludedQueryParametersGlobal = API::getInstance()->getExcludedQueryParametersGlobal();
        $view->globalExcludedQueryParameters = str_replace(',', "\n", $excludedQueryParametersGlobal);

        $globalExcludedUserAgents = API::getInstance()->getExcludedUserAgentsGlobal();
        $view->globalExcludedUserAgents = str_replace(',', "\n", $globalExcludedUserAgents);

        $view->globalSearchKeywordParameters = API::getInstance()->getSearchKeywordParametersGlobal();
        $view->globalSearchCategoryParameters = API::getInstance()->getSearchCategoryParametersGlobal();
        $view->isSearchCategoryTrackingEnabled = \Piwik\Plugin\Manager::getInstance()->isPluginActivated('CustomVariables');
        $view->allowSiteSpecificUserAgentExclude =
            API::getInstance()->isSiteSpecificUserAgentExcludeEnabled();

        $view->globalKeepURLFragments = API::getInstance()->getKeepURLFragmentsGlobal();

        $view->currentIpAddress = IP::getIpFromHeader();

        $view->showAddSite = (boolean)Common::getRequestVar('showaddsite', false);

        $this->setBasicVariablesView($view);
        return $view->render();
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
     * @return void
     */
    function displayJavascriptCode()
    {
        $idSite = Common::getRequestVar('idSite');
        Piwik::checkUserHasViewAccess($idSite);
        $jsTag = Piwik::getJavascriptCode($idSite, Url::getCurrentUrlWithoutFileName());
        $view = new View('@SitesManager/displayJavascriptCode');
        $this->setBasicVariablesView($view);
        $view->idSite = $idSite;
        $site = new Site($idSite);
        $view->displaySiteName = $site->getName();
        $view->jsTag = $jsTag;

        return $view->render();
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

    /**
     * Used to generate the doc at http://piwik.org/docs/tracking-api/
     */
    function displayAlternativeTagsHelp()
    {
        $view = new View('@SitesManager/displayAlternativeTagsHelp');
        $view->idSite = Common::getRequestVar('idSite');
        $url = Common::getRequestVar('piwikUrl', '', 'string');
        if (empty($url)
            || !UrlHelper::isLookLikeUrl($url)
        ) {
            $url = $view->piwikUrl;
        }
        $view->piwikUrlRequest = $url;
        $view->calledExternally = true;
        return $view->render();
    }

    function getSitesForAutocompleter()
    {
        $pattern = Common::getRequestVar('term');
        $sites = API::getInstance()->getPatternMatchSites($pattern);
        $pattern = str_replace('%', '', $pattern);
        if (!count($sites)) {
            $results[] = array('label' => Piwik::translate('SitesManager_NotFound') . "&nbsp;<span class='autocompleteMatched'>$pattern</span>.", 'id' => '#');
        } else {
            if (strpos($pattern, '/') !== false
                && strpos($pattern, '\\/') === false
            ) {
                $pattern = str_replace('/', '\\/', $pattern);
            }
            foreach ($sites as $s) {
                $siteName = Site::getNameFor($s['idsite']);
                $label = $siteName;
                if (strlen($pattern) > 0) {
                    @preg_match_all("/$pattern+/i", $label, $matches);
                    if (is_array($matches[0]) && count($matches[0]) >= 1) {
                        foreach ($matches[0] as $match) {
                            $label = str_replace($match, '<span class="autocompleteMatched">' . $match . '</span>', $siteName);
                        }
                    }
                }
                $results[] = array('label' => $label, 'id' => $s['idsite'], 'name' => $siteName);
            }
        }

        Json::sendHeaderJSON();
        print Common::json_encode($results);
    }
}
