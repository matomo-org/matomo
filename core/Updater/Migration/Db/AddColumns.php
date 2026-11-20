<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Updater\Migration\Db;

use Piwik\DataAccess\TableMetadata;
use Piwik\Db;
use Piwik\Db\Schema;

/**
 * @see Factory::addColumns()
 * @ignore
 */
class AddColumns extends Sql
{
    /**
     * @var string[]
     */
    private $statements;

    public function __construct($table, $columns, $placeColumnAfter)
    {
        $tableMetadata = new TableMetadata();
        try {
            $existingColumns = $tableMetadata->getColumns($table);
        } catch (\Exception $ex) {
            $existingColumns = [];
        }

        $changes = array();
        foreach ($columns as $columnName => $columnType) {
            if (in_array($columnName, $existingColumns)) {
                continue;
            }

            $part = sprintf("ADD COLUMN `%s` %s", $columnName, $columnType);

            if (!empty($placeColumnAfter)) {
                $part .= sprintf(' AFTER `%s`', $placeColumnAfter);
                $placeColumnAfter = $columnName;
            }

            $changes[] = $part;
        }

        $this->statements = Schema::getInstance()->getAlterTableSql($table, $changes);

        parent::__construct(implode(';', $this->statements), static::ERROR_CODE_DUPLICATE_COLUMN);
    }

    /**
     * Runs one statement per column where the schema needs several, so a column that already exists
     * doesn't stop the columns after it from being added.
     */
    public function exec()
    {
        if (count($this->statements) < 2) {
            parent::exec();
            return;
        }

        foreach ($this->statements as $statement) {
            try {
                Db::exec($statement);
            } catch (\Exception $e) {
                if (!$this->shouldIgnoreError($e)) {
                    throw $e;
                }
            }
        }
    }
}
