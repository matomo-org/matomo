<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Db;

use Piwik\Container\StaticContainer;
use Piwik\Db;

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
        $dbInfos = StaticContainer::get('db.config');
        $engine  = $dbInfos[$key];

        return $engine;
    }
}
