<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ExampleLogTables\Dao;

use Piwik\Common;
use Piwik\Db;
use Piwik\DbHelper;

/**
 * Owns the account table: its schema, and one row per account.
 *
 * See {@see CustomUserLog} for the conventions both DAOs share.
 */
class CustomAccountLog
{
    /**
     * @see CustomUserLog::TABLE_NAME for why this is a public constant rather than a literal.
     */
    public const TABLE_NAME = 'log_examplelogtables_account';

    /**
     * The width of the account name, in characters.
     *
     * This is the join column between the two custom tables, so it is declared here, used in both
     * schemas and clamped in the write path. A join column whose two sides disagree on width leaves
     * rows that can never be joined again -- the same trap as a `user_id` narrower than
     * `log_visit`'s, one table further out.
     */
    public const MAX_LENGTH_ACCOUNT_NAME = 30;

    private string $tablePrefixed;

    public function __construct()
    {
        $this->tablePrefixed = Common::prefixTable(self::TABLE_NAME);
    }

    public function install(): void
    {
        // The column is `account_name` rather than a bare `account`, and the habit matters more than
        // this particular name does: `RawLogDao` builds `SELECT MAX(<id column>)` unquoted for every
        // declared log table while purging raw log data, so a table whose id column is a reserved
        // word -- `group`, `order`, `rank`, `key` -- breaks that purge with a syntax error, on a
        // path no test in your plugin exercises. Neither name here is reserved. Pick column names
        // that survive being interpolated into someone else's SQL and you never have to check.
        DbHelper::createTable(self::TABLE_NAME, sprintf('
                  `account_name` VARCHAR(%d) NOT NULL,
                  `is_paying` TINYINT(1) NOT NULL DEFAULT 0,
                  PRIMARY KEY (`account_name`)', self::MAX_LENGTH_ACCOUNT_NAME));
    }

    public function uninstall(): void
    {
        Db::query(sprintf('DROP TABLE IF EXISTS `%s`', $this->tablePrefixed));
    }

    public function addOrUpdateAccountInformation(string $account, bool $isPaying): void
    {
        // Unlike the user table there is nothing partial to write here: the account name is the key
        // and the flag is the only other column, so a caller that has one has both. That is why
        // this overwrites unconditionally where the user DAO writes only the columns it was given.
        $sql = sprintf(
            'INSERT INTO `%s` (account_name, is_paying) VALUES (?, ?) ON DUPLICATE KEY UPDATE is_paying = ?',
            $this->tablePrefixed
        );

        Db::query($sql, [$account, (int) $isPaying, (int) $isPaying]);
    }
}
