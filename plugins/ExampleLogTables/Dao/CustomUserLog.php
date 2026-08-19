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

class CustomUserLog
{
    /**
     * The unprefixed table name, public because every other class that names this table reads it from
     * here: the plugin class, both log table declarations, the dimension, the record builder and the
     * tests. A literal repeated in all of them can drift, and a table Matomo cannot find under the
     * name it was declared with fails silently.
     *
     * Prefix it with your plugin name. `log_custom` or `log_user` would collide with the next core
     * table of that name, and the `log_` prefix is what marks it as log data to a human reading the
     * schema.
     */
    public const TABLE_NAME = 'log_examplelogtables_user';

    private string $tablePrefixed;

    public function __construct()
    {
        $this->tablePrefixed = Common::prefixTable(self::TABLE_NAME);
    }

    /**
     * Creates the table.
     *
     * `user_id` is `VARCHAR(200)` because that is what `log_visit.user_id` is
     * (`CoreHome\Columns\UserId::MAXLENGTH`). Matomo connects with a non-strict `sql_mode`, so a
     * shorter column here would truncate a long user id *silently*, and the truncated value would no
     * longer equal the one in `log_visit` -- leaving a row that no join, and therefore no GDPR
     * deletion or export, can ever reach again.
     */
    public function install(): void
    {
        DbHelper::createTable(self::TABLE_NAME, "
                  `user_id` VARCHAR(200) NOT NULL,
                  `gender` VARCHAR(30) NOT NULL,
                  `group_name` VARCHAR(30) NOT NULL,
                  PRIMARY KEY (user_id)");
    }

    public function uninstall(): void
    {
        Db::query(sprintf('DROP TABLE IF EXISTS `%s`', $this->tablePrefixed));
    }

    /**
     * Returns the attributes recorded for one user, or an empty array if the user is unknown.
     *
     * @return array<string, string>
     */
    public function getUserInformation(string $userId): array
    {
        $sql = 'SELECT gender, group_name FROM ' . $this->tablePrefixed . ' WHERE user_id = ?';

        $row = Db::fetchRow($sql, [$userId]);

        return $row ?: [];
    }

    /**
     * Records the attributes of one user, overwriting whatever was recorded for them before.
     *
     * The tracker sees the same user on every one of their visits, so this has to be an upsert
     * rather than an insert: the primary key on `user_id` is what keeps the table at one row per
     * user instead of one row per request.
     */
    public function addOrUpdateUserInformation(string $userId, string $group, string $gender): void
    {
        $columns = [
            'user_id' => $userId,
            'group_name' => $group,
            'gender' => $gender,
        ];

        $sql = sprintf(
            'INSERT INTO `%s` (%s) VALUES(%s) ON DUPLICATE KEY UPDATE group_name = ?, gender = ?',
            $this->tablePrefixed,
            implode(',', array_keys($columns)),
            Common::getSqlStringFieldsArray($columns)
        );

        Db::query($sql, [...array_values($columns), $group, $gender]);
    }
}
