<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\SitesManager;

use Piwik\Db;
use Piwik\Common;
use Exception;
use Piwik\Site;

class Model
{
    /**
     * Returns the list of websites from the ID array in parameters.
     *
     * @param array $idSites list of website ID
     * @param bool $limit
     * @return array
     */
    public function getSitesFromIds($idSites, $limit = false)
    {
        if (count($idSites) === 0) {
            return array();
        }

        if ($limit) {
            $limit = "LIMIT " . (int)$limit;
        } else {
            $limit = '';
        }

        $idSites = array_map('intval', $idSites);

        $db    = Db::get();
        $sites = $db->fetchAll("SELECT *
								FROM " . Common::prefixTable("site") . "
								WHERE idsite IN (" . implode(", ", $idSites) . ")
								ORDER BY idsite ASC $limit");

        return $sites;
    }

    /**
     * Returns the website information : name, main_url
     *
     * @throws Exception if the site ID doesn't exist or the user doesn't have access to it
     * @param int $idSite
     * @return array
     */
    public function getSiteFromId($idSite)
    {
        $site = Db::get()->fetchRow("SELECT *
    								FROM " . Common::prefixTable("site") . "
    								WHERE idsite = ?", $idSite);

        return $site;
    }

    /**
     * Returns the list of all the website IDs registered.
     * Caller must check access.
     *
     * @return array The list of website IDs
     */
    public function getSitesId()
    {
        $result = Db::fetchAll("SELECT idsite FROM " . Common::prefixTable('site'));
        $idSites = array();
        foreach ($result as $idSite) {
            $idSites[] = $idSite['idsite'];
        }
        return $idSites;
    }

    /**
     * Returns the list of all URLs registered for the given idSite (main_url + alias URLs).
     *
     * @throws Exception if the website ID doesn't exist or the user doesn't have access to it
     * @param int $idSite
     * @return array list of URLs
     */
    public function getSiteUrlsFromId($idSite)
    {
        $urls = $this->getAliasSiteUrlsFromId($idSite);

        $site = $this->getSiteFromId($idSite);

        if (empty($site)) {
            return $urls;
        }

        return array_merge(array($site['main_url']), $urls);
    }

    /**
     * Returns the list of alias URLs registered for the given idSite.
     * The website ID must be valid when calling this method!
     *
     * @param int $idSite
     * @return array list of alias URLs
     */
    public function getAliasSiteUrlsFromId($idSite)
    {
        $db = Db::get();
        $result = $db->fetchAll("SELECT url
								FROM " . Common::prefixTable("site_url") . "
								WHERE idsite = ?", $idSite);
        $urls = array();
        foreach ($result as $url) {
            $urls[] = $url['url'];
        }
        return $urls;
    }
}
