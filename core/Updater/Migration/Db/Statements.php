<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Updater\Migration\Db;

use Piwik\Common;
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

    public function __toString()
    {
        $sql = implode(";\n", $this->getStatements());

        if ($sql !== '' && !Common::stringEndsWith($sql, ';')) {
            $sql .= ';';
        }

        return $sql;
    }

    public function exec()
    {
        $statements = array_values(array_filter($this->getStatements(), function (string $statement): bool {
            return $statement !== '';
        }));

        if (count($statements) === 1) {
            // as a single Sql migration: the error, ignorable or not, goes to the caller
            Db::exec($statements[0]);
            return;
        }

        // the connection in use decides, whatever the configuration file says
        if (count($statements) === 0 || !Connection::isSqliteConnection(Db::get())) {
            $this->execStatements($statements);
            return;
        }

        Db::exec('SAVEPOINT matomo_statements');
        try {
            $this->execStatements($statements);
        } catch (\Exception $e) {
            Db::exec('ROLLBACK TO matomo_statements');
            Db::exec('RELEASE matomo_statements');
            throw $e;
        }
        Db::exec('RELEASE matomo_statements');
    }

    /**
     * The statements to run, for the connection in use.
     *
     * @return string[]
     */
    protected function getStatements(): array
    {
        return $this->statements;
    }

    /**
     * @param string[] $statements
     */
    private function execStatements(array $statements): void
    {
        foreach ($statements as $statement) {
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
