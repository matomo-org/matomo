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
    /**
     * Get the Db engine
     *
     * @return string
     */
    public function getEngine(): string
    {
        return (string) $this->getDbSetting('type');
    }

    /**
     * Get the Db table prefix
     *
     * @return string|null
     */
    public function getTablePrefix(): ?string
    {
        return (string) $this->getDbSetting('tables_prefix');
    }

    /**
     * Get the Db name
     *
     * @return string
     */
    public function getDbName(): string
    {
        return (string) $this->getDbSetting('dbname');
    }

    /**
     * Get the charset
     *
     * @return string
     */
    public function getUsedCharset(): string
    {
        return strtolower($this->getDbSetting('charset'));
    }

    /**
     * Get a Db setting
     *
     * @param string $key
     *
     * @return mixed|null
     */
    private function getDbSetting(string $key)
    {
        $dbInfos = Db::getDatabaseConfig();
        return $dbInfos[$key] ?? null;
    }
}
