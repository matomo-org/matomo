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
        DbHelper::createTable(self::TABLE_NAME, '
                  `group_name` VARCHAR(30) NOT NULL,
                  `is_admin` TINYINT(1) NOT NULL,
                  PRIMARY KEY (group_name)');
    }

    public function uninstall(): void
    {
        Db::query(sprintf('DROP TABLE IF EXISTS `%s`', $this->tablePrefixed));
    }

    /**
     * Records the attributes of one group, overwriting whatever was recorded for it before.
     */
    public function addOrUpdateGroupInformation(string $group, bool $isAdmin): void
    {
        $columns = [
            'group_name' => $group,
            'is_admin' => (int) $isAdmin,
        ];

        $sql = sprintf(
            'INSERT INTO `%s` (%s) VALUES(%s) ON DUPLICATE KEY UPDATE is_admin = ?',
            $this->tablePrefixed,
            implode(',', array_keys($columns)),
            Common::getSqlStringFieldsArray($columns)
        );

        Db::query($sql, [...array_values($columns), (int) $isAdmin]);
    }
}
