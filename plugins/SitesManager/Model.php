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

class Model
{
    /**
     * Returns the list of websites from the ID array in parameters.
     *
     * @param array $idSites list of website ID
     * @param bool|int $limit
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
     * Returns the website information.
     *
     * @param int $idSite
     * @return array|false
     */
    public function getSiteFromId($idSite)
    {
        $site = Db::get()->fetchRow("SELECT *
    								FROM " . Common::prefixTable("site") . "
    								WHERE idsite = ?", $idSite);

        return $site;
    }
}
