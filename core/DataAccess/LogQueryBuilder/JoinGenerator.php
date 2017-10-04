<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\DataAccess\LogQueryBuilder;

use Exception;
use Piwik\Common;
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

    /**
     * Generate the join sql based on the needed tables
     * @throws Exception if tables can't be joined
     * @return array
     */
    public function generate()
    {
        /** @var LogTable[] $availableLogTables */
        $availableLogTables = array();

        $this->tables->sort(array($this, 'sortTablesForJoin'));

        foreach ($this->tables as $i => $table) {
            if (is_array($table)) {

                // join condition provided
                $alias = isset($table['tableAlias']) ? $table['tableAlias'] : $table['table'];

                if (isset($table['join'])) {
                    $this->joinString .= ' ' . $table['join'];
                } else {
                    $this->joinString .= ' LEFT JOIN';
                }

                if (!isset($table['joinOn']) && $this->tables->getLogTable($table['table']) && !empty($availableLogTables)) {
                    $logTable = $this->tables->getLogTable($table['table']);
                    $table['joinOn'] = $this->findJoinCriteriasForTables($logTable, $availableLogTables);
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

                // the join sql the default way
                $this->joinString .= " LEFT JOIN $tableSql ON " . $join;
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
        }

        if (!isset($join)) {
            throw new Exception("Table '$table' can't be joined for segmentation");
        }

        if ($this->tables->hasJoinedTableManually($table, $join)
            || $this->tables->hasJoinedTableManually($table, $alternativeJoin)) {
            // already joined, no need to join it again
            return null;
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

    public function sortTablesForJoin($tA, $tB)
    {
        $coreSort = array(
            'log_link_visit_action' => 0,
            'log_action' => 1,
            'log_visit' => 2,
            'log_conversion' => 3,
            'log_conversion_item' => 4
        );

        if (is_array($tA) && is_array($tB)) {
            $tAName = '';
            if (isset($tA['tableAlias'])) {
                $tAName = $tA['tableAlias'];
            } elseif (isset($tA['table'])) {
                $tAName = $tA['table'];
            }

            $tBName = '';
            if (isset($tB['tableAlias'])) {
                $tBName = $tB['tableAlias'];
            } elseif (isset($tB['table'])) {
                $tBName = $tB['table'];
            }

            if ($tBName && isset($tA['joinOn']) && strpos($tA['joinOn'], $tBName) !== false) {
                return 1;
            }

            if ($tAName && isset($tB['joinOn']) && strpos($tB['joinOn'], $tAName) !== false) {
                return -1;
            }

            return 0;
        }

        if (is_array($tA)) {
            return 1;
        }

        if (is_array($tB)) {
            return -1;
        }

        if (isset($coreSort[$tA])) {
            $weightA = $coreSort[$tA];
        } else {
            $weightA = 999;
        }
        if (isset($coreSort[$tB])) {
            $weightB = $coreSort[$tB];
        } else {
            $weightB = 999;
        }

        if ($weightA === $weightB) {
            return 0;
        }

        if ($weightA > $weightB) {
            return 1;
        }

        return -1;
    }
    
}
