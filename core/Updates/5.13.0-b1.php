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

class Updates_5_13_0_b1 extends PiwikUpdates
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
     * @return Migration[]
     */
    public function getMigrations(Updater $updater): array
    {
        $migrations = [];

        $migrations[] = $this->migration->db->createTable(
            'log_page_view_time',
            [
                'idpageviewtime' => 'BIGINT(10) UNSIGNED NOT NULL AUTO_INCREMENT',
                'idsite'         => 'INTEGER(10) UNSIGNED NOT NULL',
                'idvisit'        => 'BIGINT(10) UNSIGNED NOT NULL',
                'idvisitor'      => 'BINARY(8) NOT NULL',
                'idpageview'     => 'CHAR(6) NULL DEFAULT NULL',
                'idaction_url'   => 'INTEGER(10) UNSIGNED NULL DEFAULT NULL',
                'idaction_name'  => 'INTEGER(10) UNSIGNED NULL DEFAULT NULL',
                'server_time'    => 'DATETIME NOT NULL',
                'time_spent'     => 'INTEGER(10) UNSIGNED NOT NULL DEFAULT 0',
            ],
            'idpageviewtime'
        );

        $migrations[] = $this->migration->db->addUniqueKey(
            'log_page_view_time',
            ['idvisit', 'idpageview'],
            'unique_idvisit_idpageview'
        );

        $migrations[] = $this->migration->db->addIndex(
            'log_page_view_time',
            ['idvisit', 'idaction_url'],
            'index_idvisit_idaction_url'
        );

        $migrations[] = $this->migration->db->addIndex(
            'log_page_view_time',
            ['idsite', 'server_time'],
            'index_idsite_server_time'
        );

        return $migrations;
    }

    public function doUpdate(Updater $updater): void
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }
}
