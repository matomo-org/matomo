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
    private const TABLE = 'log_group';

    private readonly string $tablePrefixed;

    public function __construct()
    {
        $this->tablePrefixed = Common::prefixTable(self::TABLE);
    }

    public function install(): void
    {
        DbHelper::createTable(self::TABLE, "
                  `group` VARCHAR(30) NOT NULL,
                  `is_admin` TINYINT(1) NOT NULL,
                  PRIMARY KEY (`group`)");
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
            'group' => $group,
            'is_admin' => (int) $isAdmin,
        ];

        $sql = sprintf(
            'INSERT INTO `%s` (`%s`) VALUES(%s) ON DUPLICATE KEY UPDATE `is_admin` = ?',
            $this->tablePrefixed,
            implode('`,`', array_keys($columns)),
            Common::getSqlStringFieldsArray($columns)
        );

        Db::query($sql, [...array_values($columns), (int) $isAdmin]);
    }
}
