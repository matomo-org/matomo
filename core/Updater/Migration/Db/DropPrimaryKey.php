<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Updater\Migration\Db;

/**
 * @see Factory::dropPrimaryKey()
 * @ignore
 */
class DropPrimaryKey extends AlterTable
{
    /**
     * @param string $table Prefixed table name
     */
    public function __construct($table)
    {
        $sql = sprintf('ALTER TABLE `%s` DROP PRIMARY KEY', $table);

        parent::__construct($table, ['DROP PRIMARY KEY'], array(static::ERROR_CODE_COLUMN_NOT_EXISTS), $sql);
    }
}
