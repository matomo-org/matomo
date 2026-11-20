<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Updater\Migration\Db;

use Piwik\Db\Schema;

/**
 * @see Factory::createTable()
 * @ignore
 */
class CreateTable extends Statements
{
    /**
     * @param string $table Prefixed table name
     * @param array $columnNames array(columnName => columnType)
     * @param string|string[] $primaryKey one or multiple columns that define the primary key
     */
    public function __construct($table, $columnNames, $primaryKey)
    {
        $columns = array();
        foreach ($columnNames as $column => $type) {
            $columns[] = sprintf('`%s` %s', $column, $type);
        }

        if (!empty($primaryKey)) {
            $columns[] = sprintf('PRIMARY KEY ( `%s` )', implode('`, `', $primaryKey));
        }

        parent::__construct(Schema::getInstance()->getCreateTableSql($table, implode(', ', $columns)), static::ERROR_CODE_TABLE_EXISTS);
    }
}
