<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Db;

use Piwik\Db;

/**
 * Schema abstraction
 *
 * Note: no relation to the ZF proposals for Zend_Db_Schema_Manager
 *
 * @method static \Piwik\Db\Schema getInstance()
 */
class Settings
{
    public function getEngine()
    {
        return $this->getDbSetting('type');
    }

    public function getTablePrefix()
    {
        return $this->getDbSetting('tables_prefix');
    }

    public function getDbName()
    {
        return $this->getDbSetting('dbname');
    }

    private function getDbSetting($key)
    {
        $dbInfos = Db::getDatabaseConfig();
        $engine  = $dbInfos[$key];

        return $engine;
    }
}
