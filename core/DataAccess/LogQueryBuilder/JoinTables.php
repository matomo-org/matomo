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
        return in_array($tableName, $this->getTables());
    }

    public function hasJoinedTableManually($tableToFind, $joinToFind)
    {
        foreach ($this as $table) {
            if (is_array($table)
                && !empty($table['table'])
                && $table['table'] === $tableToFind
                && (!isset($table['tableAlias']) || $table['tableAlias'] === $tableToFind)
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

        // we need to make sure first table always comes first, only sort tables after the first table
        $firstTable = array_shift($tables);
        usort($tables, $cmpFunction);
        array_unshift($tables, $firstTable);

        $this->exchangeArray($tables);
    }

    private function checkTableCanBeUsedForSegmentation($tableName)
    {
        if (!is_array($tableName) && !$this->getLogTable($tableName)) {
            throw new Exception("Table '$tableName' can't be used for segmentation");
        }
    }

    
}
