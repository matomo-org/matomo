<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\SegmentEditor;

use Piwik\Common;
use Piwik\Date;
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

    /**
     * This should be used _only_ by Super Users
     * @param $idSite
     * @return array
     */
    public function getAllSegmentsForAllUsers($idSite = false)
    {
        $bind = array();
        $sqlWhereCondition = '';

        if(!empty($idSite)) {
            $bind = array($idSite);
            $sqlWhereCondition = '(enable_only_idsite = ? OR enable_only_idsite = 0) AND';
        }

        $sqlWhereCondition  = $this->buildQuerySortedByName($sqlWhereCondition . ' deleted = 0');
        $segments = $this->getDb()->fetchAll($sqlWhereCondition, $bind);

        return $segments;
    }

    public function getSegmentByDefinition($definition)
    {
        $sql = $this->buildQuerySortedByName("definition = ? AND deleted = 0");
        $bind = [$definition];

        $segment = $this->getDb()->fetchRow($sql, $bind);
        return $segment;
    }

    /**
     * Gets a list of segments that have been deleted in the last week and therefore may have orphaned archives.
     * @param Date $date Segments deleted on or after this date will be returned.
     * @return array of segments. The segments are only populated with the fields needed for archive invalidation
     * (e.g. definition, enable_only_idsite).
     * @throws \Exception
     */
    public function getSegmentsDeletedSince(Date $date)
    {
        $dateStr = $date->getDatetime();
        $sql = "SELECT DISTINCT `definition`, `enable_only_idsite`, `hash` FROM " . Common::prefixTable('segment')
            . " WHERE deleted = 1 AND ts_last_edit >= ?";
        $deletedSegments = Db::fetchAll($sql, array($dateStr));

        if (empty($deletedSegments)) {
            return array();
        }

        $existingSegments = $this->getExistingSegmentsLike($deletedSegments);

        foreach ($deletedSegments as $i => $deleted) {
            $deletedSegments[$i]['idsites_to_preserve'] = array();
            foreach ($existingSegments as $existing) {
                if ($existing['definition'] != $deleted['definition'] &&
                    $existing['definition'] != urlencode($deleted['definition']) &&
                    $existing['definition'] != urldecode($deleted['definition'])
                ) {
                    continue;
                }

                if (
                    $existing['enable_only_idsite'] == $deleted['enable_only_idsite']
                    || $existing['enable_only_idsite'] == 0
                ) {
                    // There is an identical segment (for either the specific site or for all sites) that is active
                    // The archives for this segment will therefore still be needed
                    unset($deletedSegments[$i]);
                    break;
                } elseif ($deleted['enable_only_idsite'] == 0) {
                    // It is an all-sites segment that got deleted, but there is a single-site segment that is active
                    // Need to make sure we don't erase the segment's archives for that particular site
                    $deletedSegments[$i]['idsites_to_preserve'][] = $existing['enable_only_idsite'];
                }
            }
        }

        return $deletedSegments;
    }

    private function getExistingSegmentsLike(array $segments)
    {
        if (empty($segments)) {
            return array();
        }

        $whereClauses = array();
        $bind = array();
        $definitionWhereClauseTemplate = '(definition = ? OR definition = ? OR definition = ?)';
        foreach ($segments as $segment) {
            // Sometimes they are stored encoded and sometimes they aren't
            $bind[] = $segment['definition'];
            $bind[] = urlencode($segment['definition']);
            $bind[] = urldecode($segment['definition']);

            if ($segment['enable_only_idsite'] == 0) {
                // They deleted an all-sites segment, but there is a single-site segment with same definition?
                // Need to handle this carefully so that the archives for the single-site segment are preserved
                $whereClauses[] = "$definitionWhereClauseTemplate";
            } else {
                $whereClauses[] = "($definitionWhereClauseTemplate AND (enable_only_idsite = ? OR enable_only_idsite = 0))";
                $bind[] = $segment['enable_only_idsite'];
            }
        }
        $whereClauses = implode(' OR ', $whereClauses);

        // Check for any non-deleted segments with the same definition
        $sql = "SELECT DISTINCT definition, enable_only_idsite FROM " . Common::prefixTable('segment')
            . " WHERE deleted = 0 AND (" . $whereClauses . ")";
        return Db::fetchAll($sql, $bind);
    }

    public function deleteSegment($idSegment)
    {
        $fieldsToSet = array(
            'deleted' => 1,
            'ts_last_edit' => Date::factory('now')->toString('Y-m-d H:i:s')
        );

        $db = $this->getDb();
        $db->update($this->getTable(), $fieldsToSet, 'idsegment = ' . (int) $idSegment);
    }

    public function updateSegment($idSegment, $segment)
    {
        $idSegment = (int) $idSegment;

        if (isset($segment['definition'])) {
            $segment['hash'] = $this->createHash($segment['definition']);
        }
        $db = $this->getDb();
        $db->update($this->getTable(), $segment, "idsegment = $idSegment");

        return true;
    }

    public function createSegment($segment)
    {
        $db = $this->getDb();

        $segment['hash'] = $this->createHash($segment['definition']);
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

    private function createHash($definition)
    {
        return md5(urldecode($definition));
    }

    public static function install()
    {
        $segmentTable = "`idsegment` INT(11) NOT NULL AUTO_INCREMENT,
                         `name` VARCHAR(255) NOT NULL,
                         `definition` TEXT NOT NULL,
                         `hash` CHAR(32) NULL,
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
