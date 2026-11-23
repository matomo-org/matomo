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

/**
 * Creates a table in the dialect of the connection in use when the migration runs or is shown.
 *
 * @see Factory::createTable()
 * @ignore
 */
class CreateTable extends Statements
{
    /**
     * @var string
     */
    private $table;

    /**
     * @var string
     */
    private $createDefinition;

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

        $this->table = $table;
        $this->createDefinition = implode(', ', $columns);

        parent::__construct([], static::ERROR_CODE_TABLE_EXISTS);
    }

    public function exec()
    {
        if (!Schema::getInstance()->getConnectionSchema(true) instanceof Schema\Sqlite) {
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

    protected function getStatements(): array
    {
        $schema = Schema::getInstance()->getConnectionSchema();
        if ($schema instanceof Schema\Sqlite) {
            return $schema->getCreateTableSql($this->table, $this->createDefinition);
        }

        return [sprintf('CREATE TABLE `%s` (%s) %s', $this->table, $this->createDefinition, $schema->getTableCreateOptions())];
    }
}
