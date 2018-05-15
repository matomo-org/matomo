<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Updater\Migration\Db;

/**
 * @see Factory::addColumns()
 * @ignore
 */
class AddColumns extends Sql
{
    public function __construct($table, $columns, $placeColumnAfter)
    {
        $changes = array();
        foreach ($columns as $columnName => $columnType) {
            $part = sprintf("ADD COLUMN `%s` %s", $columnName, $columnType);

            if (!empty($placeColumnAfter)) {
                $part .= sprintf(' AFTER `%s`', $placeColumnAfter);
                $placeColumnAfter = $columnName;
            }

            $changes[] = $part;
        }

        $sql = sprintf("ALTER TABLE `%s` %s", $table, implode(', ', $changes));

        parent::__construct($sql, static::ERROR_CODE_DUPLICATE_COLUMN);
    }

}
