<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Updater\Migration\Db;

/**
 * @see Factory::addColumn()
 * @ignore
 */
class AddColumn extends Sql
{
    public function __construct($table, $columnName, $columnType, $placeColumnAfter)
    {
        $sql = sprintf("ALTER TABLE `%s` ADD COLUMN `%s` %s", $table, $columnName, $columnType);

        if (!empty($placeColumnAfter)) {
            $sql .= sprintf(' AFTER `%s`', $placeColumnAfter);
        }

        parent::__construct($sql, static::ERROR_CODE_DUPLICATE_COLUMN);
    }
}
