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
 * Update for version 4.7.0-b2
 */
class Updates_4_7_0_b2 extends PiwikUpdates
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

        // add column to track the last change a user viewed the changes list
        $migrations[] = $this->migration->db->addColumn('user', 'idchange_last_viewed',
            'INTEGER UNSIGNED NULL');

        $migrations[] = $this->migration->db->createTable('changes', array(
                'idchange' => 'INT(11) NOT NULL AUTO_INCREMENT',
                'created_time' => 'DATETIME NOT NULL',
                'plugin_name' => 'VARCHAR(255) NOT NULL',
                'version' => 'VARCHAR(20) NOT NULL',
                'title' => 'VARCHAR(255) NOT NULL',
                'description' => 'TEXT NOT NULL',
                'link_name' => 'VARCHAR(255) NULL',
                'link' => 'VARCHAR(255) NULL',
            ), $primaryKey = 'idchange');

        return $migrations;
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }

}
