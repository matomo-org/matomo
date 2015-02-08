<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\DataAccess;

use Piwik\Common;
use Piwik\Db;

/**
 * TODO
 */
class TableMetadata
{
    /**
     * TODO
     */
    public function getColumns($table)
    {
        $table = str_replace("`", "", $table);

        $columns = Db::fetchAll("SHOW COLUMNS FROM `" . $table . "`");

        $columnNames = array();
        foreach ($columns as $column) {
            $columnNames[] = $column['Field'];
        }

        return $columnNames;
    }

    /**
     * TODO
     */
    public function getIdActionColumnNames($table)
    {
        $columns = $this->getColumns($table);

        $columns = array_filter($columns, function ($columnName) {
            return strpos($columnName, 'idaction') !== false;
        });

        return array_values($columns);
    }
}