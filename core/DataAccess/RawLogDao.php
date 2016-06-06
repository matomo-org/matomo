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
use Piwik\Plugin\Dimension\DimensionMetadataProvider;

/**
 * DAO that queries log tables.
 */
class RawLogDao
{
    const DELETE_UNUSED_ACTIONS_TEMP_TABLE_NAME = 'tmp_log_actions_to_keep';

    /**
     * @var DimensionMetadataProvider
     */
    private $dimensionMetadataProvider;

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
     * @return int
     */
    public function countVisitsWithDatesLimit($from, $to)
    {
        $sql = "SELECT COUNT(*) AS num_rows"
             . " FROM " . Common::prefixTable('log_visit')
             . " WHERE visit_last_action_time >= ? AND visit_last_action_time < ?";

        $bind = array($from, $to);

        return (int) Db::fetchOne($sql, $bind);
    }

    /**
     * Iterates over logs in a log table in chunks. Parameters to this function are as backend agnostic
     * as possible w/o dramatically increasing code complexity.
     *
     * @param string $logTable The log table name. Unprefixed, eg, `log_visit`.
     * @param array[] $conditions An array describing the conditions logs must match in the query. Translates to
     *                            the WHERE part of a SELECT statement. Each element must contain three elements:
     *
     *                            * the column name
     *                            * the operator (ie, '=', '<>', '<', etc.)
     *                            * the operand (ie, a value)
     *
     *                            The elements are AND-ed together.
     *
     *                            Example:
     *
     *                            ```
     *                            array(
     *                                array('visit_first_action_time', '>=', ...),
     *                                array('visit_first_action_time', '<', ...)
     *                            )
     *                            ```
     * @param int $iterationStep The number of rows to query at a time.
     * @param callable $callback The callback that processes each chunk of rows.
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
     * Deletes visits with the supplied IDs from log_visit. This method does not cascade, so rows in other tables w/
     * the same visit ID will still exist.
     *
     * @param int[] $idVisits
     * @return int The number of deleted rows.
     */
    public function deleteVisits($idVisits)
    {
        $sql = "DELETE FROM `" . Common::prefixTable('log_visit') . "` WHERE idvisit IN "
             . $this->getInFieldExpressionWithInts($idVisits);

        $statement = Db::query($sql);
        return $statement->rowCount();
    }

    /**
     * Deletes visit actions for the supplied visit IDs from log_link_visit_action.
     *
     * @param int[] $visitIds
     * @return int The number of deleted rows.
     */
    public function deleteVisitActionsForVisits($visitIds)
    {
        $sql = "DELETE FROM `" . Common::prefixTable('log_link_visit_action') . "` WHERE idvisit IN "
             . $this->getInFieldExpressionWithInts($visitIds);

        $statement = Db::query($sql);
        return $statement->rowCount();
    }

    /**
     * Deletes conversions for the supplied visit IDs from log_conversion. This method does not cascade, so
     * conversion items will not be deleted.
     *
     * @param int[] $visitIds
     * @return int The number of deleted rows.
     */
    public function deleteConversions($visitIds)
    {
        $sql = "DELETE FROM `" . Common::prefixTable('log_conversion') . "` WHERE idvisit IN "
             . $this->getInFieldExpressionWithInts($visitIds);

        $statement = Db::query($sql);
        return $statement->rowCount();
    }

    /**
     * Deletes conversion items for the supplied visit IDs from log_conversion_item.
     *
     * @param int[] $visitIds
     * @return int The number of deleted rows.
     */
    public function deleteConversionItems($visitIds)
    {
        $sql = "DELETE FROM `" . Common::prefixTable('log_conversion_item') . "` WHERE idvisit IN "
             . $this->getInFieldExpressionWithInts($visitIds);

        $statement = Db::query($sql);
        return $statement->rowCount();
    }

    /**
     * Deletes all unused entries from the log_action table. This method uses a temporary table to store used
     * actions, and then deletes rows from log_action that are not in this temporary table.
     *
     * Table locking is required to avoid concurrency issues.
     *
     * @throws \Exception If table locking permission is not granted to the current MySQL user.
     */
    public function deleteUnusedLogActions()
    {
        if (!Db::isLockPrivilegeGranted()) {
            throw new \Exception("RawLogDao.deleteUnusedLogActions() requires table locking permission in order to complete without error.");
        }

        // get current max ID in log tables w/ idaction references.
        $maxIds = $this->getMaxIdsInLogTables();

        $this->createTempTableForStoringUsedActions();

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
     * Returns the list of the website IDs that received some visits between the specified timestamp.
     *
     * @param string $fromDateTime
     * @param string $toDateTime
     * @return bool true if there are visits for this site between the given timeframe, false if not
     */
    public function hasSiteVisitsBetweenTimeframe($fromDateTime, $toDateTime, $idSite)
    {
        $sites = Db::fetchOne("SELECT 1
                FROM " . Common::prefixTable('log_visit') . "
                WHERE idsite = ?
                AND visit_last_action_time > ?
                AND visit_last_action_time < ?
                LIMIT 1", array($idSite, $fromDateTime, $toDateTime));

        return (bool) $sites;
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

    // TODO: instead of creating a log query like this, we should re-use segments. to do this, however, there must be a 1-1
    //       mapping for dimensions => segments, and each dimension should automatically have a segment.
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

        $sql .= " ORDER BY $idField ASC LIMIT " . (int)$iterationStep;

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

    private function createTempTableForStoringUsedActions()
    {
        $sql = "CREATE TEMPORARY TABLE " . Common::prefixTable(self::DELETE_UNUSED_ACTIONS_TEMP_TABLE_NAME) . " (
					idaction INT(11),
					PRIMARY KEY (idaction)
				)";
        Db::query($sql);
    }

    // protected for testing purposes
    protected function insertActionsToKeep($maxIds, $olderThan = true, $insertIntoTempIterationStep = 100000)
    {
        $tempTableName = Common::prefixTable(self::DELETE_UNUSED_ACTIONS_TEMP_TABLE_NAME);

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

                Db::segmentedQuery($sql, $start, $finish, $insertIntoTempIterationStep);
            }
        }
    }

    private function lockLogTables()
    {
        Db::lockTables(
            $readLocks = Common::prefixTables('log_conversion', 'log_link_visit_action', 'log_visit', 'log_conversion_item'),
            $writeLocks = Common::prefixTables('log_action')
        );
    }

    private function deleteUnusedActions()
    {
        list($logActionTable, $tempTableName) = Common::prefixTables("log_action", self::DELETE_UNUSED_ACTIONS_TEMP_TABLE_NAME);

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