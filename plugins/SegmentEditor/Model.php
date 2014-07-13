<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\SegmentEditor;

use Piwik\Common;
use Piwik\Db;

/**
 * The SegmentEditor Model lets you persist and read custom Segments from the backend without handling any logic.
 */
class Model
{
    /**
     * Returns all stored segments.
     *
     * @param bool|int $idSite Whether to return stored segments for a specific idSite, or segments that are available
     *                         for all sites. If supplied, must be a valid site ID.
     * @return array
     */
    public function getSegmentsToAutoArchive($idSite = false)
    {
        $bind = array();

        $whereIdSite = '';
        if (!empty($idSite)) {
            $whereIdSite = 'enable_only_idsite = ? OR ';
            $bind[] = $idSite;
        }

        $sql = $this->buildQuerySortedByName("($whereIdSite enable_only_idsite = 0)
                                              AND deleted = 0 AND auto_archive = 1");

        $segments = Db::get()->fetchAll($sql, $bind);

        return $segments;
    }

    /**
     * Returns all stored segments that are available to the given login.
     *
     * @param  string $userLogin
     * @return array
     */
    public function getAllSegments($userLogin)
    {
        $bind = array($userLogin);
        $sql  = $this->buildQuerySortedByName('deleted = 0 AND (enable_all_users = 1 OR login = ?)');

        $segments = Db::get()->fetchAll($sql, $bind);

        return $segments;
    }

    /**
     * Returns all stored segments that are available for the given site and login.
     *
     * @param  string $userLogin
     * @param  int    $idSite Whether to return stored segments for a specific idSite, or all of them. If supplied, must be a valid site ID.
     * @return array
     */
    public function getAllSegmentsForSite($idSite, $userLogin)
    {
        $bind = array($idSite, $userLogin);
        $sql  = $this->buildQuerySortedByName('(enable_only_idsite = ? OR enable_only_idsite = 0)
                                               AND deleted = 0
                                               AND (enable_all_users = 1 OR login = ?)');
        $segments = Db::get()->fetchAll($sql, $bind);

        return $segments;
    }

    private function buildQuerySortedByName($where)
    {
        $sql = "SELECT * FROM " . Common::prefixTable("segment") .
               " WHERE $where ORDER BY name ASC";

        return $sql;
    }
}
