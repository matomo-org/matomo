<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Updater\Migration\Db;
use Piwik\Common;

/**
 * @see Factory::insert()
 * @ignore
 */
class Insert extends BoundSql
{
    /**
     * Insert constructor.
     * @param string $table
     * @param array $columnValuePairs array(columnName => columnValue)
     */
    public function __construct($table, $columnValuePairs)
    {
        $columns = implode('`, `', array_keys($columnValuePairs));
        $bind = array_values($columnValuePairs);

        $sql = sprintf('INSERT INTO `%s` (`%s`) VALUES (%s)', $table, $columns, Common::getSqlStringFieldsArray($columnValuePairs));

        parent::__construct($sql, $bind, static::ERROR_CODE_DUPLICATE_ENTRY);
    }

}
