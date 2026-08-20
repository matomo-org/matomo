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

class CustomGroupLog
{
    /**
     * @see CustomUserLog::TABLE_NAME for why this is a public constant rather than a literal.
     */
    public const TABLE_NAME = 'log_examplelogtables_group';

    /**
     * The width of the group name, in characters.
     *
     * This is the join column between the two custom tables, so it is declared here, used in both
     * schemas and clamped in the write path. A join column whose two sides disagree on width leaves
     * rows that can never be joined again -- the same trap as a `user_id` narrower than
     * `log_visit`'s, one table further out.
     */
    public const MAX_LENGTH_GROUP_NAME = 30;

    private string $tablePrefixed;

    public function __construct()
    {
        $this->tablePrefixed = Common::prefixTable(self::TABLE_NAME);
    }

    /**
     * Creates the table.
     *
     * The column is `group_name`, not `group`. `group` is a MySQL reserved word, and while this
     * plugin's own queries could quote it, core's do not always: `RawLogDao` builds
     * `SELECT MAX(<id column>)` unquoted for every declared log table, so a table whose id column is
     * a reserved word makes the site's raw-log purge fail with a syntax error. Pick column names that
     * survive being interpolated into someone else's SQL.
     */
    public function install(): void
    {
        DbHelper::createTable(self::TABLE_NAME, sprintf('
                  `group_name` VARCHAR(%d) NOT NULL,
                  `is_admin` TINYINT(1) NOT NULL DEFAULT 0,
                  PRIMARY KEY (group_name)', self::MAX_LENGTH_GROUP_NAME));
    }

    public function uninstall(): void
    {
        Db::query(sprintf('DROP TABLE IF EXISTS `%s`', $this->tablePrefixed));
    }

    /**
     * Records the attributes of one group, overwriting whatever was recorded for it before.
     *
     * Unlike the user table there is nothing partial to write here: the group name is the key and the
     * flag is the only other column, so a caller that has one has both.
     */
    public function addOrUpdateGroupInformation(string $group, bool $isAdmin): void
    {
        $sql = sprintf(
            'INSERT INTO `%s` (group_name, is_admin) VALUES (?, ?) ON DUPLICATE KEY UPDATE is_admin = ?',
            $this->tablePrefixed
        );

        Db::query($sql, [$group, (int) $isAdmin, (int) $isAdmin]);
    }
}
