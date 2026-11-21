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
use Piwik\Db\Schema;
use Piwik\Plugin\Dimension\DimensionMetadataProvider;
use Piwik\Plugin\LogTablesProvider;

/**
 * DAO that queries log tables.
 */
class RawLogDao
{
    public const DELETE_UNUSED_ACTIONS_TEMP_TABLE_NAME = 'tmp_log_actions_to_keep';

    /**
     * Log tables scanned by their own auto-increment key, whose action references are never updated: a new
     * reference always gets a higher id, so a scan can resume above the last id it read. Tables scanned by
     * idvisit cannot: a cart update or a conversion adds references to an existing visit.
     */
    private const TABLES_SCANNED_IN_INSERT_ORDER = ['log_link_visit_action', 'log_page_view_time'];

    /**
     * SQLite: the most actions one write transaction of the purge deletes.
     *
     * @var int
     */
    protected $sqliteDeleteChunkSize = 1000;

    /**
     * SQLite: the most catch-up scans one purge makes before it stops until its next run.
     *
     * @var int
     */
    protected $sqliteMaxCatchUpScans = 20;

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
            . " FROM `" . Common::prefixTable('log_visit') . "`"
             . " WHERE visit_last_action_time >= ? AND visit_last_action_time < ?";

        $bind = array($from, $to);

        return (int) Db::fetchOne($sql, $bind);
    }

    /**
     * Iterates over logs in a log table in chunks. Parameters to this function are as backend agnostic
     * as possible w/o dramatically increasing code complexity.
     *
     * @param string $logTable The log table name. Unprefixed, eg, `log_visit`.
     * @param string[] $fields The columns to select.
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
     * @param bool $willDelete Set to true if you will make sure to delete all rows that were fetched. If you are in
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

        [$query, $bind] = $this->createLogIterationQuery($logTable, $idField, $fields, $conditions, $iterationStep);

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
     * On SQLite, trackers fail instead of waiting once the write lock is held longer than their busy timeout,
     * so the locked phase is split into bounded transactions. Such a purge can stop before it is done, when more
     * visits arrive during the run than one transaction may rescan; the next run starts over.
     *
     * @return bool whether every unused action was deleted; false only on SQLite, when the purge stopped early
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

        $locked = false;
        try {
            // do large insert (inserting everything before maxIds) w/o locking tables...
            $this->insertActionsToKeep($maxIds, $deleteOlderThanMax = true, $max_rows_per_query);

            if (Db\Sqlite\Connection::isSqliteConnection(Db::get())) {
                $complete = $this->deleteUnusedActionsInChunks($maxIds, (int) $max_rows_per_query);
                $this->dropTempTableForStoringUsedActions();
                return $complete;
            }

            // ... then do small insert w/ locked tables to minimize the amount of time tables are locked.
            $this->lockLogTables();
            $locked = true;
            $this->insertActionsToKeep($maxIds, $deleteOlderThanMax = false, $max_rows_per_query);

            // delete before unlocking tables so there's no chance a new log row that references an
            // unused action will be inserted.
            $this->deleteUnusedActions();

            Db::unlockAllTables();
        } catch (\Throwable $e) {
            // release only the locks taken here (on SQLite, rolling back the partial delete) and
            // drop the temporary table for the next run
            if ($locked) {
                try {
                    Db::abortTableLocks();
                } catch (\Throwable $ignored) {
                }
            }
            try {
                $this->dropTempTableForStoringUsedActions();
            } catch (\Throwable $ignored) {
            }
            throw $e;
        }

        $this->dropTempTableForStoringUsedActions();

        return true;
    }

    /**
     * SQLite's locked phase. Each transaction rescans what was written since the last one, within $budget ids,
     * then deletes at most $sqliteDeleteChunkSize unused actions. Tables scanned in insert order are caught up
     * outside the lock first; tables scanned by idvisit are always rescanned from $maxIds, as MySQL's locked
     * scan does, so once visits since the start of the run exceed the budget, the purge stops.
     */
    private function deleteUnusedActionsInChunks(array $maxIds, int $budget): bool
    {
        [$logActionTable, $tempTableName] = Common::prefixTables('log_action', self::DELETE_UNUSED_ACTIONS_TEMP_TABLE_NAME);
        $referencingTables = array_keys($this->dimensionMetadataProvider->getActionReferenceColumnsByTable());

        $scannedTo = [];
        foreach ($referencingTables as $table) {
            if (in_array($table, self::TABLES_SCANNED_IN_INSERT_ORDER, true)) {
                $scannedTo[$table] = (int) $maxIds[$table];
            }
        }

        // an action created during the run may not be referenced yet by the request that created it
        $lastCandidate = (int) Db::fetchOne("SELECT MAX(idaction) FROM `$logActionTable`");
        $chunkSize = max(1, (int) $this->sqliteDeleteChunkSize);
        $afterIdAction = 0;
        $catchUpScans = 0;

        while (true) {
            if (!$this->catchUpTablesScannedInInsertOrder($scannedTo, $budget, $catchUpScans)) {
                return false;
            }

            $candidates = array_column(Db::fetchAll(
                "SELECT idaction FROM `$logActionTable` la
                  WHERE idaction > ? AND idaction <= ?
                    AND NOT EXISTS (SELECT 1 FROM `$tempTableName` tmp WHERE tmp.idaction = la.idaction)
               ORDER BY idaction LIMIT $chunkSize",
                [$afterIdAction, $lastCandidate]
            ), 'idaction');
            if (empty($candidates)) {
                return true;
            }

            $this->lockLogTables();
            try {
                $currentMaxIds = $this->getMaxIdsInLogTables();

                $rescanFrom = [];
                $byVisit = 0;
                $inInsertOrder = 0;
                foreach ($referencingTables as $table) {
                    $rescanFrom[$table] = $scannedTo[$table] ?? (int) $maxIds[$table];
                    $outstanding = max(0, (int) $currentMaxIds[$table] - $rescanFrom[$table]);
                    if (isset($scannedTo[$table])) {
                        $inInsertOrder += $outstanding;
                    } else {
                        $byVisit += $outstanding;
                    }
                }

                if ($byVisit + $inInsertOrder > $budget) {
                    Db::abortTableLocks();
                    if ($byVisit > $budget) {
                        // only grows during the run: a later transaction cannot do better
                        return false;
                    }
                    // the lock waited while trackers wrote: catch up again outside it
                    continue;
                }

                foreach ($referencingTables as $table) {
                    $this->insertActionsToKeepBetween($table, $rescanFrom[$table], (int) $currentMaxIds[$table], $budget);
                }

                Db::query("DELETE FROM `$logActionTable`
                            WHERE idaction IN (" . implode(',', array_map('intval', $candidates)) . ")
                              AND NOT EXISTS (SELECT 1 FROM `$tempTableName` tmp WHERE tmp.idaction = `$logActionTable`.idaction)");

                Db::unlockAllTables();
            } catch (\Throwable $e) {
                try {
                    Db::abortTableLocks();
                } catch (\Throwable $ignored) {
                }
                throw $e;
            }

            foreach ($scannedTo as $table => $id) {
                $scannedTo[$table] = (int) $currentMaxIds[$table];
            }
            $afterIdAction = (int) end($candidates);
        }
    }

    /**
     * Scans, outside the lock, the rows tables scanned in insert order got since their last scan, until each is
     * at most $budget ids behind. False when that took more than $sqliteMaxCatchUpScans scans.
     *
     * @param array<string, int> $scannedTo
     */
    private function catchUpTablesScannedInInsertOrder(array &$scannedTo, int $budget, int &$catchUpScans): bool
    {
        $idColumns = $this->getTableIdColumns();
        while (true) {
            $behind = [];
            foreach ($scannedTo as $table => $id) {
                $idColumn = $idColumns[$table];
                $maxId = (int) Db::fetchOne("SELECT MAX($idColumn) FROM `" . Common::prefixTable($table) . "`");
                if ($maxId - $id > $budget) {
                    $behind[$table] = $maxId;
                }
            }

            if (empty($behind)) {
                return true;
            }

            if (++$catchUpScans > $this->sqliteMaxCatchUpScans) {
                return false;
            }

            foreach ($behind as $table => $maxId) {
                $this->insertActionsToKeepBetween($table, $scannedTo[$table], $maxId, $budget);
                $scannedTo[$table] = $maxId;
            }
        }
    }

    /**
     * Returns true if the given site received some visits between the specified timeframe. The
     * start date and the end date are included in the time frame.
     *
     * @param string $fromDateTime
     * @param string $toDateTime
     * @param int $idSite
     * @return bool true if there are visits for this site between the given timeframe, false if not
     */
    public function hasSiteVisitsBetweenTimeframe($fromDateTime, $toDateTime, $idSite)
    {
        $sites = Db::fetchOne("SELECT 1
                FROM `" . Common::prefixTable('log_visit') . "`
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
            [$column, $operator, $value] = $condition;

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
            $result[$table] = Db::fetchOne("SELECT MAX($idCol) FROM `" . Common::prefixTable($table) . "`");
        }

        return $result;
    }

    private function createTempTableForStoringUsedActions()
    {
        $sql = Schema::getInstance()->getCreateTemporaryTableSql(
            Common::prefixTable(self::DELETE_UNUSED_ACTIONS_TEMP_TABLE_NAME),
            'idaction INTEGER(10) UNSIGNED NOT NULL, PRIMARY KEY (idaction)'
        );
        Db::query($sql);
    }

    private function dropTempTableForStoringUsedActions()
    {
        Db::query(Schema::getInstance()->getDropTemporaryTableSql(Common::prefixTable(self::DELETE_UNUSED_ACTIONS_TEMP_TABLE_NAME)));
    }

    // protected for testing purposes
    protected function insertActionsToKeep($maxIds, $olderThan = true, $insertIntoTempIterationStep = 100000)
    {
        $idColumns = $this->getTableIdColumns();
        foreach (array_keys($this->dimensionMetadataProvider->getActionReferenceColumnsByTable()) as $table) {
            $idCol = $idColumns[$table];

            if ($olderThan) {
               // Why start on zero? When running for a couple of months, this will generate about 10000+ queries with zero result. Use the lowest value instead.... saves a LOT of waiting time!
                $start = (int)Db::fetchOne("SELECT MIN($idCol) FROM `" . Common::prefixTable($table) . "`");
                $finish = $maxIds[$table];
            } else {
                $start = $maxIds[$table];
                $finish = (int)Db::fetchOne("SELECT MAX($idCol) FROM `" . Common::prefixTable($table) . "`");
            }

            $this->insertActionsToKeepBetween($table, (int) $start, (int) $finish, $insertIntoTempIterationStep);
        }
    }

    /**
     * Adds to the temporary table the actions referenced by the rows of $table whose id is from $start to $finish.
     */
    private function insertActionsToKeepBetween(string $table, int $start, int $finish, int $insertIntoTempIterationStep): void
    {
        $tempTableName = Common::prefixTable(self::DELETE_UNUSED_ACTIONS_TEMP_TABLE_NAME);
        $columns = $this->dimensionMetadataProvider->getActionReferenceColumnsByTable()[$table];
        $idCol = $this->getTableIdColumns()[$table];
        $insertIntoTempIterationStep = max(1, $insertIntoTempIterationStep);

        // Create select query for requesting ALL needed fields at once
        $sql = "SELECT " . implode(',', $columns) . " FROM `" . Common::prefixTable($table) . "` WHERE $idCol >= ? AND $idCol < ?";

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
                    $insert = Schema::getInstance()->getInsertIgnoreSql() . ' INTO ' . $tempTableName . ' VALUES (';
                    $insert .= implode('),(', $keepValues);
                    $insert .= ')';

                    Db::exec($insert);
                    $keepValues = [];
                }
            }

            if (empty($keepValues)) {
                continue;
            }

            $insert = Schema::getInstance()->getInsertIgnoreSql() . ' INTO ' . $tempTableName . ' VALUES (';
            $insert .= implode('),(', $keepValues);
            $insert .= ')';

            Db::exec($insert);
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
        [$logActionTable, $tempTableName] = Common::prefixTables("log_action", self::DELETE_UNUSED_ACTIONS_TEMP_TABLE_NAME);

        if (Db\Sqlite\Connection::isSqliteConnection(Db::get())) {
            // SQLite has no multiple-table DELETE: the same rows, with an anti-join
            Db::query("DELETE FROM `$logActionTable`
                        WHERE NOT EXISTS (SELECT 1 FROM `$tempTableName` tmp WHERE tmp.idaction = `$logActionTable`.idaction)");
            return;
        }

        $deleteSql = "DELETE LOW_PRIORITY QUICK IGNORE `$logActionTable`
						FROM `$logActionTable`
				   LEFT JOIN `$tempTableName` tmp ON tmp.idaction = `$logActionTable`.idaction
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
