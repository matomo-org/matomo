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
    private const TABLE = 'log_custom';

    private readonly string $tablePrefixed;

    public function __construct()
    {
        $this->tablePrefixed = Common::prefixTable(self::TABLE);
    }

    public function install(): void
    {
        DbHelper::createTable(self::TABLE, "
                  `user_id` VARCHAR(191) NOT NULL,
                  `gender` VARCHAR(30) NOT NULL,
                  `group` VARCHAR(30) NOT NULL,
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
        $sql = 'SELECT `gender`, `group` FROM ' . $this->tablePrefixed . ' WHERE `user_id` = ?';

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
            'group' => $group,
            'gender' => $gender,
        ];

        $sql = sprintf(
            'INSERT INTO `%s` (`%s`) VALUES(%s) ON DUPLICATE KEY UPDATE `group` = ?, `gender` = ?',
            $this->tablePrefixed,
            implode('`,`', array_keys($columns)),
            Common::getSqlStringFieldsArray($columns)
        );

        Db::query($sql, [...array_values($columns), $group, $gender]);
    }
}
