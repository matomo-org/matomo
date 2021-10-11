<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Updater\Migration\Db;

use Piwik\Db;

/**
 * @see Factory::createTable()
 * @ignore
 */
class CreateTable extends Sql
{
    /**
     * Constructor.
     * @param Db\Settings $dbSettings
     * @param string $table Prefixed table name
     * @param string|string[] $columnNames array(columnName => columnValue)
     * @param string|string[] $primaryKey one or multiple columns that define the primary key
     */
    public function __construct(Db\Settings $dbSettings, $table, $columnNames, $primaryKey)
    {
        $columns = array();
        foreach ($columnNames as $column => $type) {
            $columns[] = sprintf('`%s` %s', $column, $type);
        }

        if (!empty($primaryKey)) {
            $columns[] = sprintf('PRIMARY KEY ( `%s` )', implode('`, `', $primaryKey));
        }


        $sql = rtrim(sprintf('CREATE TABLE `%s` (%s) ENGINE=%s DEFAULT CHARSET=%s %s',
          $table, implode(', ', $columns), $dbSettings->getEngine(), $dbSettings->getUsedCharset(), $dbSettings->getRowFormat()));

        parent::__construct($sql, static::ERROR_CODE_TABLE_EXISTS);
    }

}
