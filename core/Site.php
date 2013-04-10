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

/**
 *
 * @package Piwik
 */
class Piwik_Site
{
    /**
     * @var int|null
     */
    protected $id = null;

    /**
     * @var array
     */
    public static $infoSites = array();

    /**
     * @param int $idsite
     */
    function __construct($idsite)
    {
        $this->id = (int)$idsite;
        if (!isset(self::$infoSites[$this->id])) {
            self::$infoSites[$this->id] = Piwik_SitesManager_API::getInstance()->getSiteFromId($this->id);
        }
    }

    /**
     * Sets the cached Site data with an array that associates site IDs with
     * individual site data.
     *
     * @param array $sites  The array of sites data. Indexed by site ID.
     */
    public static function setSites($sites)
    {
        self::$infoSites = $sites;
    }

    /**
     * Sets the cached Site data with a non-associated array of site data.
     *
     * @param array $sites  The array of sites data.
     */
    public static function setSitesFromArray($sites)
    {
        $sitesById = array();
        foreach ($sites as $site) {
            $sitesById[$site['idsite']] = $site;
        }
        self::setSites($sitesById);
    }

    /**
     * @return string
     */
    function __toString()
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
     * Returns the name of the site
     *
     * @return string
     */
    function getName()
    {
        return $this->get('name');
    }

    /**
     * Returns the main url of the site
     *
     * @return string
     */
    function getMainUrl()
    {
        return $this->get('main_url');
    }

    /**
     * Returns the id of the site
     *
     * @return int
     */
    function getId()
    {
        return $this->id;
    }

    /**
     * Returns a site property
     * @param string $name  property to return
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
     * Returns the creation date of the site
     *
     * @return Piwik_Date
     */
    function getCreationDate()
    {
        $date = $this->get('ts_created');
        return Piwik_Date::factory($date);
    }

    /**
     * Returns the timezone of the size
     *
     * @return string
     */
    function getTimezone()
    {
        return $this->get('timezone');
    }

    /**
     * Returns the currency of the site
     *
     * @return string
     */
    function getCurrency()
    {
        return $this->get('currency');
    }

    /**
     * Returns the excluded ips of the site
     *
     * @return string
     */
    function getExcludedIps()
    {
        return $this->get('excluded_ips');
    }

    /**
     * Returns the excluded query parameters of the site
     *
     * @return string
     */
    function getExcludedQueryParameters()
    {
        return $this->get('excluded_parameters');
    }

    /**
     * Returns whether ecommerce id enabled for the site
     *
     * @return bool
     */
    function isEcommerceEnabled()
    {
        return $this->get('ecommerce') == 1;
    }

    function getSearchKeywordParameters()
    {
        return $this->get('sitesearch_keyword_parameters');
    }

    function getSearchCategoryParameters()
    {
        return $this->get('sitesearch_category_parameters');
    }

    /**
     * Returns whether Site Search Tracking is enabled for the site
     *
     * @return bool
     */
    function isSiteSearchEnabled()
    {
        return $this->get('sitesearch') == 1;
    }

    /**
     * Checks the given string for valid site ids and returns them as an array
     *
     * @param string $ids comma separated idSite list
     * @return array of valid integer
     */
    static public function getIdSitesFromIdSitesString($ids)
    {
        if ($ids === 'all') {
            return Piwik_SitesManager_API::getInstance()->getSitesIdWithAtLeastViewAccess();
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
     * Clears the site cache
     */
    static public function clearCache()
    {
        self::$infoSites = array();
    }

    /**
     * Utility function. Returns the value of the specified field for the
     * site with the specified ID.
     *
     * @param int|string $idsite  The ID of the site whose data is being
     *                             accessed.
     * @param string $field   The name of the field to get.
     * @return mixed
     */
    static protected function getFor($idsite, $field)
    {
        $idsite = (int)$idsite;

        if (!isset(self::$infoSites[$idsite])) {
            self::$infoSites[$idsite] = Piwik_SitesManager_API::getInstance()->getSiteFromId($idsite);
        }

        return self::$infoSites[$idsite][$field];
    }

    /**
     * Returns the name of the site with the specified ID.
     *
     * @param int $idsite  The site ID.
     * @return string
     */
    static public function getNameFor($idsite)
    {
        return self::getFor($idsite, 'name');
    }

    /**
     * Returns the timezone of the site with the specified ID.
     *
     * @param int $idsite  The site ID.
     * @return string
     */
    static public function getTimezoneFor($idsite)
    {
        return self::getFor($idsite, 'timezone');
    }

    /**
     * Returns the creation date of the site with the specified ID.
     *
     * @param int $idsite  The site ID.
     * @return string
     */
    static public function getCreationDateFor($idsite)
    {
        return self::getFor($idsite, 'ts_created');
    }

    /**
     * Returns the url for the site with the specified ID.
     *
     * @param int $idsite  The site ID.
     * @return string
     */
    static public function getMainUrlFor($idsite)
    {
        return self::getFor($idsite, 'main_url');
    }

    /**
     * Returns whether the site with the specified ID is ecommerce enabled
     *
     * @param int $idsite  The site ID.
     * @return string
     */
    static public function isEcommerceEnabledFor($idsite)
    {
        return self::getFor($idsite, 'ecommerce') == 1;
    }

    /**
     * Returns whether the site with the specified ID is Site Search enabled
     *
     * @param int $idsite  The site ID.
     * @return string
     */
    static public function isSiteSearchEnabledFor($idsite)
    {
        return self::getFor($idsite, 'sitesearch') == 1;
    }

    /**
     * Returns the currency of the site with the specified ID.
     *
     * @param int $idsite  The site ID.
     * @return string
     */
    static public function getCurrencyFor($idsite)
    {
        return self::getFor($idsite, 'currency');
    }

    /**
     * Returns the excluded IP addresses of the site with the specified ID.
     *
     * @param int $idsite  The site ID.
     * @return string
     */
    static public function getExcludedIpsFor($idsite)
    {
        return self::getFor($idsite, 'excluded_ips');
    }

    /**
     * Returns the excluded query parameters for the site with the specified ID.
     *
     * @param int $idsite  The site ID.
     * @return string
     */
    static public function getExcludedQueryParametersFor($idsite)
    {
        return self::getFor($idsite, 'excluded_parameters');
    }
}
