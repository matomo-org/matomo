<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Updater\Migration\Db;

/**
 * @see Factory::addPrimaryKey()
 * @ignore
 */
class AddPrimaryKey extends AlterTable
{
    /**
     * AddPrimaryKey constructor.
     * @param string $table
     * @param array $columnNames
     */
    public function __construct($table, $columnNames)
    {
        $specification = sprintf("ADD PRIMARY KEY(`%s`)", implode('`, `', $columnNames));
        $sql = sprintf("ALTER TABLE `%s` %s", $table, $specification);

        parent::__construct($table, [$specification], array(static::ERROR_CODE_DUPLICATE_KEY, static::ERROR_CODE_DUPLICATE_PRIMARY_KEY, static::ERROR_CODE_KEY_COLUMN_NOT_EXISTS), $sql);
    }
}
