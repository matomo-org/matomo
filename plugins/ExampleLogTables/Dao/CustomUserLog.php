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
     * @return list<array<array-key, string|int|float|null>>
     */
    public function getAllRecords(): array
    {
        return Db::fetchAll('SELECT * FROM ' . $this->tablePrefixed);
    }

    public function addUserInformation(string $userId, string $group, string $gender): void
    {
        $columns = [
            'user_id' => $userId,
            'group' => $group,
            'gender' => $gender,
        ];

        $sql = sprintf(
            'INSERT INTO `%s` (`%s`) VALUES(%s)',
            $this->tablePrefixed,
            implode('`,`', array_keys($columns)),
            Common::getSqlStringFieldsArray($columns)
        );

        Db::query($sql, array_values($columns));
    }
}
