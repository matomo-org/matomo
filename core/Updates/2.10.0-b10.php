<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Updates;

use Piwik\DataAccess\ArchiveTableCreator;
use Piwik\Updater;
use Piwik\Updates;
use Piwik\Updater\Migration\Factory as MigrationFactory;

class Updates_2_10_0_b10 extends Updates
{
    /**
     * @var MigrationFactory
     */
    private $migration;

    public function __construct(MigrationFactory $factory)
    {
        $this->migration = $factory;
    }

    public function getMigrations(Updater $updater)
    {
        $migrations = array();

        $archiveTables = ArchiveTableCreator::getTablesArchivesInstalled();

        $archiveBlobTables = array_filter($archiveTables, function ($name) {
            return ArchiveTableCreator::getTypeFromTableName($name) == ArchiveTableCreator::BLOB_TABLE;
        });

        foreach ($archiveBlobTables as $table) {
            $migrations[] = $this->migration->db->sql("UPDATE $table SET name = 'DevicePlugins_plugin' WHERE name = 'UserSettings_plugin'");
        }

        return $migrations;
    }

    public function doUpdate(Updater $updater)
    {
        $pluginManager = \Piwik\Plugin\Manager::getInstance();

        try {
            $pluginManager->activatePlugin('DevicePlugins');
        } catch (\Exception $e) {
        }

        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }
}
