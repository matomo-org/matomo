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
class RawLogDao
{
    /**
     * @param array $values
     * @param string $idVisit
     */
    public function updateVisits(array $values, $idVisit)
    {
        $sql = "UPDATE " . Common::prefixTable('log_visit')
            . " SET " . $this->getColumnSetExpressions(array_keys($values))
            . " WHERE idvisit = ?";

        $this->update($sql, $values, $idVisit);
    }

    /**
     * @param array $values
     * @param string $idVisit
     */
    public function updateConversions(array $values, $idVisit)
    {
        $sql = "UPDATE " . Common::prefixTable('log_conversion')
            . " SET " . $this->getColumnSetExpressions(array_keys($values))
            . " WHERE idvisit = ?";

        $this->update($sql, $values, $idVisit);
    }

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

    /**
     * TODO
     *
     * @param $logTable
     * @param $conditions
     * @param $iterationStep
     * @param $callback
     */
    public function forAllLogs($logTable, $fields, $conditions, $iterationStep, $callback)
    {
        $idField = $this->getIdFieldForLogTable($logTable);
        list($query, $bind) = $this->createLogIterationQuery($logTable, $idField, $fields, $conditions, $iterationStep);

        $lastId = 0;
        do {
            $rows = Db::fetchAll($query, array_merge(array($lastId), $bind));
            if (!empty($rows)) {
                $lastId = $rows[count($rows) - 1][$idField];

                $callback($rows);
            }
        } while (count($rows) == $iterationStep);
    }

    /**
     * TODO
     *
     * @param $idVisits
     * @return int
     */
    public function deleteVisits($idVisits)
    {
        $sql = "DELETE FROM `" . Common::prefixTable('log_visit') . "` WHERE idvisit IN "
             . $this->getInFieldExpressionWithInts($idVisits);

        $statement = Db::exec($sql);
        return $statement->rowCount();
    }

    /**
     * TODO
     *
     * @param $visitIds
     * @return int
     */
    public function deleteVisitActionsForVisits($visitIds)
    {
        $sql = "DELETE FROM `" . Common::prefixTable('log_link_visit_action') . "` WHERE idvisit IN "
             . $this->getInFieldExpressionWithInts($visitIds);

        $statement = Db::exec($sql);
        return $statement->rowCount();
    }

    /**
     * TODO
     *
     * @param $visitActionIds
     * @return int
     */
    public function deleteVisitActions($visitActionIds)
    {
        $sql = "DELETE FROM `" . Common::prefixTable('log_link_visit_action') . "` WHERE idlink_va IN "
             . $this->getInFieldExpressionWithInts($visitActionIds);

        $statement = Db::exec($sql);
        return $statement->rowCount();
    }

    /**
     * TODO
     *
     * @param $visitIds
     * @return int
     */
    public function deleteConversions($visitIds)
    {
        $sql = "DELETE FROM `" . Common::prefixTable('log_conversion') . "` WHERE idvisit IN "
             . $this->getInFieldExpressionWithInts($visitIds);

        $statement = Db::exec($sql);
        return $statement->rowCount();
    }

    /**
     * TODO
     *
     * @param $visitIds
     * @return int
     */
    public function deleteConversionItems($visitIds)
    {
        $sql = "DELETE FROM `" . Common::prefixTable('log_conversion_item') . "` WHERE idvisit IN "
             . $this->getInFieldExpressionWithInts($visitIds);

        $statement = Db::exec($sql);
        return $statement->rowCount();
    }

    /**
     * @param array $columnsToSet
     * @return string
     */
    protected function getColumnSetExpressions(array $columnsToSet)
    {
        $columnsToSet = array_map(
            function ($column) {
                return $column . ' = ?';
            },
            $columnsToSet
        );

        return implode(', ', $columnsToSet);
    }

    /**
     * @param array $values
     * @param $idVisit
     * @param $sql
     * @return \Zend_Db_Statement
     * @throws \Exception
     */
    protected function update($sql, array $values, $idVisit)
    {
        return Db::query($sql, array_merge(array_values($values), array($idVisit)));
    }

    private function getIdFieldForLogTable($logTable)
    {
        switch ($logTable) {
            case 'log_visit':
                return 'idvisit';
            case 'log_link_visit_action':
                return 'idlink_va';
            case 'log_conversion':
                return 'idvisit';
            case 'log_conversion_item':
                return 'idvisit';
            case 'log_action':
                return 'idaction';
            default:
                throw new \InvalidArgumentException("Unknown log table '$logTable'.");
        }
    }

    // TODO: move to query builder class? only relevant to relational backends
    private function createLogIterationQuery($logTable, $idField, $fields, $conditions, $iterationStep)
    {
        $bind = array();

        $sql = "SELECT " . implode(', ', $fields) . " FROM `" . Common::prefixTable($logTable) . "` WHERE $idField > ?";

        foreach ($conditions as $condition) {
            list($column, $operator, $value) = $condition;

            if (is_array($value)) {
                $sql .= " AND $column IN (" . Common::getSqlStringFieldsArray($value) . ")";

                $bind = array_merge($bind, $value);
            } else {
                $sql .= " AND $column $operator ?";

                $bind[] = $value;
            }
        }

        $sql .= " LIMIT " . (int)$iterationStep;

        return array($sql, $bind);
    }

    private function getInFieldExpressionWithInts($idVisits)
    {
        $sql = "(";

        $isFirst = true;
        foreach ($idVisits as $idVisit) {
            if ($isFirst) {
                $isFirst = false;
            } else {
                $sql .= ', ';
            }

            $sql .= (int)$idVisit;
        }

        $sql .= ")";

        return $sql;
    }
}