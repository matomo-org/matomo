<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */

namespace Piwik;

use Exception;
use Piwik\Plugins\SitesManager\API;

/**
 * Provides access to individual site data (such as name, URL, etc.).
 * 
 * ### Examples
 * 
 * **Basic usage**
 * 
 *     $site = new Site($idSite);
 *     $name = $site->getName();
 * 
 * **Without allocation**
 * 
 *     $name = Site::getNameFor($idSite);
 * 
 * @package Piwik
 * @api
 */
class Site
{
    const DEFAULT_SITE_TYPE = "website";

    /**
     * @var int|null
     */
    protected $id = null;

    /**
     * @var array
     */
    protected static $infoSites = array();

    /**
     * Constructor.
     * 
     * @param int $idsite The ID of the site we want data for.
     */
    public function __construct($idsite)
    {
        $this->id = (int)$idsite;
        if (!isset(self::$infoSites[$this->id])) {
            $site = API::getInstance()->getSiteFromId($this->id);
            self::setSite($this->id, $site);
        }
    }

    /**
     * Sets the cached site data with an array that associates site IDs with
     * individual site data.
     *
     * @param array $sites The array of sites data. Indexed by site ID. eg,
     *                     ```
     *                     array('1' => array('name' => 'Site 1', ...),
     *                           '2' => array('name' => 'Site 2', ...))`
     *                     ```
     */
    public static function setSites($sites)
    {
        foreach($sites as $idsite => $site) {
            self::setSite($idsite, $site);
        }
    }

    /**
     * Sets a site information in memory (statically cached).
     *
     * Plugins can filter the website attributes before it is cached, eg. to change the website name,
     * creation date, etc.
     *
     * @param $idSite
     * @param $infoSite
     * @throws Exception if website or idsite is invalid
     */
    protected static function setSite($idSite, $infoSite)
    {
        if(empty($idSite) || empty($infoSite)) {
            throw new Exception("Un unexpected website was found.");
        }


        /**
         * Piwik core APIs and plugins use the Site object to get information about websites.
         * This event is called just before a Website information is stored in the memory cache.
         * It can be used to modify the data for a website, such as decorate its name or change its created datetime.
         *
         * @param $idSite int Website ID
         * @param $infoSite array Website information array
         */
        Piwik::postEvent('Site.setSite', array($idSite, &$infoSite));

        self::$infoSites[$idSite] = $infoSite;
    }

    /**
     * Sets the cached Site data with a non-associated array of site data.
     * 
     * @param array $sites The array of sites data. eg,
     *                     ```
     *                     array(
     *                         array('idsite' => '1', 'name' => 'Site 1', ...),
     *                         array('idsite' => '2', 'name' => 'Site 2', ...),
     *                     )
     *                     ```
     */
    public static function setSitesFromArray($sites)
    {
        foreach ($sites as $site) {
            self::setSite($site['idsite'], $site);
        }
    }

    /**
     * The Multisites reports displays the first calendar date as the earliest day available for all websites.
     * Also, today is the later "today" available across all timezones.
     * @param array $siteIds Array of IDs for each site being displayed.
     * @return Date[] of two Date instances. First is the min-date & the second
     *               is the max date.
     */
    public static function getMinMaxDateAcrossWebsites($siteIds)
    {
        $siteIds = self::getIdSitesFromIdSitesString($siteIds);
        $now = Date::now();

        $minDate = null;
        $maxDate = $now->subDay(1)->getTimestamp();
        foreach ($siteIds as $idsite) {
            // look for 'now' in the website's timezone
            $timezone = Site::getTimezoneFor($idsite);
            $date = Date::adjustForTimezone($now->getTimestamp(), $timezone);
            if ($date > $maxDate) {
                $maxDate = $date;
            }

            // look for the absolute minimum date
            $creationDate = Site::getCreationDateFor($idsite);
            $date = Date::adjustForTimezone(strtotime($creationDate), $timezone);
            if (is_null($minDate) || $date < $minDate) {
                $minDate = $date;
            }
        }

        return array(Date::factory($minDate), Date::factory($maxDate));
    }

    /**
     * Returns a string representation of the site this instance references.
     * 
     * Useful for debugging.
     * 
     * @return string
     */
    public function __toString()
    {
        return "site id=" . $this->getId() . ",
				 name=" . $this->getName() . ",
				 url = " . $this->getMainUrl() . ",
				 IPs excluded = " . $this->getExcludedIps() . ",
				 timezone = " . $this->getTimezone() . ",
				 currency = " . $this->getCurrency() . ",
				 creation date = " . $this->getCreationDate();
    }

    /**
     * Returns the name of the site.
     *
     * @return string
     * @throws Exception if data for the site cannot be found.
     */
    public function getName()
    {
        return $this->get('name');
    }

    /**
     * Returns the main url of the site.
     *
     * @return string
     * @throws Exception if data for the site cannot be found.
     */
    public function getMainUrl()
    {
        return $this->get('main_url');
    }

    /**
     * Returns the id of the site.
     *
     * @return int
     * @throws Exception if data for the site cannot be found.
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns a site property by name.
     * 
     * @param string $name Name of the property to return (eg, `'main_url'` or `'name'`).
     * @return mixed
     * @throws Exception
     */
    protected function get($name)
    {
        if (!isset(self::$infoSites[$this->id][$name])) {
            throw new Exception('The requested website id = ' . (int)$this->id . ' (or its property ' . $name . ') couldn\'t be found');
        }
        return self::$infoSites[$this->id][$name];
    }

    /**
     * Returns the type of the website (by default "website")
     * @return string
     */
    public function getType()
    {
        $type = $this->get('type');
        return $type;
    }

    /**
     * Returns the creation date of the site.
     *
     * @return Date
     * @throws Exception if data for the site cannot be found.
     */
    public function getCreationDate()
    {
        $date = $this->get('ts_created');
        return Date::factory($date);
    }

    /**
     * Returns the timezone of the size.
     *
     * @return string
     * @throws Exception if data for the site cannot be found.
     */
    public function getTimezone()
    {
        return $this->get('timezone');
    }

    /**
     * Returns the currency of the site.
     *
     * @return string
     * @throws Exception if data for the site cannot be found.
     */
    public function getCurrency()
    {
        return $this->get('currency');
    }

    /**
     * Returns the excluded ips of the site.
     *
     * @return string
     * @throws Exception if data for the site cannot be found.
     */
    public function getExcludedIps()
    {
        return $this->get('excluded_ips');
    }

    /**
     * Returns the excluded query parameters of the site.
     *
     * @return string
     * @throws Exception if data for the site cannot be found.
     */
    public function getExcludedQueryParameters()
    {
        return $this->get('excluded_parameters');
    }

    /**
     * Returns whether ecommerce is enabled for the site.
     *
     * @return bool
     * @throws Exception if data for the site cannot be found.
     */
    public function isEcommerceEnabled()
    {
        return $this->get('ecommerce') == 1;
    }

    /**
     * Returns the site search keyword query parameters for the site.
     * 
     * @return string
     * @throws Exception if data for the site cannot be found.
     */
    public function getSearchKeywordParameters()
    {
        return $this->get('sitesearch_keyword_parameters');
    }

    /**
     * Returns the site search category query parameters for the site.
     * 
     * @return string
     * @throws Exception if data for the site cannot be found.
     */
    public function getSearchCategoryParameters()
    {
        return $this->get('sitesearch_category_parameters');
    }

    /**
     * Returns whether Site Search Tracking is enabled for the site.
     *
     * @return bool
     * @throws Exception if data for the site cannot be found.
     */
    public function isSiteSearchEnabled()
    {
        return $this->get('sitesearch') == 1;
    }

    /**
     * Checks the given string for valid site ids and returns them as an array.
     *
     * @param string $ids Comma separated idSite list, eg, `'1,2,3,4'`.
     * @param bool|string $_restrictSitesToLogin Used only when running as a scheduled task.
     * @return array An array of valid, unique integers.
     */
    static public function getIdSitesFromIdSitesString($ids, $_restrictSitesToLogin = false)
    {
        if ($ids === 'all') {
            return API::getInstance()->getSitesIdWithAtLeastViewAccess($_restrictSitesToLogin);
        }

        if(is_bool($ids)) {
            return array();
        }
        if (!is_array($ids)) {
            $ids = explode(',', $ids);
        }
        $validIds = array();
        foreach ($ids as $id) {
            $id = trim($id);
            if (!empty($id) && is_numeric($id) && $id > 0) {
                $validIds[] = $id;
            }
        }
        $validIds = array_filter($validIds);
        $validIds = array_unique($validIds);

        return $validIds;
    }

    /**
     * Clears the site data cache.
     * 
     * See also {@link setSites()} and {@link setSitesFromArray()}.
     */
    static public function clearCache()
    {
        self::$infoSites = array();
    }

    /**
     * Utility function. Returns the value of the specified field for the
     * site with the specified ID.
     *
     * @param int $idsite The ID of the site whose data is being accessed.
     * @param bool|string $field The name of the field to get.
     * @return array|string
     */
    static protected function getFor($idsite, $field = false)
    {
        $idsite = (int)$idsite;

        if (!isset(self::$infoSites[$idsite])) {
            $site = API::getInstance()->getSiteFromId($idsite);
            self::setSite($idsite, $site);
        }
        if($field) {
            return self::$infoSites[$idsite][$field];
        }
        return self::$infoSites[$idsite];
    }

    /**
     * Returns all websites pre-cached
     *
     * @ignore
     */
    static public function getSites()
    {
        return self::$infoSites;
    }

    static public function getSite($id)
    {
        return self::getFor($id);
    }

    /**
     * Returns the name of the site with the specified ID.
     *
     * @param int $idsite The site ID.
     * @return string
     */
    static public function getNameFor($idsite)
    {
        return self::getFor($idsite, 'name');
    }

    /**
     * Returns the timezone of the site with the specified ID.
     *
     * @param int $idsite The site ID.
     * @return string
     */
    static public function getTimezoneFor($idsite)
    {
        return self::getFor($idsite, 'timezone');
    }

    /**
     * Returns the type of the site with the specified ID.
     *
     * @param $idsite
     * @return string
     */
    static public function getTypeFor($idsite)
    {
        return self::getFor($idsite, 'type');
    }

    /**
     * Returns the creation date of the site with the specified ID.
     *
     * @param int $idsite The site ID.
     * @return string
     */
    static public function getCreationDateFor($idsite)
    {
        return self::getFor($idsite, 'ts_created');
    }

    /**
     * Returns the url for the site with the specified ID.
     *
     * @param int $idsite The site ID.
     * @return string
     */
    static public function getMainUrlFor($idsite)
    {
        return self::getFor($idsite, 'main_url');
    }

    /**
     * Returns whether the site with the specified ID is ecommerce enabled
     *
     * @param int $idsite The site ID.
     * @return string
     */
    static public function isEcommerceEnabledFor($idsite)
    {
        return self::getFor($idsite, 'ecommerce') == 1;
    }

    /**
     * Returns whether the site with the specified ID is Site Search enabled
     *
     * @param int $idsite The site ID.
     * @return string
     */
    static public function isSiteSearchEnabledFor($idsite)
    {
        return self::getFor($idsite, 'sitesearch') == 1;
    }

    /**
     * Returns the currency of the site with the specified ID.
     *
     * @param int $idsite The site ID.
     * @return string
     */
    static public function getCurrencyFor($idsite)
    {
        return self::getFor($idsite, 'currency');
    }

    /**
     * Returns the excluded IP addresses of the site with the specified ID.
     *
     * @param int $idsite The site ID.
     * @return string
     */
    static public function getExcludedIpsFor($idsite)
    {
        return self::getFor($idsite, 'excluded_ips');
    }

    /**
     * Returns the excluded query parameters for the site with the specified ID.
     *
     * @param int $idsite The site ID.
     * @return string
     */
    static public function getExcludedQueryParametersFor($idsite)
    {
        return self::getFor($idsite, 'excluded_parameters');
    }
}