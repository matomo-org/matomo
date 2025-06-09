<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\DataAccess;

use Piwik\Common;
use Piwik\Config as PiwikConfig;
use Piwik\Container\StaticContainer;
use Piwik\Db;
use Piwik\Plugin\Dimension\DimensionMetadataProvider;
use Piwik\Plugin\LogTablesProvider;

/**
 * DAO that queries log tables.
 */
class RawLogDao
{
    public const DELETE_UNUSED_ACTIONS_TEMP_TABLE_NAME = 'tmp_log_actions_to_keep';

    /**
     * @var DimensionMetadataProvider
     */
    private $dimensionMetadataProvider;

    /**
     * @var LogTablesProvider
     */
    private $logTablesProvider;

    public function __construct(?DimensionMetadataProvider $provider = null, ?LogTablesProvider $logTablesProvider = null)
    {
        $this->dimensionMetadataProvider = $provider ?: StaticContainer::get('Piwik\Plugin\Dimension\DimensionMetadataProvider');
        $this->logTablesProvider = $logTablesProvider ?: StaticContainer::get('Piwik\Plugin\LogTablesProvider');
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
     * @param string $willDelete Set to true if you will make sure to delete all rows that were fetched. If you are in
     *                           doubt and not sure if to set true or false, use "false". Setting it to true will
     *                           enable an internal performance improvement but it can result in an endless loop if not
     *                           used properly.
     */
    public function forAllLogs($logTable, $fields, $conditions, $iterationStep, $callback, $willDelete)
    {
        $lastId = 0;

        if ($willDelete) {
            // we don't want to look at eg idvisit so the query will be mostly index covered as the
            // "where idvisit > 0 ... ORDER BY idvisit ASC" will be gone... meaning we don't need to look at a huge range
            // of visits...
            $idField = null;
            $bindFunction = function ($bind, $lastId) {
                return $bind;
            };
        } else {
            // when we are not deleting, we need to ensure to iterate over each visitor step by step... meaning we
            // need to remember which visit we have already looked at and which one not. Therefore we need to apply
            // "where idvisit > $lastId" in the query and "order by idvisit ASC"
            $idField = $this->getIdFieldForLogTable($logTable);
            $bindFunction = function ($bind, $lastId) {
                return array_merge(array($lastId), $bind);
            };
        }

        list($query, $bind) = $this->createLogIterationQuery($logTable, $idField, $fields, $conditions, $iterationStep);

        do {
            $rows = Db::fetchAll($query, call_user_func($bindFunction, $bind, $lastId));
            if (!empty($rows)) {
                if ($idField) {
                    $lastId = $rows[count($rows) - 1][$idField];
                }
                $callback($rows);
            }
        } while (count($rows) == $iterationStep);
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

        // get max rows to analyze
        $max_rows_per_query = PiwikConfig::getInstance()->Deletelogs['delete_logs_unused_actions_max_rows_per_query'];

        $this->createTempTableForStoringUsedActions();

        // do large insert (inserting everything before maxIds) w/o locking tables...
        $this->insertActionsToKeep($maxIds, $deleteOlderThanMax = true, $max_rows_per_query);

        // ... then do small insert w/ locked tables to minimize the amount of time tables are locked.
        $this->lockLogTables();
        $this->insertActionsToKeep($maxIds, $deleteOlderThanMax = false, $max_rows_per_query);

        // delete before unlocking tables so there's no chance a new log row that references an
        // unused action will be inserted.
        $this->deleteUnusedActions();

        Db::unlockAllTables();

        $this->dropTempTableForStoringUsedActions();
    }

    /**
     * Returns the list of the website IDs that received some visits between the specified timestamp. The
     * start date and the end date is included in the time frame.
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
                AND visit_last_action_time >= ?
                AND visit_last_action_time <= ?
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

    protected function getIdFieldForLogTable($logTable)
    {
        $idColumns = $this->getTableIdColumns();

        if (isset($idColumns[$logTable])) {
            return $idColumns[$logTable];
        }

        throw new \InvalidArgumentException("Unknown log table '$logTable'.");
    }

    // TODO: instead of creating a log query like this, we should re-use segments. to do this, however, there must be a 1-1
    //       mapping for dimensions => segments, and each dimension should automatically have a segment.
    private function createLogIterationQuery($logTable, $idField, $fields, $conditions, $iterationStep)
    {
        $bind = array();

        $sql = "SELECT " . implode(', ', $fields) . " FROM `" . Common::prefixTable($logTable) . "` WHERE ";

        $parts = array();

        if ($idField) {
            $parts[] = "$idField > ?";
        }

        foreach ($conditions as $condition) {
            list($column, $operator, $value) = $condition;

            if (is_array($value)) {
                $parts[] = "$column IN (" . Common::getSqlStringFieldsArray($value) . ")";

                $bind = array_merge($bind, $value);
            } else {
                $parts[] = "$column $operator ?";

                $bind[] = $value;
            }
        }
        $sql .= implode(' AND ', $parts);

        if ($idField) {
            $sql .= " ORDER BY $idField ASC";
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

    protected function getMaxIdsInLogTables()
    {
        $idColumns = $this->getTableIdColumns();
        $tables = array_keys($idColumns);

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
					idaction INTEGER(10) UNSIGNED NOT NULL,
					PRIMARY KEY (idaction)
				)";
        Db::query($sql);
    }

    private function dropTempTableForStoringUsedActions()
    {
        $sql = "DROP TABLE " . Common::prefixTable(self::DELETE_UNUSED_ACTIONS_TEMP_TABLE_NAME);
        Db::query($sql);
    }

    // protected for testing purposes
    protected function insertActionsToKeep($maxIds, $olderThan = true, $insertIntoTempIterationStep = 100000)
    {
        $tempTableName = Common::prefixTable(self::DELETE_UNUSED_ACTIONS_TEMP_TABLE_NAME);

        $idColumns = $this->getTableIdColumns();
        foreach ($this->dimensionMetadataProvider->getActionReferenceColumnsByTable() as $table => $columns) {
            $idCol = $idColumns[$table];
            // Create select query for requesting ALL needed fields at once
            $sql = "SELECT " . implode(',', $columns) . " FROM " . Common::prefixTable($table) . " WHERE $idCol >= ? AND $idCol < ?";

            if ($olderThan) {
               // Why start on zero? When running for a couple of months, this will generate about 10000+ queries with zero result. Use the lowest value instead.... saves a LOT of waiting time!
                $start = (int) Db::fetchOne("SELECT MIN($idCol) FROM " . Common::prefixTable($table));
                $finish = $maxIds[$table];
            } else {
                $start = $maxIds[$table];
                $finish = (int) Db::fetchOne("SELECT MAX($idCol) FROM " . Common::prefixTable($table));
            }
            // Borrowed from Db::segmentedFetchAll
            // Request records per $insertIntoTempIterationStep amount
            // Loop over the result set, mapping all numeric fields in a single insert query

            // Insert query would be: INSERT IGNORE INTO [temp_table] VALUES (X),(Y),(Z) depending on the amount of fields requested per row
            for ($i = $start; $i <= $finish; $i += $insertIntoTempIterationStep) {
                $currentParams = array($i, $i + $insertIntoTempIterationStep);
                $result        = Db::fetchAll($sql, $currentParams);
                // Now we loop over the result set of max $insertIntoTempIterationStep rows and create insert queries
                $keepValues = [];
                foreach ($result as $row) {
                     $keepValues = array_merge($keepValues, array_filter(array_values($row), "is_numeric"));
                    if (count($keepValues) >= 1000) {
                        $insert = 'INSERT IGNORE INTO ' . $tempTableName . ' VALUES (';
                        $insert .= implode('),(', $keepValues);
                        $insert .= ')';

                        Db::exec($insert);
                        $keepValues = [];
                    }
                }

                $insert = 'INSERT IGNORE INTO ' . $tempTableName . ' VALUES (';
                $insert .= implode('),(', $keepValues);
                $insert .= ')';

                Db::exec($insert);
            }
        }
    }

    private function lockLogTables()
    {
        $tables = $this->getTableIdColumns();
        unset($tables['log_action']); // we write lock it
        $tableNames = array_keys($tables);

        $readLocks = array();
        foreach ($tableNames as $tableName) {
            $readLocks[] = Common::prefixTable($tableName);
        }

        Db::lockTables(
            $readLocks,
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

    protected function getTableIdColumns()
    {
        $columns = array();

        foreach ($this->logTablesProvider->getAllLogTables() as $logTable) {
            $idColumn = $logTable->getIdColumn();

            if (!empty($idColumn)) {
                $columns[$logTable->getName()] = $idColumn;
            }
        }

        return $columns;
    }
}
