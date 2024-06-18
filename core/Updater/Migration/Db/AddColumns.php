<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Updater\Migration\Db;

use Piwik\DataAccess\TableMetadata;
use Piwik\Db\Schema;

/**
 * @see Factory::addColumns()
 * @ignore
 */
class AddColumns extends Sql
{
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

        if (Schema::getInstance()->supportsComplexColumnUpdates()) {
            $sql = sprintf("ALTER TABLE `%s` %s", $table, implode(', ', $changes));

            parent::__construct($sql, static::ERROR_CODE_DUPLICATE_COLUMN);
        } else {
            $queriesToPerform = [];

            foreach ($changes as $change) {
                $queriesToPerform[] = sprintf("ALTER TABLE `%s` %s", $table, $change);
            }

            parent::__construct(implode(';', $queriesToPerform), static::ERROR_CODE_DUPLICATE_COLUMN);
        }
    }
}
