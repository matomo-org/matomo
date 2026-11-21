<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Updater\Migration\Db;

use Piwik\Db;
use Piwik\Db\Schema;
use Piwik\Db\Sqlite\Connection;

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

        // the connection in use decides, whatever the configuration file says
        if (Connection::isSqliteConnection(Db::get())) {
            $statements = (new Schema\Sqlite())->getCreateTableSql($table, implode(', ', $columns));
        } else {
            $statements = [sprintf('CREATE TABLE `%s` (%s) %s', $table, implode(', ', $columns), Schema::getInstance()->getTableCreateOptions())];
        }

        parent::__construct($statements, static::ERROR_CODE_TABLE_EXISTS);
    }

    public function exec()
    {
        if (!Connection::isSqliteConnection(Db::get())) {
            parent::exec();
            return;
        }

        // all or nothing, as one MySQL statement: an existing table fails the CREATE TABLE, and its
        // indexes aren't created (the updater ignores the error)
        Db::inSqliteSavepoint(function () {
            foreach ($this->getStatements() as $statement) {
                Db::exec($statement);
            }
        });
    }
}
