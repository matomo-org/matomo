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
use Piwik\Container\StaticContainer;
use Piwik\Db;
use Piwik\Piwik;
use Piwik\Plugin\Dimension\DimensionMetadataProvider;

/**
 * DAO that queries log tables.
 */
class RawLogDao
{
    const TEMP_TABLE_NAME = 'tmp_log_actions_to_keep'; // TODO: rename TEMP_TABLE_NAME const name for the method its used in

    /**
     * @var DimensionMetadataProvider
     */
    private $dimensionMetadataProvider;

    /**
     * The max set of rows each table scan select should query at one time. TODO: this was copied from LogDataPurger. should be specified on construction.
     */
    public static $selectSegmentSize = 100000;

    public function __construct(DimensionMetadataProvider $provider = null)
    {
        $this->dimensionMetadataProvider = $provider ?: StaticContainer::get('Piwik\Plugin\Dimension\DimensionMetadataProvider');
    }

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

        $statement = Db::query($sql);
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

        $statement = Db::query($sql);
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

        $statement = Db::query($sql);
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

        $statement = Db::query($sql);
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

        $statement = Db::query($sql);
        return $statement->rowCount();
    }

    /**
     * TODO
     * @throws \Exception
     */
    public function deleteUnusedLogActions()
    {
        if (!Db::isLockPrivilegeGranted()) {
            throw new \Exception("RawLogDao.deleteUnusedLogActions() requires table locking permission in order to complete without error.");
        }

        // get current max ID in log tables w/ idaction references.
        $maxIds = $this->getMaxIdsInLogTables();

        $this->createTempTable();

        // do large insert (inserting everything before maxIds) w/o locking tables...
        $this->insertActionsToKeep($maxIds, $deleteOlderThanMax = true);

        // ... then do small insert w/ locked tables to minimize the amount of time tables are locked.
        $this->lockLogTables();
        $this->insertActionsToKeep($maxIds, $deleteOlderThanMax = false);

        // delete before unlocking tables so there's no chance a new log row that references an
        // unused action will be inserted.
        $this->deleteUnusedActions();
        Db::unlockAllTables();
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


    private function getMaxIdsInLogTables()
    {
        $tables = array('log_conversion', 'log_link_visit_action', 'log_visit', 'log_conversion_item');
        $idColumns = $this->getTableIdColumns();

        $result = array();
        foreach ($tables as $table) {
            $idCol = $idColumns[$table];
            $result[$table] = Db::fetchOne("SELECT MAX($idCol) FROM " . Common::prefixTable($table));
        }

        return $result;
    }

    private function createTempTable()
    {
        $sql = "CREATE TEMPORARY TABLE " . Common::prefixTable(self::TEMP_TABLE_NAME) . " (
					idaction INT(11),
					PRIMARY KEY (idaction)
				)";
        Db::query($sql);
    }

    private function insertActionsToKeep($maxIds, $olderThan = true)
    {
        $tempTableName = Common::prefixTable(self::TEMP_TABLE_NAME);

        $idColumns = $this->getTableIdColumns();
        foreach ($this->dimensionMetadataProvider->getActionReferenceColumnsByTable() as $table => $columns) {
            $idCol = $idColumns[$table];

            foreach ($columns as $col) {
                $select = "SELECT $col FROM " . Common::prefixTable($table) . " WHERE $idCol >= ? AND $idCol < ?";
                $sql = "INSERT IGNORE INTO $tempTableName $select";

                if ($olderThan) {
                    $start = 0;
                    $finish = $maxIds[$table];
                } else {
                    $start = $maxIds[$table];
                    $finish = Db::fetchOne("SELECT MAX($idCol) FROM " . Common::prefixTable($table));
                }

                Db::segmentedQuery($sql, $start, $finish, self::$selectSegmentSize);
            }
        }

        // allow code to be executed after data is inserted. for concurrency testing purposes.
        if ($olderThan) {
            /**
             * @ignore
             */
            Piwik::postEvent("LogDataPurger.ActionsToKeepInserted.olderThan"); // TODO: use DI/descendant class instead
        } else {
            /**
             * @ignore
             */
            Piwik::postEvent("LogDataPurger.ActionsToKeepInserted.newerThan");
        }
    }

    private function lockLogTables()
    {
        Db::lockTables(
            $readLocks = Common::prefixTables('log_conversion',
                'log_link_visit_action',
                'log_visit',
                'log_conversion_item'),
            $writeLocks = Common::prefixTables('log_action')
        );
    }

    private function deleteUnusedActions()
    {
        list($logActionTable, $tempTableName) = Common::prefixTables("log_action", self::TEMP_TABLE_NAME);

        $deleteSql = "DELETE LOW_PRIORITY QUICK IGNORE $logActionTable
						FROM $logActionTable
				   LEFT JOIN $tempTableName tmp ON tmp.idaction = $logActionTable.idaction
					   WHERE tmp.idaction IS NULL";

        Db::query($deleteSql);
    }

    private function getTableIdColumns()
    {
        return array(
            'log_link_visit_action' => 'idlink_va',
            'log_conversion'        => 'idvisit',
            'log_visit'             => 'idvisit',
            'log_conversion_item'   => 'idvisit'
        );
    }
}