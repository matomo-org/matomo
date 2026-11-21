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
        $schema = Schema::getInstance()->getConnectionSchema();
        if (!$schema instanceof Schema\Sqlite) {
            parent::exec();
            return;
        }

        if ($this->specifications === []) {
            return;
        }

        try {
            // all or nothing, as one MySQL statement
            $schema->alterTable($this->tableName, $this->specifications);
        } catch (\Exception $e) {
            if (count($this->specifications) < 2 || !$this->shouldIgnoreError($e)) {
                throw $e;
            }
            foreach ($this->specifications as $specification) {
                try {
                    $schema->alterTable($this->tableName, [$specification]);
                } catch (\Exception $e) {
                    if (!$this->shouldIgnoreError($e)) {
                        throw $e;
                    }
                }
            }
        }
    }

    protected function getStatements(): array
    {
        $schema = Schema::getInstance()->getConnectionSchema();
        if ($schema instanceof Schema\Sqlite) {
            return $this->getSqliteStatements($schema);
        }

        if ($this->mysqlStatement !== null) {
            return [$this->mysqlStatement];
        }

        if ($schema instanceof Schema\Mysql) {
            return $schema->getAlterTableSql($this->tableName, $this->specifications);
        }

        return Schema::getInstance()->getAlterTableSql($this->tableName, $this->specifications);
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
        foreach ($this->specifications as $specification) {
            if ($schema->needsRebuild($specification)) {
                $statements[] = sprintf('/* SQLite rebuilds the table %s for: %s */', $this->tableName, str_replace('*/', '* /', $specification));
            } else {
                $converted[] = $specification;
            }
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
}
