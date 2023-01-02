<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\DataAccess\LogQueryBuilder;

use Exception;
use Piwik\Common;
use Piwik\DataAccess\LogAggregator;
use Piwik\Tracker\LogTable;

class JoinGenerator
{
    /**
     * @var JoinTables
     */
    protected $tables;

    /**
     * @var bool
     */
    private $joinWithSubSelect = false;

    /**
     * @var string
     */
    private $joinString = '';

    /**
     * @var array
     */
    private $nonVisitJoins = array();

    public function __construct(JoinTables $tables)
    {
        $this->tables = $tables;
        $this->addMissingTablesNeededForJoins();
    }
    
    private function addMissingTablesNeededForJoins()
    {
        foreach ($this->tables as $index => $table) {
            if (is_array($table)) {
                continue;
            }

            $logTable = $this->tables->getLogTable($table);

            if (!$logTable->getColumnToJoinOnIdVisit()) {
                $tableNameToJoin = $logTable->getLinkTableToBeAbleToJoinOnVisit();

                if (empty($tableNameToJoin) && $logTable->getWaysToJoinToOtherLogTables()) {
                    foreach ($logTable->getWaysToJoinToOtherLogTables() as $otherLogTable => $column) {
                        if ($this->tables->hasJoinedTable($otherLogTable)) {
                            $this->tables->addTableDependency($table, $otherLogTable);
                            continue;
                        }
                        if ($this->tables->isTableJoinableOnVisit($otherLogTable) || $this->tables->isTableJoinableOnAction($otherLogTable)) {
                            $this->addMissingTablesForOtherTableJoin($otherLogTable, $table);
                        }
                    }
                    continue;
                }

                if ($index > 0 && !$this->tables->hasJoinedTable($tableNameToJoin)) {
                    $this->tables->addTableToJoin($tableNameToJoin);
                }

                if ($this->tables->hasJoinedTable($tableNameToJoin)) {
                    $this->generateNonVisitJoins($table, $tableNameToJoin, $index);
                }
            }
        }

        foreach ($this->tables as $index => $table) {
            if (is_array($table)) {
                if (!isset($table['tableAlias'])) {
                    $tableName = $table['table'];
                    $numTables = count($this->tables);
                    for ($j = $index + 1; $j < $numTables; $j++) {
                        if (!isset($this->tables[$j])) {
                            continue;
                        }

                        $tableOther = $this->tables[$j];
                        if (is_string($tableOther) && $tableOther === $tableName) {
                            unset($this->tables[$j]);
                        }
                    }
                }
            } elseif (is_string($table)) {
                $numTables = count($this->tables);

                for ($j = $index + 1; $j < $numTables; $j++) {
                    if (isset($this->tables[$j]) && is_array($this->tables[$j]) && !isset($this->tables[$j]['tableAlias'])) {
                        $tableOther = $this->tables[$j];
                        if ($table === $tableOther['table']) {
                            $message = sprintf('Please reorganize the joined tables as the table %s in %s cannot be joined correctly. We recommend to join tables with arrays first. %s', $table, json_encode($this->tables), json_encode(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10)));
                            throw new Exception($message);
                        }
                    }

                }
            }
        }
    }

    private function addMissingTablesForOtherTableJoin($tableName, $dependentTable)
    {
        $this->tables->addTableDependency($dependentTable, $tableName);

        if ($this->tables->hasJoinedTable($tableName)) {
            return;
        }

        $table = $this->tables->getLogTable($tableName);

        if ($table->getColumnToJoinOnIdAction() || $table->getColumnToJoinOnIdVisit() || $table->getLinkTableToBeAbleToJoinOnVisit()) {
            $this->tables->addTableToJoin($tableName);
            return;
        }

        $otherTableJoins = $table->getWaysToJoinToOtherLogTables();

        foreach ($otherTableJoins as $logTable => $column) {
            $this->addMissingTablesForOtherTableJoin($logTable, $tableName);
        }

        $this->tables->addTableToJoin($tableName);
    }

    /**
     * Generate the join sql based on the needed tables
     * @throws Exception if tables can't be joined
     * @return array
     */
    public function generate()
    {
        /** @var LogTable[] $availableLogTables */
        $availableLogTables = array();

        $this->tables->sort();

        foreach ($this->tables as $i => $table) {
            if (is_array($table)) {

                // join condition provided
                $alias = isset($table['tableAlias']) ? $table['tableAlias'] : $table['table'];

                if (isset($table['join'])) {
                    $this->joinString .= ' ' . $table['join'];
                } else {
                    $this->joinString .= ' LEFT JOIN';
                }

                if (!isset($table['joinOn']) && $this->tables->getLogTable($table['table'])) {
                    $logTable = $this->tables->getLogTable($table['table']);
                    if (!empty($availableLogTables)) {
                        $table['joinOn'] = $this->findJoinCriteriasForTables($logTable, $availableLogTables);
                    }
                    if (!isset($table['tableAlias'])) {
                        // eg array('table' => 'log_link_visit_action', 'join' => 'RIGHT JOIN')
                        // we treat this like a regular string table which we can join automatically
                        $availableLogTables[$table['table']] = $logTable;
                    }
                }

                $this->joinString .= ' ' . Common::prefixTable($table['table']) . " AS " . $alias
                                   . " ON " . $table['joinOn'];
                continue;
            }

            $tableSql = Common::prefixTable($table) . " AS $table";

            $logTable = $this->tables->getLogTable($table);

            if ($i == 0) {
                // first table
                $this->joinString .= $tableSql;
            } else {

                $join = $this->findJoinCriteriasForTables($logTable, $availableLogTables);

                if ($join === null) {
                    $availableLogTables[$table] = $logTable;
                    continue;
                }

                $joinName = 'LEFT JOIN';
                if ($i > 0
                    && $this->tables[$i - 1]
                    && is_string($this->tables[$i - 1])
                    && strpos($this->tables[$i - 1], LogAggregator::LOG_TABLE_SEGMENT_TEMPORARY_PREFIX) === 0) {
                    $joinName = 'INNER JOIN';
                    // when we archive a segment there will be eg `logtmpsegment$HASH` as first table.
                    // then we join log_conversion for example... if we didn't use INNER JOIN we would as a result
                    // get rows for visits even when they didn't have a conversion. Instead we only want to find rows
                    // that have an entry in both tables when doing eg
                    // logtmpsegment57cd546b7203d68a41027547c4abe1a2.idvisit = log_conversion.idvisit
                }
                // the join sql the default way
                $this->joinString .= " $joinName $tableSql ON " . $join;
            }

            $availableLogTables[$table] = $logTable;
        }
    }

    public function getJoinString()
    {
        return $this->joinString;
    }

    public function shouldJoinWithSelect()
    {
        return $this->joinWithSubSelect;
    }

    /**
     * @param LogTable $logTable
     * @param LogTable[] $availableLogTables
     * @return string|null   returns null in case the table is already joined, or the join string if the table needs
     *                       to be joined
     * @throws Exception if table cannot be joined for segmentation
     */
    public function findJoinCriteriasForTables(LogTable $logTable, $availableLogTables)
    {
        $join = null;
        $alternativeJoin = null;
        $table = $logTable->getName();

        foreach ($availableLogTables as $availableLogTable) {
            if ($logTable->getColumnToJoinOnIdVisit() && $availableLogTable->getColumnToJoinOnIdVisit()) {

                $join = sprintf("%s.%s = %s.%s", $table, $logTable->getColumnToJoinOnIdVisit(),
                                                 $availableLogTable->getName(), $availableLogTable->getColumnToJoinOnIdVisit());
                $alternativeJoin = sprintf("%s.%s = %s.%s", $availableLogTable->getName(), $availableLogTable->getColumnToJoinOnIdVisit(),
                                                            $table, $logTable->getColumnToJoinOnIdVisit());

                if ($availableLogTable->shouldJoinWithSubSelect()) {
                    $this->joinWithSubSelect = true;
                }

                break;
            }

            if ($logTable->getColumnToJoinOnIdAction() && $availableLogTable->getColumnToJoinOnIdAction()) {
                if (isset($this->nonVisitJoins[$logTable->getName()][$availableLogTable->getName()])) {
                    $join = $this->nonVisitJoins[$logTable->getName()][$availableLogTable->getName()];
                }

                break;
            }

            $otherJoins = $logTable->getWaysToJoinToOtherLogTables();
            foreach ($otherJoins as $joinTable => $column) {
                if($availableLogTable->getName() == $joinTable) {
                    $join = sprintf("`%s`.`%s` = `%s`.`%s`", $table, $column, $availableLogTable->getName(), $column);
                    break;
                }
            }

            $otherJoins = $availableLogTable->getWaysToJoinToOtherLogTables();
            foreach ($otherJoins as $joinTable => $column) {
                if ($table == $joinTable) {
                    $join = sprintf("`%s`.`%s` = `%s`.`%s`", $table, $column, $availableLogTable->getName(), $column);
                    break;
                }
            }
        }

        if (!isset($join)) {
            throw new Exception("Table '$table' can't be joined for segmentation");
        }

        if ($this->tables->hasJoinedTableManually($table, $join)
            || $this->tables->hasJoinedTableManually($table, $alternativeJoin)) {
            // already joined, no need to join it again
            return null;
        }

        if ($table == 'log_conversion_item') { // by default we don't want to consider deleted columns
            $join .= sprintf(' AND `%s`.deleted = 0', $table);
        }

        return $join;
    }

    /**
     * This code is a bit tricky. We have to execute this right at the beginning before actually iterating over all the
     * tables and generating the join string as we may have to delete a table from the tables. If we did not delete
     * this table upfront, we would have maybe already added a joinString for that table, even though it will be later
     * removed by another table. This means if we wouldn't delete/unset that table upfront, we would need to alter
     * an already generated join string which would not be really nice code as well.
     *
     * Next problem is, because we are deleting a table, we have to remember the "joinOn" string for that table in a
     * property "nonVisitJoins". Otherwise we would not be able to generate the correct "joinOn" string when actually
     * iterating over all the tables to generate that string.
     *
     * @param $tableName
     * @param $tableNameToJoin
     * @param $index
     */
    protected function generateNonVisitJoins($tableName, $tableNameToJoin, $index)
    {
        $logTable = $this->tables->getLogTable($tableName);
        $logTableToJoin = $this->tables->getLogTable($tableNameToJoin);

        $nonVisitJoin = sprintf("%s.%s = %s.%s", $logTableToJoin->getName(), $logTableToJoin->getColumnToJoinOnIdAction(),
                                                 $tableName, $logTable->getColumnToJoinOnIdAction());

        $altNonVisitJoin = sprintf("%s.%s = %s.%s", $tableName, $logTable->getColumnToJoinOnIdAction(),
                                                    $logTableToJoin->getName(), $logTableToJoin->getColumnToJoinOnIdAction());

        if ($index > 0
            && $this->tables->hasAddedTableManually($tableName)
            && !$this->tables->hasJoinedTableManually($tableName, $nonVisitJoin)
            && !$this->tables->hasJoinedTableManually($tableName, $altNonVisitJoin)) {
            $tableIndex = $this->tables->findIndexOfManuallyAddedTable($tableName);
            $nonVisitJoin = '(' . $this->tables[$tableIndex]['joinOn'] . ' AND ' . $nonVisitJoin . ')';
            unset($this->tables[$tableIndex]);
        }

        if (!isset($this->nonVisitJoins[$tableName])) {
            $this->nonVisitJoins[$tableName] = array();
        }

        if (!isset($this->nonVisitJoins[$tableNameToJoin])) {
            $this->nonVisitJoins[$tableNameToJoin] = array();
        }

        $this->nonVisitJoins[$tableName][$tableNameToJoin] = $nonVisitJoin;
        $this->nonVisitJoins[$tableNameToJoin][$tableName] = $nonVisitJoin;
    }
}
