<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Updates;

use Piwik\Updater;
use Piwik\Updates as PiwikUpdates;
use Piwik\Updater\Migration;
use Piwik\Updater\Migration\Factory as MigrationFactory;

/**
 * Update for version 4.7.1-b1
 */
class Updates_4_7_1_b1 extends PiwikUpdates
{
    /**
     * @var MigrationFactory
     */
    private $migration;

    public function __construct(MigrationFactory $factory)
    {
        $this->migration = $factory;
    }

    /**
     * Here you can define one or multiple SQL statements that should be executed during the update.
     *
     * @param Updater $updater
     *
     * @return Migration[]
     */
    public function getMigrations(Updater $updater)
    {
        $migrations = [];

        $migrations[] = $this->migration->db->changeColumn('changes', 'plugin_name', 'plugin_name', 'VARCHAR(60) NOT NULL');

        $migrations[] = $this->migration->db->dropIndex('changes', 'unique_plugin_version_title');
        $migrations[] = $this->migration->db->addUniqueKey('changes', ['plugin_name', 'version', 'title(100)'], 'unique_plugin_version_title');

        return $migrations;
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }

}
