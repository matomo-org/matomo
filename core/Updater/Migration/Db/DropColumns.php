<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Updater\Migration\Db;

use Piwik\DbHelper;

/**
 * @see Factory::dropColumn()
 * @ignore
 */
class DropColumns extends Sql
{
    public function __construct($tableName, $columnNames)
    {
        $table = DbHelper::getTableColumns($tableName);

        // we need to remove all not existing columns. Otherwise if only one of the columns doesn't exist, all of
        // the columns wouldn't be removed
        $columnNames = array_filter($columnNames, function ($columnName) use ($table) {
            return isset($table[$columnName]);
        });

        if (empty($columnNames)) {
            parent::__construct('', static::ERROR_CODE_COLUMN_NOT_EXISTS);
        } else {
            $columnNames = array_unique($columnNames);
            $dropColumns = array_map(function ($columnName) {
                return sprintf('DROP COLUMN `%s`', $columnName);
            }, $columnNames);

            $sql = sprintf("ALTER TABLE `%s` %s", $tableName, implode(', ', $dropColumns));
            parent::__construct($sql, static::ERROR_CODE_COLUMN_NOT_EXISTS);
        }

    }

}
