<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Concurrency\LockBackend;

/**
 * The `locks` table backend on SQLite: the same statements with SQLite's clock and without MySQL's
 * query cache modifier.
 */
class SqliteLockBackend extends MySqlLockBackend
{
    protected function getCurrentTimestampSql(): string
    {
        return 'unixepoch()';
    }

    protected function getSelectModifiers(): string
    {
        return '';
    }
}
