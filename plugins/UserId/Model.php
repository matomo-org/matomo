<?php
namespace Piwik\Plugins\UserId;

use Piwik\Common;
use Piwik\Db;

/**
 * Provides DB data access for the UserId module
 */
class Model
{
    /** @var string */
    private $logVisitTable;

    /** @var string */
    private $userIdsTable;

    /** @var Piwik\Db\Adapter\Pdo\Mysql */
    private $db;

    /**
     * @param null|Piwik\Db\Adapter\Pdo\Mysql $db Optional DB adapter, injected to simplify unit tests
     */
    public function __construct($db = null)
    {
        $this->logVisitTable = Common::prefixTable('log_visit');
        $this->userIdsTable = Common::prefixTable('user_ids');
        $this->db = $db ?: DB::get();
    }

    /**
     * @return string Main visits log DB table
     */
    public function getLogVisitTable()
    {
        return $this->logVisitTable;
    }

    /**
     * @return string Aggregated user IDs index table
     */
    public function getUserIdsTable()
    {
        return $this->userIdsTable;
    }

    /**
     * Get the total number of users for the given site ID and optionally for user ID search query.
     * Used to initialize DataTable with correct data for pagination
     *
     * @param        $idSite
     * @param string $filterPattern User ID search query
     *
     * @return int
     */
    public function getTotalUsersNumber($idSite, $filterPattern = '')
    {
        $filterPattern = $this->prepareFilterPatternForQuery($filterPattern);
        if (strlen($filterPattern) > 0) {
            $filterPatternExpression = ' AND user_id LIKE ?';
            $bindParams = array($idSite, $filterPattern);
        } else {
            $filterPatternExpression = '';
            $bindParams = array($idSite);
        }

        return $this->db->fetchOne(
            "SELECT COUNT(*)
            FROM {$this->userIdsTable}
            WHERE idsite = ? AND user_id != ''$filterPatternExpression",
            $bindParams
        );
    }

    /**
     * Prepare user ID query string to be used in MySQL LIKE query
     *
     * @param $filterPattern
     *
     * @return string
     */
    private function prepareFilterPatternForQuery($filterPattern)
    {
        $filterPattern = trim($filterPattern);
        if (strlen($filterPattern) == 0) {
            return '';
        }

        $filterPattern = html_entity_decode($filterPattern, ENT_QUOTES, 'UTF-8');
        return '%' . str_replace(array('_', '%'), array('\_', '\%'), $filterPattern) . '%';
    }

    /**
     * Get user IDs and related data from user_ids table, with optional limiting, sorting
     * and filtering by user ID
     *
     * @param int    $idSite
     * @param int    $filterOffset
     * @param int    $filterLimit
     * @param string $filterSortOrder
     * @param string $filterSortColumn
     * @param string $filterPattern
     *
     * @return array
     */
    public function getSiteUserIds($idSite, $filterOffset, $filterLimit, $filterSortOrder = 'asc', $filterSortColumn = 'user_id', $filterPattern = '')
    {
        $filterOffset = intval($filterOffset);
        $filterLimit = intval($filterLimit);
        $filterSortOrder = $filterSortOrder == 'desc' ? 'desc' : 'asc';

        if ($filterSortColumn == 'user_id') {
            // user_id is a varchar field, so we need this to correctly sort numeric values
            $sortExpression = "user_id * 1 $filterSortOrder, user_id $filterSortOrder";
        } else {
            $sortExpression = "$filterSortColumn $filterSortOrder";
        }

        $filterPattern = $this->prepareFilterPatternForQuery($filterPattern);
        if (strlen($filterPattern) > 0) {
            $filterPatternExpression = ' AND user_id LIKE ?';
            $bindParams = array($idSite, $filterPattern);
        } else {
            $filterPatternExpression = '';
            $bindParams = array($idSite);
        }

        return $this->db->fetchAll(
            "SELECT user_id, first_visit_time, last_visit_time, total_visits, idvisitor
            FROM {$this->userIdsTable}
            WHERE idsite = ? AND user_id != ''$filterPatternExpression
            ORDER BY $sortExpression
            LIMIT $filterOffset, $filterLimit",
            $bindParams
        );
    }

    /**
     * Get last tracked visit ID from the main log_visit table
     *
     * @return int
     */
    public function getLastVisitId()
    {
        $lastVisitId = $this->db->fetchOne("SELECT MAX(idvisit) FROM {$this->getLogVisitTable()}");
        return $lastVisitId ?: 0;
    }

    /**
     * Get last indexed visit ID from the indexed user_ids table
     *
     * @return int
     */
    public function getLastIndexedVisitId()
    {
        $lastIndexedVisitId = $this->db->fetchOne("SELECT MAX(last_visit_id) FROM {$this->getUserIdsTable()}");
        return $lastIndexedVisitId ?: 0;
    }

    /**
     * Get visits grouped by user ID
     *
     * @param $lastIndexedVisitId
     * @param $lastVisitId
     * @param $limit
     *
     * @return Zend_Db_Statement
     */
    public function getVisitsAggregatedByUser($lastIndexedVisitId, $lastVisitId, $limit)
    {
        /*
         * A subquery used to speed up the whole query by using an index on the idvisit column
         */
        return $this->db->fetchAll(
            "SELECT sub.user_id, sub.idsite, MAX(sub.idvisit) as last_visit_id, sub.idvisitor,
              MIN(sub.visit_first_action_time) as first_visit_time,
              MAX(sub.visit_last_action_time) as last_visit_time,
              SUM(sub.visitor_count_visits) as total_visits
            FROM (
                SELECT user_id, idsite, idvisit, idvisitor, visit_first_action_time, visit_last_action_time, visitor_count_visits FROM log_visit
                WHERE idvisit > ? AND idvisit <= ?
                ORDER BY idvisit ASC
                LIMIT $limit
            ) as sub
            GROUP BY sub.idsite, sub.user_id
            ORDER BY last_visit_id",
            array($lastIndexedVisitId, $lastVisitId)
        );
    }

    /**
     * Get an array of visits data grouped by user ID provided by the getVisitsAggregatedByUser method.
     * Insert this data into user_ids index DB table. If a user already exists, it's being updated.
     *
     * @param array $visitsAggregatedByUser
     */
    public function indexNewVisitsToUserIdsTable(array $visitsAggregatedByUser)
    {
        $inserts = [];
        foreach ($visitsAggregatedByUser as $visitRow) {
            $inserts[] = "('" . addslashes($visitRow['user_id']) . "', {$visitRow['idsite']}, "
                . "{$visitRow['last_visit_id']}, '{$visitRow['first_visit_time']}', "
                . "'{$visitRow['last_visit_time']}', {$visitRow['total_visits']}, X'" . bin2hex($visitRow['idvisitor']) . "')";
        }
        if (empty($inserts)) {
            return;
        }

        $this->db->query(
            "INSERT INTO {$this->getUserIdsTable()}
              (user_id, idsite, last_visit_id, first_visit_time, last_visit_time, total_visits, idvisitor)
            VALUES " . implode(',', $inserts) . "
            ON DUPLICATE KEY UPDATE last_visit_id = values(last_visit_id),
              last_visit_time = values(last_visit_time),
              total_visits = total_visits + values(total_visits)",
            array(),
            false
        );
    }

    /**
     * Truncate the user_ids index table
     */
    public function cleanUserIdsTable()
    {
        Db::get()->query("TRUNCATE TABLE {$this->getUserIdsTable()}");
    }
}
