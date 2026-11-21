<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Updater\Migration\Db;

use Piwik\Db\Schema;

/**
 * @see Factory::addColumns()
 * @ignore
 */
class AddColumns extends AlterTable
{
    public function __construct($table, $columns, $placeColumnAfter)
    {
        try {
            // read on the connection in use, whatever the configuration file says
            $existingColumns = array_keys(Schema::getInstance()->getConnectionSchema()->getTableColumns(str_replace('`', '', $table)));
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

        parent::__construct($table, $changes, static::ERROR_CODE_DUPLICATE_COLUMN);
    }
}
