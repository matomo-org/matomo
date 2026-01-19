<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Updates;

use Piwik\Updater;
use Piwik\Updater\Migration\Factory as MigrationFactory;
use Piwik\Updates as PiwikUpdates;
use Piwik\Updater\Migration;

/**
 * Update for version 5.7.0-b1
 */
class Updates_5_7_0_b2 extends PiwikUpdates
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
     * @param Updater $updater
     * @return Migration[]
     */
    public function getMigrations(Updater $updater)
    {
        return [
            $this->migration->db->createTable('archiving_metrics', [
                'metadataid' => 'BIGINT UNSIGNED NOT NULL AUTO_INCREMENT',
                'idarchive' => 'BIGINT UNSIGNED NOT NULL',
                'idsite' => 'INTEGER UNSIGNED NOT NULL',
                'archive_name' => 'VARCHAR(255) NOT NULL',
                'date1' => 'DATE NOT NULL',
                'date2' => 'DATE NOT NULL',
                'period' => 'TINYINT UNSIGNED NOT NULL',
                'ts_started' => 'DATETIME NOT NULL',
                'ts_finished' => 'DATETIME NOT NULL',
                'total_time' => 'BIGINT UNSIGNED NOT NULL',
                'total_time_exclusive' => 'BIGINT UNSIGNED NOT NULL',
            ], ['metadataid']),
            $this->migration->db->addIndex('archiving_metrics', ['idarchive'], 'index_idarchive'),
            $this->migration->db->addIndex('archiving_metrics', ['idsite', 'archive_name'], 'index_idsite_archive_name'),
            $this->migration->db->addIndex('archiving_metrics', ['idsite', 'date1', 'period'], 'index_idsite_date1_period'),
        ];
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }
}
