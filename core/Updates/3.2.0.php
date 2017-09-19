<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Updates;

use Piwik\Common;
use Piwik\Db;
use Piwik\Updater;
use Piwik\Updater\Migration;
use Piwik\Updater\Migration\Factory as MigrationFactory;
use Piwik\Updates;

/**
 * Update for version 3.2.0.
 */
class Updates_3_2_0 extends Updates
{
    /**
     * @var MigrationFactory
     */
    private $migration;

    private $pluginSettingsTable = 'plugin_setting';
    private $siteSettingsTable = 'site_setting';
    private $logProfilingTable = 'log_profiling';

    public function __construct(MigrationFactory $factory)
    {
        $this->migration = $factory;
    }

    /**
     * Here you can define one or multiple SQL statements that should be executed during the update.
     * @param Updater $updater
     * @return Migration[]
     */
    public function getMigrations(Updater $updater)
    {
        $migrations = [];
        $migrations = $this->getPluginSettingsMigrations($migrations);
        $migrations = $this->getSiteSettingsMigrations($migrations);
        $migrations = $this->getLogProfilingMigrations($migrations);

        return $migrations;
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }

    /**
     * @param Migration[] $queries
     * @return Migration[]
     */
    private function getPluginSettingsMigrations($queries)
    {
        $queries[] = $this->migration->db->addColumn($this->pluginSettingsTable, 'plugin_setting_id', 'BIGINT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT', 'user_login');

        return $queries;
    }

    /**
     * @param Migration[] $queries
     * @return Migration[]
     */
    private function getSiteSettingsMigrations($queries)
    {
        $queries[] = $this->migration->db->addColumn($this->siteSettingsTable, 'site_setting_id', 'BIGINT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT', 'setting_value');

        return $queries;
    }

    /**
     * @param Migration[] $queries
     * @return Migration[]
     */
    private function getLogProfilingMigrations($queries)
    {
        $queries[] = $this->migration->db->addColumn($this->logProfilingTable, 'log_profiling_id', 'BIGINT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT', 'sum_time_ms');

        return $queries;
    }
}
