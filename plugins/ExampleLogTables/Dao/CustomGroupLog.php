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
    private $table = 'log_group';
    private $tablePrefixed = '';

    public function __construct()
    {
        $this->tablePrefixed = Common::prefixTable($this->table);
    }

    public function install()
    {
        DbHelper::createTable($this->table, "
                  `group` VARCHAR(30) NOT NULL,
                  `is_admin` TINYINT(1) NOT NULL,
                  PRIMARY KEY (`group`)");
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

    public function addGroupInformation($group, $isAdmin)
    {
        $columns = array(
            'group' => $group,
            'is_admin' => $isAdmin
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
