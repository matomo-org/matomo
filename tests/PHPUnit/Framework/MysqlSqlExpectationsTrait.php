<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Framework;

use Piwik\Config;
use Piwik\Db;
use Piwik\Db\Schema;

/**
 * For unit tests whose expectations are the SQL generated for MySQL and MariaDB: when the tests
 * are configured for SQLite, they generate it with the MySQL schema instead, and an open SQLite
 * connection, which the schema would follow, is set aside meanwhile. The SQLite variants are covered
 * by the integration tests, which run the queries.
 */
trait MysqlSqlExpectationsTrait
{
    /** @var string|null */
    private $schemaBeforeMysqlExpectations = null;

    /** @var object|null */
    private $connectionBeforeMysqlExpectations = null;

    protected function useMysqlSchemaIfSqlite(): void
    {
        $schema = Config::getInstance()->database['schema'] ?? null;
        if ($schema === null || strtolower(trim($schema)) !== 'sqlite') {
            return;
        }

        $this->schemaBeforeMysqlExpectations = $schema;
        Config::getInstance()->database['schema'] = 'Mysql';
        Schema::unsetInstance();

        if (Db::hasDatabaseObject()) {
            $this->connectionBeforeMysqlExpectations = Db::get();
            Db::setDatabaseObject(null);
        }
    }

    protected function restoreSchemaAfterMysqlExpectations(): void
    {
        if ($this->schemaBeforeMysqlExpectations === null) {
            return;
        }

        Config::getInstance()->database['schema'] = $this->schemaBeforeMysqlExpectations;
        $this->schemaBeforeMysqlExpectations = null;
        Schema::unsetInstance();

        if ($this->connectionBeforeMysqlExpectations !== null) {
            Db::setDatabaseObject($this->connectionBeforeMysqlExpectations);
            $this->connectionBeforeMysqlExpectations = null;
        }
    }
}
