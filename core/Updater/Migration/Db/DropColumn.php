<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Updater\Migration\Db;

/**
 * @see Factory::dropColumn()
 * @ignore
 */
class DropColumn extends AlterTable
{
    public function __construct($table, $columnName)
    {
        $specification = sprintf("DROP COLUMN `%s`", $columnName);

        parent::__construct($table, [$specification], static::ERROR_CODE_COLUMN_NOT_EXISTS);
    }
}
