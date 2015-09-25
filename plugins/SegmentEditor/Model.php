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
use Piwik\DbHelper;

/**
 * The SegmentEditor Model lets you persist and read custom Segments from the backend without handling any logic.
 */
class Model
{
    private static $rawPrefix = 'segment';

    protected function getTable()
    {
        return Common::prefixTable(self::$rawPrefix);
    }

    /**
     * Returns all stored segments that haven't been deleted. Ignores the site the segments are enabled
     * for and whether to auto archive or not.
     *
     * @return array
     */
    public function getAllSegmentsAndIgnoreVisibility()
    {
        $sql = "SELECT * FROM " . $this->getTable() . " WHERE deleted = 0";

        $segments = $this->getDb()->fetchAll($sql);

        return $segments;
    }

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

        $segments = $this->getDb()->fetchAll($sql, $bind);

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

        $segments = $this->getDb()->fetchAll($sql, $bind);

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
        $segments = $this->getDb()->fetchAll($sql, $bind);

        return $segments;
    }

    public function deleteSegment($idSegment)
    {
        $db = $this->getDb();
        $db->delete($this->getTable(), 'idsegment = ' . (int) $idSegment);
    }

    public function updateSegment($idSegment, $segment)
    {
        $idSegment = (int) $idSegment;

        $db = $this->getDb();
        $db->update($this->getTable(), $segment, "idsegment = $idSegment");

        return true;
    }

    public function createSegment($segment)
    {
        $db = $this->getDb();
        $db->insert($this->getTable(), $segment);
        $id = $db->lastInsertId();

        return $id;
    }

    public function getSegment($idSegment)
    {
        $db = $this->getDb();
        $segment = $db->fetchRow("SELECT * FROM " . $this->getTable() . " WHERE idsegment = ?", $idSegment);

        return $segment;
    }

    private function getDb()
    {
        return Db::get();
    }

    private function buildQuerySortedByName($where)
    {
        return "SELECT * FROM " . $this->getTable() . " WHERE $where ORDER BY name ASC";
    }

    public static function install()
    {
        $segmentTable = "`idsegment` INT(11) NOT NULL AUTO_INCREMENT,
					     `name` VARCHAR(255) NOT NULL,
					     `definition` TEXT NOT NULL,
					     `login` VARCHAR(100) NOT NULL,
					     `enable_all_users` tinyint(4) NOT NULL default 0,
					     `enable_only_idsite` INTEGER(11) NULL,
					     `auto_archive` tinyint(4) NOT NULL default 0,
					     `ts_created` TIMESTAMP NULL,
					     `ts_last_edit` TIMESTAMP NULL,
					     `deleted` tinyint(4) NOT NULL default 0,
					     PRIMARY KEY (`idsegment`)";

        DbHelper::createTable(self::$rawPrefix, $segmentTable);
    }

}
