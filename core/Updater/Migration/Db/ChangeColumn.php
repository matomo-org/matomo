<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Updater\Migration\Db;

/**
 * @see Factory::changeColumn()
 * @ignore
 */
class ChangeColumn extends AlterTable
{
    public function __construct($table, $oldColumnName, $newColumnName, $columnType)
    {
        $specification = sprintf("CHANGE `%s` `%s` %s", $oldColumnName, $newColumnName, $columnType);
        $sql = sprintf("ALTER TABLE `%s` %s", $table, $specification);
        parent::__construct($table, [$specification], array(static::ERROR_CODE_DUPLICATE_COLUMN,
                                        static::ERROR_CODE_UNKNOWN_COLUMN), $sql);
    }
}
