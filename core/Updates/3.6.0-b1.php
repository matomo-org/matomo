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
 * Update for version 3.6.0-b1.
 */
class Updates_3_6_0_b1 extends Updates
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

        // in the previous 2.14.0-b2 idsite was added w/ AUTO_INCREMENT, but it should not have been. (note: this
        // should have been undone in the 3.0.0-b1 update, but at least one instance out there still has the
        // AUTO_INCREMENT modifier).
        $migrations[] = $this->migration->db->changeColumn($this->siteSettingsTable, 'idsite', 'idsite', 'INTEGER(10) UNSIGNED NOT NULL');

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
        $queries[] = $this->migration->db->addColumn($this->pluginSettingsTable, 'idplugin_setting', 'BIGINT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT');

        return $queries;
    }

    /**
     * @param Migration[] $queries
     * @return Migration[]
     */
    private function getSiteSettingsMigrations($queries)
    {
        $queries[] = $this->migration->db->addColumn($this->siteSettingsTable, 'idsite_setting', 'BIGINT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT');

        return $queries;
    }

    /**
     * @param Migration[] $queries
     * @return Migration[]
     */
    private function getLogProfilingMigrations($queries)
    {
        $queries[] = $this->migration->db->addColumn($this->logProfilingTable, 'idprofiling', 'BIGINT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT');

        return $queries;
    }
}
