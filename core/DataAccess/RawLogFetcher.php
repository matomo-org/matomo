<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\DataAccess;

use Piwik\Common;
use Piwik\Db;

/**
 * DAO that queries log tables.
 */
class RawLogFetcher
{
    /**
     * @param string $from
     * @param string $to
     * @param array $fields
     * @param int $fromId
     * @param int $limit
     * @return array[]
     */
    public function getVisitsWithDatesLimit($from, $to, $fields = array(), $fromId = 0, $limit = 1000)
    {
        $sql = "SELECT " . implode(', ', $fields)
             . " FROM " . Common::prefixTable('log_visit')
             . " WHERE visit_first_action_time >= ? AND visit_last_action_time < ?"
             . " AND idvisit > ?"
             . sprintf(" LIMIT %d", $limit);

        $bind = array($from, $to, $fromId);

        return Db::fetchAll($sql, $bind);
    }

    /**
     * @param string $from
     * @param string $to
     * @return int
     */
    public function countVisitsWithDatesLimit($from, $to)
    {
        $sql = "SELECT COUNT(*) AS num_rows"
             . " FROM " . Common::prefixTable('log_visit')
             . " WHERE visit_first_action_time >= ? AND visit_last_action_time < ?";

        $bind = array($from, $to);

        return (int) Db::fetchOne($sql, $bind);
    }
}