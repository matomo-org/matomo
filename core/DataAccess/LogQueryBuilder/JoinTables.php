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

    public function sort($cmpFunction)
    {
        // we do not use $this->uasort as we do not want to maintain keys
        $tables = $this->getTables();
        $this->parseDependencies($tables);

        $sorted = [];
        $this->visitTableListDfs($tables, function ($tableInfo) use (&$sorted) {
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

    private function parseDependencies(array &$tables)
    {
        foreach ($tables as &$fromInfo) {
            if (is_string($fromInfo)) {
                continue;
            }

            $table = isset($fromInfo['tableAlias']) ? $fromInfo['tableAlias'] : $fromInfo['table'];
            if (empty($fromInfo['joinOn'])) {
                continue;
            }

            $tablesInExpr = $this->parseSqlTables($fromInfo['joinOn'], $table);
            $fromInfo['dependencies'] = $tablesInExpr;
        }
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

    private function visitTableListDfs($tables, $visitor)
    {
        $visited = [];
        foreach ($tables as $index => $tableInfo) {
            $this->visitTableListDfsSingle($tables, $visitor, $index, $visited);
        }
    }

    private function visitTableListDfsSingle($tables, $visitor, $tableToVisitIndex, &$visited)
    {
        if ($tableToVisitIndex === null) {
            $tableToVisitIndex = 0;
        }

        $visited[$tableToVisitIndex] = true;
        $tableToVisit = $tables[$tableToVisitIndex];

        if (is_array($tableToVisit)
            && !empty($tableToVisit['dependencies'])
        ) {
            foreach ($tableToVisit['dependencies'] as $dependencyTableName) {
                $dependentTableToVisit = $this->findTableInfo($tables, $dependencyTableName);
                if ($dependentTableToVisit === null) {
                    continue;
                }

                if (empty($visited[$tableToVisitIndex])) {
                    $this->visitTableListDfsSingle($tables, $visitor, $dependentTableToVisit, $visited);
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
