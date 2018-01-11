<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Updater\Migration\Db;

/**
 * @see Factory::changeColumnType()
 * @ignore
 */
class ChangeColumnType extends ChangeColumn
{
    public function __construct($table, $columnName, $columnType)
    {
        parent::__construct($table, $columnName, $columnName, $columnType);
    }

}
