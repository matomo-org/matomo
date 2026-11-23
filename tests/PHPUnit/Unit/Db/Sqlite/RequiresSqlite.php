<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Db\Sqlite;

use PDO;
use Piwik\Db\Sqlite\Connection;

/**
 * Skips tests needing SQLite on runtimes without pdo_sqlite or with a SQLite older than supported.
 */
trait RequiresSqlite
{
    protected function requireSqlite(): void
    {
        if (!Connection::isEnabled()) {
            $this->markTestSkipped('pdo_sqlite is not available.');
        }

        $version = (new PDO('sqlite::memory:'))->query('SELECT sqlite_version()')->fetchColumn();
        if (version_compare($version, Connection::MINIMUM_VERSION) < 0) {
            $this->markTestSkipped("SQLite $version is older than " . Connection::MINIMUM_VERSION . '.');
        }
    }
}
