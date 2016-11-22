<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Updater\Migration\Db;

/**
 * @see Factory::dropColumn()
 * @ignore
 */
class DropColumn extends Sql
{
    public function __construct($table, $columnName)
    {
        $sql = sprintf("ALTER TABLE `%s` DROP COLUMN `%s`", $table, $columnName);

        if (!empty($placeColumnAfter)) {
            $sql .= sprintf(' AFTER `%s`', $placeColumnAfter);
        }

        parent::__construct($sql, static::ERROR_CODE_COLUMN_NOT_EXISTS);
    }

}
