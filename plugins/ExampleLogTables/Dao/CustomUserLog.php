<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\ExampleLogTables\Dao;

use Piwik\Common;
use Piwik\Db;
use Piwik\DbHelper;

class CustomUserLog
{
    private $table = 'log_custom';
    private $tablePrefixed = '';

    public function __construct()
    {
        $this->tablePrefixed = Common::prefixTable($this->table);
    }

    public function install()
    {
        DbHelper::createTable($this->table, "
                  `user_id` VARCHAR(191) NOT NULL,
                  `gender` VARCHAR(30) NOT NULL,
                  `group` VARCHAR(30) NOT NULL,
                  PRIMARY KEY (user_id)");
    }

    public function uninstall()
    {
        Db::query(sprintf('DROP TABLE IF EXISTS `%s`', $this->tablePrefixed));
    }

    private function getDb()
    {
        return Db::get();
    }

    public function getAllRecords()
    {
        return $this->getDb()->fetchAll('SELECT * FROM ' . $this->tablePrefixed);
    }

    public function addUserInformation($userId, $group, $gender)
    {
        $columns = array(
            'user_id' => $userId,
            'group' => $group,
            'gender' => $gender
        );

        $bind = array_values($columns);
        $placeholder = Common::getSqlStringFieldsArray($columns);

        $sql = sprintf(
            'INSERT INTO %s (`%s`) VALUES(%s)',
            $this->tablePrefixed,
            implode('`,`', array_keys($columns)),
            $placeholder
        );

        $db = $this->getDb();

        $db->query($sql, $bind);
    }
}
