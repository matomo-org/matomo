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
 * Changes a table with MySQL `ALTER TABLE` specifications, in the dialect of the connection in use
 * when the migration runs or is shown.
 *
 * On MySQL and MariaDB, it runs the configured schema's statements (one per specification where the
 * server lacks complex column updates), or the statement given. On SQLite, `Schema::alterTable()`
 * runs the specifications, reading the table then, so an earlier migration of the same update is
 * seen; changing a column or the primary key rebuilds the table. Should an error to ignore stop them,
 * each specification is tried on its own, as the separate statements MySQL runs without complex column
 * updates would be.
 *
 * @ignore
 */
class AlterTable extends Statements
{
    /**
     * @var string
     */
    private $tableName;

    /**
     * @var string[]
     */
    private $specifications;

    /**
     * @var string|null
     */
    private $mysqlStatement;

    /**
     * @param string $tableName the prefixed table name
     * @param string[] $specifications e.g. `["ADD COLUMN `name` VARCHAR(255) NOT NULL"]`
     * @param int|int[] $errorCodesToIgnore
     * @param string|null $sql the statement MySQL and MariaDB run, instead of the one built from the specifications
     */
    public function __construct(string $tableName, array $specifications, $errorCodesToIgnore, ?string $sql = null)
    {
        $this->tableName = $tableName;
        $this->specifications = array_values($specifications);
        $this->mysqlStatement = $sql;

        parent::__construct([], $errorCodesToIgnore);
    }

    public function exec()
    {
        $schema = Schema::getInstance()->getConnectionSchema(true);
        if (!$schema instanceof Schema\Sqlite) {
            parent::exec();
            return;
        }

        $specifications = $this->getSpecifications();
        if ($specifications === []) {
            return;
        }

        try {
            // all or nothing, as one MySQL statement
            $schema->alterTable($this->tableName, $specifications);
        } catch (\Exception $e) {
            if (count($specifications) < 2 || !$this->shouldIgnoreError($e)) {
                throw $e;
            }
            // the others still all or nothing: a later error that isn't ignored undoes them
            Db::inSqliteSavepoint(function () use ($schema, $specifications) {
                foreach ($specifications as $specification) {
                    try {
                        $schema->alterTable($this->tableName, [$specification]);
                    } catch (\Exception $e) {
                        if (!$this->shouldIgnoreError($e)) {
                            throw $e;
                        }
                    }
                }
            });
        }
    }

    /**
     * The specifications to run, read when the migration runs or is shown.
     *
     * @return string[]
     */
    protected function getSpecifications(): array
    {
        return $this->specifications;
    }

    protected function getTableName(): string
    {
        return $this->tableName;
    }

    protected function getStatements(): array
    {
        // first: reading the specifications of AddColumns or DropColumns opens the connection, whose engine
        // decides the SQL
        $this->getSpecifications();
        $schema = Schema::getInstance()->getConnectionSchema();
        if ($schema instanceof Schema\Sqlite) {
            return $this->getSqliteStatements($schema);
        }

        if ($this->mysqlStatement !== null) {
            return [$this->mysqlStatement];
        }

        if ($schema instanceof Schema\Mysql) {
            return $schema->getAlterTableSql($this->tableName, $this->getSpecifications());
        }

        return Schema::getInstance()->getAlterTableSql($this->tableName, $this->getSpecifications());
    }

    /**
     * What SQLite runs, as far as it can be told before it runs: a rebuild is described in a comment.
     *
     * @return string[]
     */
    private function getSqliteStatements(Schema\Sqlite $schema): array
    {
        $converted = [];
        $statements = [];
        foreach ($this->getSpecifications() as $specification) {
            if ($schema->needsRebuild($specification)) {
                $statements[] = sprintf('/* SQLite rebuilds the table %s for: %s */', $this->tableName, str_replace('*/', '* /', $specification));
            } else {
                $converted[] = $specification;
            }
        }

        // an index takes its columns' collation from the table: one yet to be created is described
        $indexes = preg_grep('/^\s*ADD\s+(?:(?:UNIQUE\s+)?(?:INDEX|KEY)\b|UNIQUE\b)/i', $converted);
        if ($indexes !== [] && !$this->tableExistsOn($schema)) {
            $statements[] = sprintf('/* SQLite: ALTER TABLE `%s` %s */', $this->tableName, str_replace('*/', '* /', implode(', ', $converted)));
            $converted = [];
        }
        if ($converted !== []) {
            try {
                $statements = array_merge($schema->getAlterTableSql($this->tableName, $converted), $statements);
            } catch (\Exception $e) {
                // e.g. the table is created by an earlier migration of the update
                $statements[] = sprintf('/* SQLite: ALTER TABLE `%s` %s */', $this->tableName, str_replace('*/', '* /', implode(', ', $converted)));
            }
        }

        return $statements;
    }

    private function tableExistsOn(Schema\Sqlite $schema): bool
    {
        try {
            $schema->getTableColumns(str_replace('`', '', $this->tableName));
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
