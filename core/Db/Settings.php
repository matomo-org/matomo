<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Db;

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

    public function getUsedCharset()
    {
        return strtolower($this->getDbSetting('charset'));
    }

    public function getRowFormat()
    {
        return $this->getUsedCharset() === 'utf8mb4' ? 'ROW_FORMAT=DYNAMIC' : '';
    }

    private function getDbSetting($key)
    {
        $dbInfos = Db::getDatabaseConfig();
        return $dbInfos[$key] ?? null;
    }
}
