<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_SitesManager
 */

/**
 *
 * @package Piwik_SitesManager
 */
class Piwik_SitesManager_Controller extends Piwik_Controller_Admin
{
    /*
     * Main view showing listing of websites and settings
     */
    function index()
    {
        $view = Piwik_View::factory('SitesManager');

        if (Piwik::isUserIsSuperUser()) {
            $sites = Piwik_SitesManager_API::getInstance()->getAllSites();
            Piwik_Site::setSites($sites);
            $sites = array_values($sites);
        } else {
            $sites = Piwik_SitesManager_API::getInstance()->getSitesWithAdminAccess();
            Piwik_Site::setSitesFromArray($sites);
        }

        foreach ($sites as &$site) {
            $site['alias_urls'] = Piwik_SitesManager_API::getInstance()->getSiteUrlsFromId($site['idsite']);
            $site['excluded_ips'] = str_replace(',', '<br/>', $site['excluded_ips']);
            $site['excluded_parameters'] = str_replace(',', '<br/>', $site['excluded_parameters']);
            $site['excluded_user_agents'] = str_replace(',', '<br/>', $site['excluded_user_agents']);
        }
        $view->adminSites = $sites;
        $view->adminSitesCount = count($sites);

        $timezones = Piwik_SitesManager_API::getInstance()->getTimezonesList();
        $view->timezoneSupported = Piwik::isTimezoneSupportEnabled();
        $view->timezones = Piwik_Common::json_encode($timezones);
        $view->defaultTimezone = Piwik_SitesManager_API::getInstance()->getDefaultTimezone();

        $view->currencies = Piwik_Common::json_encode(Piwik_SitesManager_API::getInstance()->getCurrencyList());
        $view->defaultCurrency = Piwik_SitesManager_API::getInstance()->getDefaultCurrency();

        $view->utcTime = Piwik_Date::now()->getDatetime();
        $excludedIpsGlobal = Piwik_SitesManager_API::getInstance()->getExcludedIpsGlobal();
        $view->globalExcludedIps = str_replace(',', "\n", $excludedIpsGlobal);
        $excludedQueryParametersGlobal = Piwik_SitesManager_API::getInstance()->getExcludedQueryParametersGlobal();
        $view->globalExcludedQueryParameters = str_replace(',', "\n", $excludedQueryParametersGlobal);

        $globalExcludedUserAgents = Piwik_SitesManager_API::getInstance()->getExcludedUserAgentsGlobal();
        $view->globalExcludedUserAgents = str_replace(',', "\n", $globalExcludedUserAgents);

        $view->globalSearchKeywordParameters = Piwik_SitesManager_API::getInstance()->getSearchKeywordParametersGlobal();
        $view->globalSearchCategoryParameters = Piwik_SitesManager_API::getInstance()->getSearchCategoryParametersGlobal();
        $view->isSearchCategoryTrackingEnabled = Piwik_PluginsManager::getInstance()->isPluginActivated('CustomVariables');
        $view->allowSiteSpecificUserAgentExclude =
            Piwik_SitesManager_API::getInstance()->isSiteSpecificUserAgentExcludeEnabled();

        $view->globalKeepURLFragments = Piwik_SitesManager_API::getInstance()->getKeepURLFragmentsGlobal();

        $view->currentIpAddress = Piwik_IP::getIpFromHeader();

        $view->showAddSite = (boolean)Piwik_Common::getRequestVar('showaddsite', false);

        $this->setBasicVariablesView($view);
        $view->menu = Piwik_GetAdminMenu();
        echo $view->render();
    }

    /*
     * Records Global settings when user submit changes
     */
    function setGlobalSettings()
    {
        $response = new Piwik_API_ResponseBuilder(Piwik_Common::getRequestVar('format'));

        try {
            $this->checkTokenInUrl();
            $timezone = Piwik_Common::getRequestVar('timezone', false);
            $excludedIps = Piwik_Common::getRequestVar('excludedIps', false);
            $excludedQueryParameters = Piwik_Common::getRequestVar('excludedQueryParameters', false);
            $excludedUserAgents = Piwik_Common::getRequestVar('excludedUserAgents', false);
            $currency = Piwik_Common::getRequestVar('currency', false);
            $searchKeywordParameters = Piwik_Common::getRequestVar('searchKeywordParameters', $default = "");
            $searchCategoryParameters = Piwik_Common::getRequestVar('searchCategoryParameters', $default = "");
            $enableSiteUserAgentExclude = Piwik_Common::getRequestVar('enableSiteUserAgentExclude', $default = 0);
            $keepURLFragments = Piwik_Common::getRequestVar('keepURLFragments', $default = 0);

            $api = Piwik_SitesManager_API::getInstance();
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
        echo $toReturn;
    }

    /**
     * Displays the admin UI page showing all tracking tags
     * @return unknown_type
     */
    function displayJavascriptCode()
    {
        $idSite = Piwik_Common::getRequestVar('idSite');
        Piwik::checkUserHasViewAccess($idSite);
        $jsTag = Piwik::getJavascriptCode($idSite, Piwik_Url::getCurrentUrlWithoutFileName());
        $view = Piwik_View::factory('Tracking');
        $this->setBasicVariablesView($view);
        $view->menu = Piwik_GetAdminMenu();
        $view->idSite = $idSite;
        $site = new Piwik_Site($idSite);
        $view->displaySiteName = $site->getName();
        $view->jsTag = $jsTag;
        echo $view->render();
    }

    /*
     *  User will download a file called PiwikTracker.php that is the content of the actual script
     */
    function downloadPiwikTracker()
    {
        $path = PIWIK_INCLUDE_PATH . '/libs/PiwikTracker/';
        $filename = 'PiwikTracker.php';
        header('Content-type: text/php');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo file_get_contents($path . $filename);
    }

    /**
     * Used to generate the doc at http://piwik.org/docs/tracking-api/
     */
    function displayAlternativeTagsHelp()
    {
        $view = Piwik_View::factory('DisplayAlternativeTags');
        $view->idSite = Piwik_Common::getRequestVar('idSite');
        $url = Piwik_Common::getRequestVar('piwikUrl', '', 'string');
        if (empty($url)
            || !Piwik_Common::isLookLikeUrl($url)
        ) {
            $url = $view->piwikUrl;
        }
        $view->piwikUrlRequest = $url;
        $view->calledExternally = true;
        echo $view->render();
    }

    function getSitesForAutocompleter()
    {
        $pattern = Piwik_Common::getRequestVar('term');
        $sites = Piwik_SitesManager_API::getInstance()->getPatternMatchSites($pattern);
        $pattern = str_replace('%', '', $pattern);
        if (!count($sites)) {
            $results[] = array('label' => Piwik_Translate('SitesManager_NotFound') . "&nbsp;<span class='autocompleteMatched'>$pattern</span>.", 'id' => '#');
        } else {
            if (strpos($pattern, '/') !== false
                && strpos($pattern, '\\/') === false
            ) {
                $pattern = str_replace('/', '\\/', $pattern);
            }
            foreach ($sites as $s) {
                $hl_name = $s['name'];
                if (strlen($pattern) > 0) {
                    @preg_match_all("/$pattern+/i", $hl_name, $matches);
                    if (is_array($matches[0]) && count($matches[0]) >= 1) {
                        foreach ($matches[0] as $match) {
                            $hl_name = str_replace($match, '<span class="autocompleteMatched">' . $match . '</span>', $s['name']);
                        }
                    }
                }
                $results[] = array('label' => $hl_name, 'id' => $s['idsite'], 'name' => $s['name']);
            }
        }

        Piwik_DataTable_Renderer_Json::sendHeaderJSON();
        print Piwik_Common::json_encode($results);
    }
}
