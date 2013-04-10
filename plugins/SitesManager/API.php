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
 * The SitesManager API gives you full control on Websites in Piwik (create, update and delete), and many methods to retrieve websites based on various attributes.
 *
 * This API lets you create websites via "addSite", update existing websites via "updateSite" and delete websites via "deleteSite".
 * When creating websites, it can be useful to access internal codes used by Piwik for currencies via "getCurrencyList", or timezones via "getTimezonesList".
 *
 * There are also many ways to request a list of websites: from the website ID via "getSiteFromId" or the site URL via "getSitesIdFromSiteUrl".
 * Often, the most useful technique is to list all websites that are known to a current user, based on the token_auth, via
 * "getSitesWithAdminAccess", "getSitesWithViewAccess" or "getSitesWithAtLeastViewAccess" (which returns both).
 *
 * Some methods will affect all websites globally: "setGlobalExcludedIps" will set the list of IPs to be excluded on all websites,
 * "setGlobalExcludedQueryParameters" will set the list of URL parameters to remove from URLs for all websites.
 * The existing values can be fetched via "getExcludedIpsGlobal" and "getExcludedQueryParametersGlobal".
 * See also the documentation about <a href='http://piwik.org/docs/manage-websites/' target='_blank'>Managing Websites</a> in Piwik.
 * @package Piwik_SitesManager
 */
class Piwik_SitesManager_API
{
    static private $instance = null;
    const DEFAULT_SEARCH_KEYWORD_PARAMETERS = 'q,query,s,search,searchword,k,keyword';

    /**
     * @return Piwik_SitesManager_API
     */
    static public function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    const OPTION_EXCLUDED_IPS_GLOBAL = 'SitesManager_ExcludedIpsGlobal';
    const OPTION_DEFAULT_TIMEZONE = 'SitesManager_DefaultTimezone';
    const OPTION_DEFAULT_CURRENCY = 'SitesManager_DefaultCurrency';
    const OPTION_EXCLUDED_QUERY_PARAMETERS_GLOBAL = 'SitesManager_ExcludedQueryParameters';
    const OPTION_SEARCH_KEYWORD_QUERY_PARAMETERS_GLOBAL = 'SitesManager_SearchKeywordParameters';
    const OPTION_SEARCH_CATEGORY_QUERY_PARAMETERS_GLOBAL = 'SitesManager_SearchCategoryParameters';
    const OPTION_EXCLUDED_USER_AGENTS_GLOBAL = 'SitesManager_ExcludedUserAgentsGlobal';
    const OPTION_SITE_SPECIFIC_USER_AGENT_EXCLUDE_ENABLE = 'SitesManager_EnableSiteSpecificUserAgentExclude';
    const OPTION_KEEP_URL_FRAGMENTS_GLOBAL = 'SitesManager_KeepURLFragmentsGlobal';

    /**
     * Returns the javascript tag for the given idSite.
     * This tag must be included on every page to be tracked by Piwik
     *
     * @param int $idSite
     * @param string $customTitle Custom title given to the pageview
     * @return string The Javascript tag ready to be included on the HTML pages
     */
    public function getJavascriptTag($idSite, $piwikUrl = '')
    {
        Piwik::checkUserHasViewAccess($idSite);

        if (empty($piwikUrl)) {
            $piwikUrl = Piwik_Url::getCurrentUrlWithoutFileName();
        }
        $piwikUrl = Piwik_Common::sanitizeInputValues($piwikUrl);

        $htmlEncoded = Piwik::getJavascriptCode($idSite, $piwikUrl);
        $htmlEncoded = str_replace(array('<br>', '<br />', '<br/>'), '', $htmlEncoded);
        return $htmlEncoded;
    }

    /**
     * Returns all websites belonging to the specified group
     * @param string $group Group name
     */
    public function getSitesFromGroup($group)
    {
        Piwik::checkUserIsSuperUser();
        $group = trim($group);

        $sites = Zend_Registry::get('db')->fetchAll("SELECT *
													FROM " . Piwik_Common::prefixTable("site") . "
													WHERE `group` = ?", $group);
        return $sites;
    }

    /**
     * Returns the list of website groups, including the empty group
     * if no group were specified for some websites
     *
     * @return array of group names strings
     */
    public function getSitesGroups()
    {
        Piwik::checkUserIsSuperUser();
        $groups = Zend_Registry::get('db')->fetchAll("SELECT DISTINCT `group` FROM " . Piwik_Common::prefixTable("site"));
        $cleanedGroups = array();
        foreach ($groups as $group) {
            $cleanedGroups[] = $group['group'];
        }
        $cleanedGroups = array_map('trim', $cleanedGroups);
        return $cleanedGroups;
    }

    /**
     * Returns the website information : name, main_url
     *
     * @exception if the site ID doesn't exist or the user doesn't have access to it
     * @return array
     */
    public function getSiteFromId($idSite)
    {
        Piwik::checkUserHasViewAccess($idSite);
        $site = Zend_Registry::get('db')->fetchRow("SELECT *
													FROM " . Piwik_Common::prefixTable("site") . "
													WHERE idsite = ?", $idSite);
        return $site;
    }

    /**
     * Returns the list of alias URLs registered for the given idSite.
     * The website ID must be valid when calling this method!
     *
     * @return array list of alias URLs
     */
    private function getAliasSiteUrlsFromId($idsite)
    {
        $db = Zend_Registry::get('db');
        $result = $db->fetchAll("SELECT url
								FROM " . Piwik_Common::prefixTable("site_url") . "
								WHERE idsite = ?", $idsite);
        $urls = array();
        foreach ($result as $url) {
            $urls[] = $url['url'];
        }
        return $urls;
    }

    /**
     * Returns the list of all URLs registered for the given idSite (main_url + alias URLs).
     *
     * @exception if the website ID doesn't exist or the user doesn't have access to it
     * @return array list of URLs
     */
    public function getSiteUrlsFromId($idSite)
    {
        Piwik::checkUserHasViewAccess($idSite);
        $site = new Piwik_Site($idSite);
        $urls = $this->getAliasSiteUrlsFromId($idSite);
        return array_merge(array($site->getMainUrl()), $urls);
    }

    /**
     * Returns the list of all the website IDs registered.
     * Caller must check access.
     *
     * @return array The list of website IDs
     */
    private function getSitesId()
    {
        $result = Piwik_FetchAll("SELECT idsite FROM " . Piwik_Common::prefixTable('site'));
        $idSites = array();
        foreach ($result as $idSite) {
            $idSites[] = $idSite['idsite'];
        }
        return $idSites;
    }

    /**
     * Returns all websites, requires Super User access
     *
     * @return array The list of websites, indexed by idsite
     */
    public function getAllSites()
    {
        Piwik::checkUserIsSuperUser();
        $sites = Zend_Registry::get('db')->fetchAll("SELECT * FROM " . Piwik_Common::prefixTable("site"));
        $return = array();
        foreach ($sites as $site) {
            $return[$site['idsite']] = $site;
        }
        return $return;
    }

    /**
     * Returns the list of all the website IDs registered.
     * Requires super user access.
     *
     * @return array The list of website IDs
     */
    public function getAllSitesId()
    {
        Piwik::checkUserIsSuperUser();
        return Piwik_SitesManager_API::getInstance()->getSitesId();
    }

    /**
     * Returns the list of the website IDs that received some visits since the specified timestamp.
     * Requires super user access.
     *
     * @return array The list of website IDs
     */
    public function getSitesIdWithVisits($timestamp = false)
    {
        Piwik::checkUserIsSuperUser();

        if (empty($timestamp)) $timestamp = time();

        $time = Piwik_Date::factory((int)$timestamp)->getDatetime();
        $result = Piwik_FetchAll("
            SELECT
                idsite
            FROM
                " . Piwik_Common::prefixTable('site') . " s
            WHERE EXISTS (
                SELECT 1 
                FROM " . Piwik_Common::prefixTable('log_visit') . " v
                WHERE v.idsite = s.idsite
                AND visit_last_action_time > ?
                AND visit_last_action_time <= ?
                LIMIT 1)
        ", array($time, $now = Piwik_Date::now()->addHour(1)->getDatetime()));
        $idSites = array();
        foreach ($result as $idSite) {
            $idSites[] = $idSite['idsite'];
        }
        return $idSites;
    }


    /**
     * Returns the list of websites with the 'admin' access for the current user.
     * For the superUser it returns all the websites in the database.
     *
     * @return array for each site, an array of information (idsite, name, main_url, etc.)
     */
    public function getSitesWithAdminAccess()
    {
        $sitesId = $this->getSitesIdWithAdminAccess();
        return $this->getSitesFromIds($sitesId);
    }

    /**
     * Returns the list of websites with the 'view' access for the current user.
     * For the superUser it doesn't return any result because the superUser has admin access on all the websites (use getSitesWithAtLeastViewAccess() instead).
     *
     * @return array for each site, an array of information (idsite, name, main_url, etc.)
     */
    public function getSitesWithViewAccess()
    {
        $sitesId = $this->getSitesIdWithViewAccess();
        return $this->getSitesFromIds($sitesId);
    }

    /**
     * Returns the list of websites with the 'view' or 'admin' access for the current user.
     * For the superUser it returns all the websites in the database.
     *
     * @param int $limit Specify max number of sites to return
     * @param bool $_restrictSitesToLogin Hack necessary when runnning scheduled tasks, where "Super User" is forced, but sometimes not desired, see #3017
     * @return array array for each site, an array of information (idsite, name, main_url, etc.)
     */
    public function getSitesWithAtLeastViewAccess($limit = false, $_restrictSitesToLogin = false)
    {
        $sitesId = $this->getSitesIdWithAtLeastViewAccess($_restrictSitesToLogin);
        return $this->getSitesFromIds($sitesId, $limit);
    }

    /**
     * Returns the list of websites ID with the 'admin' access for the current user.
     * For the superUser it returns all the websites in the database.
     *
     * @return array list of websites ID
     */
    public function getSitesIdWithAdminAccess()
    {
        $sitesId = Zend_Registry::get('access')->getSitesIdWithAdminAccess();
        return $sitesId;
    }

    /**
     * Returns the list of websites ID with the 'view' access for the current user.
     * For the superUser it doesn't return any result because the superUser has admin access on all the websites (use getSitesIdWithAtLeastViewAccess() instead).
     *
     * @return array list of websites ID
     */
    public function getSitesIdWithViewAccess()
    {
        return Zend_Registry::get('access')->getSitesIdWithViewAccess();
    }

    /**
     * Returns the list of websites ID with the 'view' or 'admin' access for the current user.
     * For the superUser it returns all the websites in the database.
     *
     * @return array list of websites ID
     */
    public function getSitesIdWithAtLeastViewAccess($_restrictSitesToLogin = false)
    {
        if (!empty($_restrictSitesToLogin)
            // Only super user or logged in user can see viewable sites for a specific login,
            // but during scheduled task execution, we sometimes want to restrict sites to
            // a different login than the superuser.
            && (Piwik::isUserIsSuperUserOrTheUser($_restrictSitesToLogin)
                || Piwik_TaskScheduler::isTaskBeingExecuted())
        ) {
            $accessRaw = Zend_Registry::get('access')->getRawSitesWithSomeViewAccess($_restrictSitesToLogin);
            $sitesId = array();
            foreach ($accessRaw as $access) {
                $sitesId[] = $access['idsite'];
            }
            return $sitesId;
        } else {
            return Zend_Registry::get('access')->getSitesIdWithAtLeastViewAccess();
        }
    }

    /**
     * Returns the list of websites from the ID array in parameters.
     * The user access is not checked in this method so the ID have to be accessible by the user!
     *
     * @param array list of website ID
     */
    private function getSitesFromIds($idSites, $limit = false)
    {
        if (count($idSites) === 0) {
            return array();
        }

        if ($limit) {
            $limit = "LIMIT " . (int)$limit;
        }

        $db = Zend_Registry::get('db');
        $sites = $db->fetchAll("SELECT *
								FROM " . Piwik_Common::prefixTable("site") . "
								WHERE idsite IN (" . implode(", ", $idSites) . ")
								ORDER BY idsite ASC $limit");
        return $sites;
    }

    protected function getNormalizedUrls($url)
    {
        if (strpos($url, 'www.') !== false) {
            $urlBis = str_replace('www.', '', $url);
        } else {
            $urlBis = str_replace('://', '://www.', $url);
        }
        return array($url, $urlBis);
    }

    /**
     * Returns the list of websites ID associated with a URL.
     *
     * @param string $url
     * @return array list of websites ID
     */
    public function getSitesIdFromSiteUrl($url)
    {
        $url = $this->removeTrailingSlash($url);
        list($url, $urlBis) = $this->getNormalizedUrls($url);
        if (Piwik::isUserIsSuperUser()) {
            $ids = Zend_Registry::get('db')->fetchAll(
                'SELECT idsite
                FROM ' . Piwik_Common::prefixTable('site') . '
					WHERE (main_url = ? OR main_url = ?) ' .
                    'UNION
                    SELECT idsite
                    FROM ' . Piwik_Common::prefixTable('site_url') . '
					WHERE (url = ? OR url = ?) ', array($url, $urlBis, $url, $urlBis));
        } else {
            $login = Piwik::getCurrentUserLogin();
            $ids = Zend_Registry::get('db')->fetchAll(
                'SELECT idsite
                FROM ' . Piwik_Common::prefixTable('site') . '
					WHERE (main_url = ? OR main_url = ?)' .
                    'AND idsite IN (' . Piwik_Access::getSqlAccessSite('idsite') . ') ' .
                    'UNION
                    SELECT idsite
                    FROM ' . Piwik_Common::prefixTable('site_url') . '
					WHERE (url = ? OR url = ?)' .
                    'AND idsite IN (' . Piwik_Access::getSqlAccessSite('idsite') . ')',
                array($url, $urlBis, $login, $url, $urlBis, $login));
        }

        return $ids;
    }

    /**
     * Returns all websites with a timezone matching one the specified timezones
     *
     * @param array $timezones
     * @ignore
     */
    public function getSitesIdFromTimezones($timezones)
    {
        Piwik::checkUserIsSuperUser();
        $timezones = Piwik::getArrayFromApiParameter($timezones);
        $timezones = array_unique($timezones);
        $ids = Zend_Registry::get('db')->fetchAll(
            'SELECT idsite
            FROM ' . Piwik_Common::prefixTable('site') . '
					WHERE timezone IN (' . Piwik_Common::getSqlStringFieldsArray($timezones) . ')
					ORDER BY idsite ASC',
            $timezones);
        $return = array();
        foreach ($ids as $id) {
            $return[] = $id['idsite'];
        }
        return $return;
    }

    /**
     * Add a website.
     * Requires Super User access.
     *
     * The website is defined by a name and an array of URLs.
     * @param string Site name
     * @param array|string The URLs array must contain at least one URL called the 'main_url' ;
     *                        if several URLs are provided in the array, they will be recorded
     *                        as Alias URLs for this website.
     * @param int Is Ecommerce Reporting enabled for this website?
     * @param int $sitesearch Whether site search is enabled, 0 or 1
     * @param string $searchKeywordParameters Comma separated list of search keyword parameter names
     * @param string $searchCategoryParameters Comma separated list of search category parameter names
     * @param string Comma separated list of IPs to exclude from the reports (allows wildcards)
     * @param string Timezone string, eg. 'Europe/London'
     * @param string Currency, eg. 'EUR'
     * @param string Website group identifier
     * @param string Date at which the statistics for this website will start. Defaults to today's date in YYYY-MM-DD format
     * @param int $keepURLFragments If 1, URL fragments will be kept when tracking. If 2, they
     *                              will be removed. If 0, the default global behavior will be used.
     * @see getKeepURLFragmentsGlobal.
     *
     * @return int the website ID created
     */
    public function addSite($siteName,
                            $urls,
                            $ecommerce = null,
                            $siteSearch = null,
                            $searchKeywordParameters = null,
                            $searchCategoryParameters = null,
                            $excludedIps = null,
                            $excludedQueryParameters = null,
                            $timezone = null,
                            $currency = null,
                            $group = null,
                            $startDate = null,
                            $excludedUserAgents = null,
                            $keepURLFragments = 0)
    {
        Piwik::checkUserIsSuperUser();

        $this->checkName($siteName);
        $urls = $this->cleanParameterUrls($urls);
        $this->checkUrls($urls);
        $this->checkAtLeastOneUrl($urls);
        $siteSearch = $this->checkSiteSearch($siteSearch);
        list($searchKeywordParameters, $searchCategoryParameters) = $this->checkSiteSearchParameters($searchKeywordParameters, $searchCategoryParameters);

        $keepURLFragments = (int)$keepURLFragments;
        self::checkKeepURLFragmentsValue($keepURLFragments);

        $timezone = trim($timezone);
        if (empty($timezone)) {
            $timezone = $this->getDefaultTimezone();
        }
        $this->checkValidTimezone($timezone);

        if (empty($currency)) {
            $currency = $this->getDefaultCurrency();
        }
        $this->checkValidCurrency($currency);

        $db = Zend_Registry::get('db');

        $url = $urls[0];
        $urls = array_slice($urls, 1);

        $bind = array('name'     => $siteName,
                      'main_url' => $url,

        );

        $bind['excluded_ips'] = $this->checkAndReturnExcludedIps($excludedIps);
        $bind['excluded_parameters'] = $this->checkAndReturnCommaSeparatedStringList($excludedQueryParameters);
        $bind['excluded_user_agents'] = $this->checkAndReturnCommaSeparatedStringList($excludedUserAgents);
        $bind['keep_url_fragment'] = $keepURLFragments;
        $bind['timezone'] = $timezone;
        $bind['currency'] = $currency;
        $bind['ecommerce'] = (int)$ecommerce;
        $bind['sitesearch'] = $siteSearch;
        $bind['sitesearch_keyword_parameters'] = $searchKeywordParameters;
        $bind['sitesearch_category_parameters'] = $searchCategoryParameters;
        $bind['ts_created'] = !is_null($startDate)
            ? Piwik_Date::factory($startDate)->getDatetime()
            : Piwik_Date::now()->getDatetime();

        if (!empty($group)
            && Piwik::isUserIsSuperUser()
        ) {
            $bind['group'] = trim($group);
        } else {
            $bind['group'] = "";
        }

        $db->insert(Piwik_Common::prefixTable("site"), $bind);

        $idSite = $db->lastInsertId();

        $this->insertSiteUrls($idSite, $urls);

        // we reload the access list which doesn't yet take in consideration this new website
        Zend_Registry::get('access')->reloadAccess();
        $this->postUpdateWebsite($idSite);

        Piwik_PostEvent('SitesManager.addSite', $idSite);

        return (int)$idSite;
    }

    private function postUpdateWebsite($idSite)
    {
        Piwik_Site::clearCache();
        Piwik_Tracker_Cache::regenerateCacheWebsiteAttributes($idSite);
    }

    /**
     * Delete a website from the database, given its Id.
     *
     * Requires Super User access.
     *
     * @param int $idSite
     * @throws Exception
     */
    public function deleteSite($idSite)
    {
        Piwik::checkUserIsSuperUser();

        $idSites = Piwik_SitesManager_API::getInstance()->getSitesId();
        if (!in_array($idSite, $idSites)) {
            throw new Exception("website id = $idSite not found");
        }
        $nbSites = count($idSites);
        if ($nbSites == 1) {
            throw new Exception(Piwik_TranslateException("SitesManager_ExceptionDeleteSite"));
        }

        $db = Zend_Registry::get('db');

        $db->query("DELETE FROM " . Piwik_Common::prefixTable("site") . "
					WHERE idsite = ?", $idSite);

        $db->query("DELETE FROM " . Piwik_Common::prefixTable("site_url") . "
					WHERE idsite = ?", $idSite);

        $db->query("DELETE FROM " . Piwik_Common::prefixTable("access") . "
					WHERE idsite = ?", $idSite);

        // we do not delete logs here on purpose (you can run these queries on the log_ tables to delete all data)
        Piwik_Tracker_Cache::deleteCacheWebsiteAttributes($idSite);

        Piwik_PostEvent('SitesManager.deleteSite', $idSite);
    }


    /**
     * Checks that the array has at least one element
     *
     * @param array $urls
     * @throws Exception
     */
    private function checkAtLeastOneUrl($urls)
    {
        if (!is_array($urls)
            || count($urls) == 0
        ) {
            throw new Exception(Piwik_TranslateException("SitesManager_ExceptionNoUrl"));
        }
    }

    private function checkValidTimezone($timezone)
    {
        $timezones = $this->getTimezonesList();
        foreach (array_values($timezones) as $cities) {
            foreach ($cities as $timezoneId => $city) {
                if ($timezoneId == $timezone) {
                    return true;
                }
            }
        }
        throw new Exception(Piwik_TranslateException('SitesManager_ExceptionInvalidTimezone', array($timezone)));
    }

    private function checkValidCurrency($currency)
    {
        if (!in_array($currency, array_keys($this->getCurrencyList()))) {
            throw new Exception(Piwik_TranslateException('SitesManager_ExceptionInvalidCurrency', array($currency, "USD, EUR, etc.")));
        }
    }

    /**
     * Checks that the submitted IPs (comma separated list) are valid
     * Returns the cleaned up IPs
     *
     * @param string $excludedIps Comma separated list of IP addresses
     * @throws Exception
     * @return array of IPs
     */
    private function checkAndReturnExcludedIps($excludedIps)
    {
        if (empty($excludedIps)) {
            return '';
        }
        $ips = explode(',', $excludedIps);
        $ips = array_map('trim', $ips);
        $ips = array_filter($ips, 'strlen');
        foreach ($ips as $ip) {
            if (!$this->isValidIp($ip)) {
                throw new Exception(Piwik_TranslateException('SitesManager_ExceptionInvalidIPFormat', array($ip, "1.2.3.4, 1.2.3.*, or 1.2.3.4/5")));
            }
        }
        $ips = implode(',', $ips);
        return $ips;
    }

    /**
     * Add a list of alias Urls to the given idSite
     *
     * If some URLs given in parameter are already recorded as alias URLs for this website,
     * they won't be duplicated. The 'main_url' of the website won't be affected by this method.
     *
     * @return int the number of inserted URLs
     */
    public function addSiteAliasUrls($idSite, $urls)
    {
        Piwik::checkUserHasAdminAccess($idSite);

        $urls = $this->cleanParameterUrls($urls);
        $this->checkUrls($urls);

        $urlsInit = $this->getSiteUrlsFromId($idSite);
        $toInsert = array_diff($urls, $urlsInit);
        $this->insertSiteUrls($idSite, $toInsert);
        $this->postUpdateWebsite($idSite);

        return count($toInsert);
    }

    /**
     * Get the start and end IP addresses for an IP address range
     *
     * @param string $ipRange IP address range in presentation format
     * @return array|false Array( low, high ) IP addresses in presentation format; or false if error
     */
    public function getIpsForRange($ipRange)
    {
        $range = Piwik_IP::getIpsForRange($ipRange);
        if ($range === false) {
            return false;
        }

        return array(Piwik_IP::N2P($range[0]), Piwik_IP::N2P($range[1]));
    }

    /**
     * Sets IPs to be excluded from all websites. IPs can contain wildcards.
     * Will also apply to websites created in the future.
     *
     * @param string Comma separated list of IPs to exclude from being tracked (allows wildcards)
     * @return bool
     */
    public function setGlobalExcludedIps($excludedIps)
    {
        Piwik::checkUserIsSuperUser();
        $excludedIps = $this->checkAndReturnExcludedIps($excludedIps);
        Piwik_SetOption(self::OPTION_EXCLUDED_IPS_GLOBAL, $excludedIps);
        Piwik_Tracker_Cache::deleteTrackerCache();
        return true;
    }

    /**
     * Sets Site Search keyword/category parameter names, to be used on websites which have not specified these values
     * Expects Comma separated list of query params names
     *
     * @param string
     * @param string
     * @return bool
     */
    public function setGlobalSearchParameters($searchKeywordParameters, $searchCategoryParameters)
    {
        Piwik::checkUserIsSuperUser();
        Piwik_SetOption(self::OPTION_SEARCH_KEYWORD_QUERY_PARAMETERS_GLOBAL, $searchKeywordParameters);
        Piwik_SetOption(self::OPTION_SEARCH_CATEGORY_QUERY_PARAMETERS_GLOBAL, $searchCategoryParameters);
        Piwik_Tracker_Cache::deleteTrackerCache();
        return true;
    }

    /**
     * @return string Comma separated list of URL parameters
     */
    public function getSearchKeywordParametersGlobal()
    {
        Piwik::checkUserHasSomeAdminAccess();
        $names = Piwik_GetOption(self::OPTION_SEARCH_KEYWORD_QUERY_PARAMETERS_GLOBAL);
        if ($names === false) {
            $names = self::DEFAULT_SEARCH_KEYWORD_PARAMETERS;
        }
        if (empty($names)) {
            $names = '';
        }
        return $names;
    }

    /**
     * @return string Comma separated list of URL parameters
     */
    public function getSearchCategoryParametersGlobal()
    {
        Piwik::checkUserHasSomeAdminAccess();
        return Piwik_GetOption(self::OPTION_SEARCH_CATEGORY_QUERY_PARAMETERS_GLOBAL);
    }

    /**
     * Returns the list of URL query parameters that are excluded from all websites
     *
     * @return string Comma separated list of URL parameters
     */
    public function getExcludedQueryParametersGlobal()
    {
        Piwik::checkUserHasSomeViewAccess();
        return Piwik_GetOption(self::OPTION_EXCLUDED_QUERY_PARAMETERS_GLOBAL);
    }

    /**
     * Returns the list of user agent substrings to look for when excluding visits for
     * all websites. If a visitor's user agent string contains one of these substrings,
     * their visits will not be included.
     *
     * @return string Comma separated list of strings.
     */
    public function getExcludedUserAgentsGlobal()
    {
        Piwik::checkUserHasSomeAdminAccess();
        return Piwik_GetOption(self::OPTION_EXCLUDED_USER_AGENTS_GLOBAL);
    }

    /**
     * Sets list of user agent substrings to look for when excluding visits. For more info,
     * @see getExcludedUserAgentsGlobal.
     *
     * @param string $excludedUserAgents Comma separated list of strings. Each element is trimmed,
     *                                   and empty strings are removed.
     */
    public function setGlobalExcludedUserAgents($excludedUserAgents)
    {
        Piwik::checkUserIsSuperUser();

        // update option
        $excludedUserAgents = $this->checkAndReturnCommaSeparatedStringList($excludedUserAgents);
        Piwik_SetOption(self::OPTION_EXCLUDED_USER_AGENTS_GLOBAL, $excludedUserAgents);

        // make sure tracker cache will reflect change
        Piwik_Tracker_Cache::deleteTrackerCache();
    }

    /**
     * Returns true if site-specific user agent exclusion has been enabled. If it hasn't,
     * only the global user agent substrings (see @setGlobalExcludedUserAgents) will be used.
     *
     * @return bool
     */
    public function isSiteSpecificUserAgentExcludeEnabled()
    {
        Piwik::checkUserHasSomeAdminAccess();
        return (bool)Piwik_GetOption(self::OPTION_SITE_SPECIFIC_USER_AGENT_EXCLUDE_ENABLE);
    }

    /**
     * Sets whether it should be allowed to exclude different user agents for different
     * websites.
     *
     * @param bool $enabled
     */
    public function setSiteSpecificUserAgentExcludeEnabled($enabled)
    {
        Piwik::checkUserIsSuperUser();

        // update option
        Piwik_SetOption(self::OPTION_SITE_SPECIFIC_USER_AGENT_EXCLUDE_ENABLE, $enabled);

        // make sure tracker cache will reflect change
        Piwik_Tracker_Cache::deleteTrackerCache();
    }

    /**
     * Returns true if the default behavior is to keep URL fragments when tracking,
     * false if otherwise.
     *
     * @return bool
     */
    public function getKeepURLFragmentsGlobal()
    {
        Piwik::checkUserHasSomeViewAccess();
        return (bool)Piwik_GetOption(self::OPTION_KEEP_URL_FRAGMENTS_GLOBAL);
    }

    /**
     * Sets whether the default behavior should be to keep URL fragments when
     * tracking or not.
     *
     * @param $enabled bool If true, the default behavior will be to keep URL
     *                      fragments when tracking. If false, the default
     *                      behavior will be to remove them.
     */
    public function setKeepURLFragmentsGlobal($enabled)
    {
        Piwik::checkUserIsSuperUser();

        // update option
        Piwik_SetOption(self::OPTION_KEEP_URL_FRAGMENTS_GLOBAL, $enabled);

        // make sure tracker cache will reflect change
        Piwik_Tracker_Cache::deleteTrackerCache();
    }

    /**
     * Sets list of URL query parameters to be excluded on all websites.
     * Will also apply to websites created in the future.
     *
     * @param string Comma separated list of URL query parameters to exclude from URLs
     * @return bool
     */
    public function setGlobalExcludedQueryParameters($excludedQueryParameters)
    {
        Piwik::checkUserIsSuperUser();
        $excludedQueryParameters = $this->checkAndReturnCommaSeparatedStringList($excludedQueryParameters);
        Piwik_SetOption(self::OPTION_EXCLUDED_QUERY_PARAMETERS_GLOBAL, $excludedQueryParameters);
        Piwik_Tracker_Cache::deleteTrackerCache();
        return true;
    }

    /**
     * Returns the list of IPs that are excluded from all websites
     *
     * @return string Comma separated list of IPs
     */
    public function getExcludedIpsGlobal()
    {
        Piwik::checkUserHasSomeAdminAccess();
        return Piwik_GetOption(self::OPTION_EXCLUDED_IPS_GLOBAL);
    }

    /**
     * Returns the default currency that will be set when creating a website through the API.
     *
     * @return string Currency ID eg. 'USD'
     */
    public function getDefaultCurrency()
    {
        Piwik::checkUserHasSomeAdminAccess();
        $defaultCurrency = Piwik_GetOption(self::OPTION_DEFAULT_CURRENCY);
        if ($defaultCurrency) {
            return $defaultCurrency;
        }
        return 'USD';
    }

    /**
     * Sets the default currency that will be used when creating websites
     *
     * @param string $defaultCurrency Currency code, eg. 'USD'
     * @return bool
     */
    public function setDefaultCurrency($defaultCurrency)
    {
        Piwik::checkUserIsSuperUser();
        $this->checkValidCurrency($defaultCurrency);
        Piwik_SetOption(self::OPTION_DEFAULT_CURRENCY, $defaultCurrency);
        return true;
    }

    /**
     * Returns the default timezone that will be set when creating a website through the API.
     * Via the UI, if the default timezone is not UTC, it will be pre-selected in the drop down
     *
     * @return string Timezone eg. UTC+7 or Europe/Paris
     */
    public function getDefaultTimezone()
    {
        $defaultTimezone = Piwik_GetOption(self::OPTION_DEFAULT_TIMEZONE);
        if ($defaultTimezone) {
            return $defaultTimezone;
        }
        return 'UTC';
    }

    /**
     * Sets the default timezone that will be used when creating websites
     *
     * @param string $defaultTimezone Timezone string eg. Europe/Paris or UTC+8
     * @return bool
     */
    public function setDefaultTimezone($defaultTimezone)
    {
        Piwik::checkUserIsSuperUser();
        $this->checkValidTimezone($defaultTimezone);
        Piwik_SetOption(self::OPTION_DEFAULT_TIMEZONE, $defaultTimezone);
        return true;
    }

    /**
     * Update an existing website.
     * If only one URL is specified then only the main url will be updated.
     * If several URLs are specified, both the main URL and the alias URLs will be updated.
     *
     * @param int $idSite website ID defining the website to edit
     * @param string $siteName website name
     * @param string|array $urls the website URLs
     * @param int $ecommerce Whether Ecommerce is enabled, 0 or 1
     * @param int $sitesearch Whether site search is enabled, 0 or 1
     * @param string $searchKeywordParameters Comma separated list of search keyword parameter names
     * @param string $searchCategoryParameters Comma separated list of search category parameter names
     * @param string $excludedIps Comma separated list of IPs to exclude from being tracked (allows wildcards)
     * @param null $excludedQueryParameters
     * @param string $timezone Timezone
     * @param string $currency Currency code
     * @param string $group Group name where this website belongs
     * @param string $startDate Date at which the statistics for this website will start. Defaults to today's date in YYYY-MM-DD format
     * @param int|null $keepURLFragments If 1, URL fragments will be kept when tracking. If 2, they
     *                                   will be removed. If 0, the default global behavior will be used.
     * @see getKeepURLFragmentsGlobal. If null, the existing value will
     *                                   not be modified.
     *
     * @throws Exception
     * @return bool true on success
     */
    public function updateSite($idSite,
                               $siteName,
                               $urls = null,
                               $ecommerce = null,
                               $siteSearch = null,
                               $searchKeywordParameters = null,
                               $searchCategoryParameters = null,
                               $excludedIps = null,
                               $excludedQueryParameters = null,
                               $timezone = null,
                               $currency = null,
                               $group = null,
                               $startDate = null,
                               $excludedUserAgents = null,
                               $keepURLFragments = null)
    {
        Piwik::checkUserHasAdminAccess($idSite);

        $idSites = Piwik_SitesManager_API::getInstance()->getSitesId();
        if (!in_array($idSite, $idSites)) {
            throw new Exception("website id = $idSite not found");
        }

        $this->checkName($siteName);

        // Build the SQL UPDATE based on specified updates to perform
        $bind = array();
        if (!is_null($urls)) {
            $urls = $this->cleanParameterUrls($urls);
            $this->checkUrls($urls);
            $this->checkAtLeastOneUrl($urls);
            $url = $urls[0];
            $bind['main_url'] = $url;
        }

        if (!is_null($currency)) {
            $currency = trim($currency);
            $this->checkValidCurrency($currency);
            $bind['currency'] = $currency;
        }
        if (!is_null($timezone)) {
            $timezone = trim($timezone);
            $this->checkValidTimezone($timezone);
            $bind['timezone'] = $timezone;
        }
        if (!is_null($group)
            && Piwik::isUserIsSuperUser()
        ) {
            $bind['group'] = trim($group);
        }
        if (!is_null($ecommerce)) {
            $bind['ecommerce'] = (int)(bool)$ecommerce;
        }
        if (!is_null($startDate)) {
            $bind['ts_created'] = Piwik_Date::factory($startDate)->getDatetime();
        }
        $bind['excluded_ips'] = $this->checkAndReturnExcludedIps($excludedIps);
        $bind['excluded_parameters'] = $this->checkAndReturnCommaSeparatedStringList($excludedQueryParameters);
        $bind['excluded_user_agents'] = $this->checkAndReturnCommaSeparatedStringList($excludedUserAgents);

        if (!is_null($keepURLFragments)) {
            $keepURLFragments = (int)$keepURLFragments;
            self::checkKeepURLFragmentsValue($keepURLFragments);

            $bind['keep_url_fragment'] = $keepURLFragments;
        }

        $bind['sitesearch'] = $this->checkSiteSearch($siteSearch);
        list($searchKeywordParameters, $searchCategoryParameters) = $this->checkSiteSearchParameters($searchKeywordParameters, $searchCategoryParameters);
        $bind['sitesearch_keyword_parameters'] = $searchKeywordParameters;
        $bind['sitesearch_category_parameters'] = $searchCategoryParameters;

        $bind['name'] = $siteName;
        $db = Zend_Registry::get('db');
        $db->update(Piwik_Common::prefixTable("site"),
            $bind,
            "idsite = $idSite"
        );

        // we now update the main + alias URLs
        $this->deleteSiteAliasUrls($idSite);
        if (count($urls) > 1) {
            $this->addSiteAliasUrls($idSite, array_slice($urls, 1));
        }
        $this->postUpdateWebsite($idSite);

        Piwik_PostEvent('SitesManager.updateSite', $idSite);
    }

    private function checkAndReturnCommaSeparatedStringList($parameters)
    {
        $parameters = trim($parameters);
        if (empty($parameters)) {
            return '';
        }

        $parameters = explode(',', $parameters);
        $parameters = array_map('trim', $parameters);
        $parameters = array_filter($parameters, 'strlen');
        $parameters = array_unique($parameters);
        return implode(',', $parameters);
    }

    /**
     * Returns the list of supported currencies
     * @see getCurrencySymbols()
     * @return array ( currencyId => currencyName)
     */
    public function getCurrencyList()
    {
        $currencies = Piwik::getCurrencyList();
        return array_map(create_function('$a', 'return $a[1]." (".$a[0].")";'), $currencies);
    }

    /**
     * Returns the list of currency symbols
     * @see getCurrencyList()
     * @return array( currencyId => currencySymbol )
     */
    public function getCurrencySymbols()
    {
        $currencies = Piwik::getCurrencyList();
        return array_map(create_function('$a', 'return $a[0];'), $currencies);
    }

    /**
     * Returns the list of timezones supported.
     * Used for addSite and updateSite
     *
     * @TODO NOT COMPATIBLE WITH API RESPONSE AUTO BUILDER
     *
     * @return array of timezone strings
     */
    public function getTimezonesList()
    {
        if (!Piwik::isTimezoneSupportEnabled()) {
            return array('UTC' => $this->getTimezonesListUTCOffsets());
        }

        $continents = array('Africa', 'America', 'Antarctica', 'Arctic', 'Asia', 'Atlantic', 'Australia', 'Europe', 'Indian', 'Pacific');
        $timezones = timezone_identifiers_list();

        $return = array();
        foreach ($timezones as $timezone) {
            // filter out timezones not recognized by strtotime()
            // @see http://bugs.php.net/46111
            $testDate = '2008-09-18 13:00:00 ' . $timezone;
            if (!strtotime($testDate)) {
                continue;
            }

            $timezoneExploded = explode('/', $timezone);
            $continent = $timezoneExploded[0];

            // only display timezones that are grouped by continent
            if (!in_array($continent, $continents)) {
                continue;
            }
            $city = $timezoneExploded[1];
            if (!empty($timezoneExploded[2])) {
                $city .= ' - ' . $timezoneExploded[2];
            }
            $city = str_replace('_', ' ', $city);
            $return[$continent][$timezone] = $city;
        }

        foreach ($continents as $continent) {
            if (!empty($return[$continent])) {
                ksort($return[$continent]);
            }
        }

        $return['UTC'] = $this->getTimezonesListUTCOffsets();
        return $return;
    }

    private function getTimezonesListUTCOffsets()
    {
        // manually add the UTC offsets
        $GmtOffsets = array(-12, -11.5, -11, -10.5, -10, -9.5, -9, -8.5, -8, -7.5, -7, -6.5, -6, -5.5, -5, -4.5, -4, -3.5, -3, -2.5, -2, -1.5, -1, -0.5,
                            0, 0.5, 1, 1.5, 2, 2.5, 3, 3.5, 4, 4.5, 5, 5.5, 5.75, 6, 6.5, 7, 7.5, 8, 8.5, 8.75, 9, 9.5, 10, 10.5, 11, 11.5, 12, 12.75, 13, 13.75, 14);

        $return = array();
        foreach ($GmtOffsets as $offset) {
            if ($offset > 0) {
                $offset = '+' . $offset;
            } elseif ($offset == 0) {
                $offset = '';
            }
            $offset = 'UTC' . $offset;
            $offsetName = str_replace(array('.25', '.5', '.75'), array(':15', ':30', ':45'), $offset);
            $return[$offset] = $offsetName;
        }
        return $return;
    }

    /**
     * Returns the list of unique timezones from all configured sites.
     *
     * @return array ( string )
     */
    public function getUniqueSiteTimezones()
    {
        Piwik::checkUserIsSuperUser();
        $results = Piwik_FetchAll("SELECT distinct timezone FROM " . Piwik_Common::prefixTable('site'));
        $timezones = array();
        foreach ($results as $result) {
            $timezones[] = $result['timezone'];
        }
        return $timezones;
    }

    /**
     * Insert the list of alias URLs for the website.
     * The URLs must not exist already for this website!
     */
    private function insertSiteUrls($idSite, $urls)
    {
        if (count($urls) != 0) {
            $db = Zend_Registry::get('db');
            foreach ($urls as $url) {
                $db->insert(Piwik_Common::prefixTable("site_url"), array(
                                                                        'idsite' => $idSite,
                                                                        'url'    => $url
                                                                   )
                );
            }
        }
    }

    /**
     * Delete all the alias URLs for the given idSite.
     */
    private function deleteSiteAliasUrls($idsite)
    {
        $db = Zend_Registry::get('db');
        $db->query("DELETE FROM " . Piwik_Common::prefixTable("site_url") . "
					WHERE idsite = ?", $idsite);
    }

    /**
     * Remove the final slash in the URLs if found
     *
     * @return string the URL without the trailing slash
     */
    private function removeTrailingSlash($url)
    {
        // if there is a final slash, we take the URL without this slash (expected URL format)
        if (strlen($url) > 5
            && $url[strlen($url) - 1] == '/'
        ) {
            $url = substr($url, 0, strlen($url) - 1);
        }
        return $url;
    }

    /**
     * Tests if the URL is a valid URL
     *
     * @return bool
     */
    private function isValidUrl($url)
    {
        return Piwik_Common::isLookLikeUrl($url);
    }

    /**
     * Tests if the IP is a valid IP, allowing wildcards, except in the first octet.
     * Wildcards can only be used from right to left, ie. 1.1.*.* is allowed, but 1.1.*.1 is not.
     *
     * @param string $ip IP address
     * @return bool
     */
    private function isValidIp($ip)
    {
        return Piwik_IP::getIpsForRange($ip) !== false;
    }

    /**
     * Check that the website name has a correct format.
     *
     * @param $siteName
     * @throws Exception
     */
    private function checkName($siteName)
    {
        if (empty($siteName)) {
            throw new Exception(Piwik_TranslateException("SitesManager_ExceptionEmptyName"));
        }
    }


    private function checkSiteSearch($siteSearch)
    {
        if ($siteSearch === null) {
            return "1";
        }
        return $siteSearch == 1 ? "1" : "0";
    }

    private function checkSiteSearchParameters($searchKeywordParameters, $searchCategoryParameters)
    {
        $searchKeywordParameters = trim($searchKeywordParameters);
        $searchCategoryParameters = trim($searchCategoryParameters);
        if (empty($searchKeywordParameters)) {
            $searchKeywordParameters = '';
        }
        if (empty($searchCategoryParameters)) {
            $searchCategoryParameters = '';
        }

        return array($searchKeywordParameters, $searchCategoryParameters);
    }

    /**
     * Check that the array of URLs are valid URLs
     *
     * @param array $urls
     * @throws Exception if any of the urls is not valid
     */
    private function checkUrls($urls)
    {
        foreach ($urls as $url) {
            if (!$this->isValidUrl($url)) {
                throw new Exception(sprintf(Piwik_TranslateException("SitesManager_ExceptionInvalidUrl"), $url));
            }
        }
    }

    /**
     * Clean the parameter URLs:
     * - if the parameter is a string make it an array
     * - remove the trailing slashes if found
     *
     * @param string|array urls
     * @return array the array of cleaned URLs
     */
    private function cleanParameterUrls($urls)
    {
        if (!is_array($urls)) {
            $urls = array($urls);
        }
        $urls = array_filter($urls);

        $urls = array_map('urldecode', $urls);
        foreach ($urls as &$url) {
            $url = $this->removeTrailingSlash($url);
            if (strpos($url, 'http') !== 0) {
                $url = 'http://' . $url;
            }
            $url = Piwik_Common::sanitizeInputValue($url);
        }
        $urls = array_unique($urls);
        return $urls;
    }

    public function getPatternMatchSites($pattern)
    {
        $ids = $this->getSitesIdWithAtLeastViewAccess();
        if (empty($ids)) {
            return array();
        }

        $ids_str = '';
        foreach ($ids as $id_num => $id_val) {
            $ids_str .= $id_val . ' , ';
        }
        $ids_str .= $id_val;

        $db = Zend_Registry::get('db');
        $bind = array('%' . $pattern . '%', 'http%' . $pattern . '%');

        // Also match the idsite
        $where = '';
        if (is_numeric($pattern)) {
            $bind[] = $pattern;
            $where = 'OR  s.idsite = ?';
        }
        $sites = $db->fetchAll("SELECT idsite, name, main_url
								FROM " . Piwik_Common::prefixTable('site') . " s
								WHERE (		s.name like ? 
										OR 	s.main_url like ?
										 $where ) 
									AND idsite in ($ids_str) 
								LIMIT " . Piwik::getWebsitesCountToDisplay(),
            $bind);
        return $sites;
    }

    /**
     * Utility function that throws if a value is not valid for the 'keep_url_fragment'
     * column of the piwik_site table.
     *
     * @param int $keepURLFragments
     * @throws Exception
     */
    private static function checkKeepURLFragmentsValue($keepURLFragments)
    {
        // make sure value is between 0 & 2
        if (!in_array($keepURLFragments, array(0, 1, 2))) {
            throw new Exception("Error in SitesManager.updateSite: keepURLFragments must be between 0 & 2" .
                " (actual value: $keepURLFragments).");
        }
    }
}
