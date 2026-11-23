<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Updater\Migration\Db;

/**
 * @see Factory::addColumn()
 * @ignore
 */
class AddColumn extends AlterTable
{
    public function __construct($table, $columnName, $columnType, $placeColumnAfter)
    {
        $specification = sprintf("ADD COLUMN `%s` %s", $columnName, $columnType);

        if (!empty($placeColumnAfter)) {
            $specification .= sprintf(' AFTER `%s`', $placeColumnAfter);
        }

        parent::__construct($table, [$specification], static::ERROR_CODE_DUPLICATE_COLUMN);
    }
}
