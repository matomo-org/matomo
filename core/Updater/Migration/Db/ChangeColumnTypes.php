<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Updater\Migration\Db;

/**
 * @see Factory::changeColumnTypes()
 * @ignore
 */
class ChangeColumnTypes extends Sql
{
    public function __construct($table, $columns)
    {
        $changes = array();
        foreach ($columns as $columnName => $columnType) {
            $changes[] = sprintf("CHANGE `%s` `%s` %s", $columnName, $columnName, $columnType);
        }

        $sql = sprintf("ALTER TABLE `%s` %s", $table, implode(', ', $changes));

        parent::__construct($sql, static::ERROR_CODE_UNKNOWN_COLUMN);
    }

}
