<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Updater\Migration\Db;

/**
 * @see Factory::changeColumn()
 * @ignore
 */
class ChangeColumn extends Sql
{
    public function __construct($table, $oldColumnName, $newColumnName, $columnType)
    {
        $sql = sprintf("ALTER TABLE `%s` CHANGE `%s` `%s` %s", $table, $oldColumnName, $newColumnName, $columnType);
        parent::__construct($sql, array(static::ERROR_CODE_DUPLICATE_COLUMN,
                                        static::ERROR_CODE_UNKNOWN_COLUMN));
    }

}
