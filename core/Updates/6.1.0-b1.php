<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Updates;

use Piwik\Updater;
use Piwik\Updater\Migration;
use Piwik\Updater\Migration\Factory as MigrationFactory;
use Piwik\Updates;

/**
 * Adds the table holding the pages whose Transitions report is pre-processed.
 */
class Updates_6_1_0_b1 extends Updates
{
    private MigrationFactory $migration;

    public function __construct(MigrationFactory $factory)
    {
        $this->migration = $factory;
    }

    /**
     * @return Migration[]
     */
    public function getMigrations(Updater $updater)
    {
        return [
            $this->migration->db->createTable('transitions_preprocessed_page', [
                'idpage' => 'INTEGER UNSIGNED NOT NULL AUTO_INCREMENT',
                'idsite' => 'INTEGER UNSIGNED NOT NULL',
                'url' => 'TEXT NOT NULL',
                'url_hash' => 'CHAR(40) NOT NULL',
                'idaction' => 'INTEGER UNSIGNED NULL',
                'previous_idaction' => 'INTEGER UNSIGNED NULL',
                'created_date' => 'DATETIME NOT NULL',
                'created_by' => 'VARCHAR(100) NULL',
            ], ['idpage']),
            $this->migration->db->addUniqueKey('transitions_preprocessed_page', ['idsite', 'url_hash'], 'index_idsite_url_hash'),
        ];
    }

    /**
     * @return void
     */
    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }
}
