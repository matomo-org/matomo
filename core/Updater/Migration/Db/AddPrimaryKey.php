<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Updater\Migration\Db;

/**
 * @see Factory::addPrimaryKey()
 * @ignore
 */
class AddPrimaryKey extends Sql
{
    /**
     * AddPrimaryKey constructor.
     * @param string $table
     * @param array $columnNames
     */
    public function __construct($table, $columnNames)
    {
        $sql = sprintf("ALTER TABLE `%s` ADD PRIMARY KEY(`%s`)", $table, implode('`, `', $columnNames));

        parent::__construct($sql, array(static::ERROR_CODE_DUPLICATE_KEY, static::ERROR_CODE_DUPLICATE_PRIMARY_KEY, static::ERROR_CODE_KEY_COLUMN_NOT_EXISTS));
    }

}
