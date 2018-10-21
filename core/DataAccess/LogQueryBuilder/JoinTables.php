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
use Piwik\Plugin\LogTablesProvider;

class JoinTables extends \ArrayObject
{
    /**
     * @var LogTablesProvider
     */
    private $logTableProvider;

    /**
     * Tables constructor.
     * @param LogTablesProvider $logTablesProvider
     * @param array $tables
     */
    public function __construct(LogTablesProvider $logTablesProvider, $tables)
    {
        $this->logTableProvider = $logTablesProvider;

        foreach ($tables as $table) {
            $this->checkTableCanBeUsedForSegmentation($table);
        }

        $this->exchangeArray(array_values($tables));
    }

    public function getTables()
    {
        return $this->getArrayCopy();
    }

    public function addTableToJoin($tableName)
    {
        $this->checkTableCanBeUsedForSegmentation($tableName);
        $this->append($tableName);
    }

    public function hasJoinedTable($tableName)
    {
        $tables = in_array($tableName, $this->getTables());
        if ($tables) {
            return true;
        }

        foreach ($this as $table) {
            if (is_array($table)) {
                if (!isset($table['tableAlias']) && $table['table'] === $table) {
                    return true;
                } elseif (isset($table['tableAlias']) && $table['tableAlias'] === $table) {
                    return true;
                }
            }
        }

        return false;
    }

    public function hasJoinedTableManually($tableToFind, $joinToFind)
    {
        foreach ($this as $table) {
            if (is_array($table)
                && !empty($table['table'])
                && $table['table'] === $tableToFind
                && (!isset($table['tableAlias']) || $table['tableAlias'] === $tableToFind)
                && (!isset($table['join']) || strtolower($table['join']) === 'left join')
                && isset($table['joinOn']) && $table['joinOn'] === $joinToFind) {
                return true;
            }
        }

        return false;
    }

    public function getLogTable($tableName)
    {
        return $this->logTableProvider->getLogTable($tableName);
    }

    public function findIndexOfManuallyAddedTable($tableNameToFind)
    {
        foreach ($this as $index => $table) {
            if (is_array($table)
                && !empty($table['table'])
                && $table['table'] === $tableNameToFind
                && (!isset($table['join']) || strtolower($table['join']) === 'left join')
                && (!isset($table['tableAlias']) || $table['tableAlias'] === $tableNameToFind)) {
                return $index;
            }
        }
    }

    public function hasAddedTableManually($tableToFind)
    {
        $table = $this->findIndexOfManuallyAddedTable($tableToFind);

        return isset($table);
    }

    public function sort()
    {
        // we do not use $this->uasort as we do not want to maintain keys
        $tables = $this->getTables();

        // the first entry is always the FROM table
        $firstTable = array_shift($tables);

        $dependencies = $this->parseDependencies($tables);

        $sorted = [$firstTable];
        $this->visitTableListDfs($tables, $dependencies, function ($tableInfo) use (&$sorted) {
            $sorted[] = $tableInfo;
        });

        $this->exchangeArray($sorted);
    }

    private function checkTableCanBeUsedForSegmentation($tableName)
    {
        if (!is_array($tableName) && !$this->getLogTable($tableName)) {
            throw new Exception("Table '$tableName' can't be used for segmentation");
        }
    }

    private function parseDependencies(array $tables)
    {
        // NOTE: joins can be specified explicitly as arrays w/ 'joinOn' keys or implicitly as table names. when
        // table names are used, the joins dependencies are assumed based on how we want to order those joins.
        // the below table list the possible dependencies of each table, and is specifically designed to enforce
        // the following order:
        // log_link_visit_action, log_action, log_visit, log_conversion, log_conversion_item
        // which means if an array is supplied where log_visit comes before log_link_visitAction, it will
        // be moved to after it.
        static $implicitTableDependencies = [
            'log_link_visit_action' => [
                // empty
            ],
            'log_action' => [
                'log_link_visit_action',
                'log_conversion',
                'log_conversion_item',
                'log_visit',
            ],
            'log_visit' => [
                'log_link_visit_action',
                'log_action',
            ],
            'log_conversion' => [
                'log_link_visit_action',
                'log_action',
                'log_visit',
            ],
            'log_conversion_item' => [
                'log_link_visit_action',
                'log_action',
                'log_visit',
                'log_conversion',
            ],
        ];

        $dependencies = [];
        foreach ($tables as $key => &$fromInfo) {
            if (is_string($fromInfo)) {
                if (isset($implicitTableDependencies[$fromInfo])) {
                    $dependencies[$key] = array_filter($implicitTableDependencies[$fromInfo], function ($table) use ($tables) {
                        return $this->isInTableArray($tables, $table);
                    });
                }

                continue;
            }

            $table = isset($fromInfo['tableAlias']) ? $fromInfo['tableAlias'] : $fromInfo['table'];
            if (empty($fromInfo['joinOn'])) {
                continue;
            }

            $tablesInExpr = $this->parseSqlTables($fromInfo['joinOn'], $table);
            $dependencies[$key] = $tablesInExpr;
        }
        return $dependencies;
    }

    private function isInTableArray($tables, $table)
    {
        foreach ($tables as $entry) {
            if (is_string($entry)
                && $entry == $table
            ) {
                return true;
            }

            if (is_array($entry)
                && $entry['table'] == $table
            ) {
                return true;
            }
        }
        return false;
    }

    private function parseSqlTables($joinOn, $self)
    {
        preg_match_all('/\b([a-zA-Z0-9_`]+)\.[a-zA-Z0-9_`]+\b/', $joinOn, $matches);

        $tables = [];
        foreach ($matches[1] as $table) {
            if ($table === $self) {
                continue;
            }

            $tables[] = $table;
        }
        return $tables;
    }

    private function visitTableListDfs($tables, $dependencies, $visitor)
    {
        $visited = [];
        foreach ($tables as $index => $tableInfo) {
            if (empty($visited[$index])) {
                $this->visitTableListDfsSingle($tables, $dependencies, $visitor, $index, $visited);
            }
        }
    }

    private function visitTableListDfsSingle($tables, $dependencies, $visitor, $tableToVisitIndex, &$visited)
    {
        if ($tableToVisitIndex === null) {
            $tableToVisitIndex = 0;
        }

        $visited[$tableToVisitIndex] = true;
        $tableToVisit = $tables[$tableToVisitIndex];

        if (!empty($dependencies[$tableToVisitIndex])) {
            foreach ($dependencies[$tableToVisitIndex] as $dependencyTableName) {
                $dependentTableToVisit = $this->findTableInfo($tables, $dependencyTableName);
                if ($dependentTableToVisit === null) {
                    continue;
                }

                if (empty($visited[$dependentTableToVisit])) {
                    $this->visitTableListDfsSingle($tables, $dependencies, $visitor, $dependentTableToVisit, $visited);
                }
            }
        }

        $visitor($tableToVisit);
    }

    private function findTableInfo($tables, $dependencyTableName)
    {
        foreach ($tables as $key => $info) {
            $tableName = null;
            if (is_string($info)) {
                $tableName = $info;
            } else if (is_array($info)) {
                $tableName = isset($info['tableAlias']) ? $info['tableAlias'] : $info['table'];
            }

            if ($tableName == $dependencyTableName) {
                return $key;
            }
        }
        return null;
    }
}
