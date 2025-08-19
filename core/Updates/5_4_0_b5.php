<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Updates;

use Piwik\Updater;
use Piwik\Updates as PiwikUpdates;
use Piwik\Updater\Migration;
use Piwik\Updater\Migration\Factory as MigrationFactory;

/**
 * Update for version 5.4.0-b5
 */
class Updates_5_4_0_b5 extends PiwikUpdates
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

        // create new annotations table
        $migrations[] = $this->migration->db->createTable('annotations', [
                'id' => 'BIGINT UNSIGNED NOT NULL AUTO_INCREMENT',
                'idsite' => 'INTEGER UNSIGNED NOT NULL',
                'date' => 'DATETIME NOT NULL',
                'note' => 'TEXT NOT NULL',
                'starred' => 'TINYINT(1) NOT NULL DEFAULT 0',
            ], $primaryKey = 'id');
        $migrations[] = $this->migration->db->addIndex('annotations', ['id', 'idsite', 'date']);

        return $migrations;
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }
}
