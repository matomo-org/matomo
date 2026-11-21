<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Updater\Migration\Db;

use Piwik\Db;
use Piwik\Db\Sqlite\Connection;

/**
 * Executes the statements one change needs, e.g. those `Schema::getAlterTableSql()` returns. An error
 * to ignore skips only the statement that raised it. On SQLite, whose DDL is transactional, several
 * statements run in a savepoint, so any other failure leaves none of them applied; MySQL commits
 * each DDL statement.
 *
 * @ignore
 */
class Statements extends Sql
{
    /**
     * @var string[]
     */
    private $statements;

    /**
     * @param string[] $statements
     * @param int|int[] $errorCodesToIgnore
     */
    public function __construct(array $statements, $errorCodesToIgnore)
    {
        $this->statements = array_values($statements);

        parent::__construct(implode(";\n", $this->statements), $errorCodesToIgnore);
    }

    public function exec()
    {
        if (count($this->statements) < 2) {
            parent::exec();
            return;
        }

        // the connection in use decides, whatever the configuration file says
        $db = Db::get();
        if (!Connection::isSqliteConnection($db)) {
            $this->execStatements();
            return;
        }

        Db::exec('SAVEPOINT matomo_statements');
        try {
            $this->execStatements();
        } catch (\Exception $e) {
            Db::exec('ROLLBACK TO matomo_statements');
            Db::exec('RELEASE matomo_statements');
            throw $e;
        }
        Db::exec('RELEASE matomo_statements');
    }

    /**
     * @return string[]
     */
    protected function getStatements(): array
    {
        return $this->statements;
    }

    private function execStatements(): void
    {
        foreach ($this->statements as $statement) {
            try {
                Db::exec($statement);
            } catch (\Exception $e) {
                if (!$this->shouldIgnoreError($e)) {
                    throw $e;
                }
            }
        }
    }
}
