<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Updates;

use Piwik\Common;
use Piwik\DbHelper;
use Piwik\Updater;
use Piwik\Updates;
use Piwik\Updater\Migration\Factory as MigrationFactory;

class Updates_5_11_0_b1 extends Updates
{
    /**
     * @var MigrationFactory
     */
    private $migration;

    public function __construct(MigrationFactory $factory)
    {
        $this->migration = $factory;
    }

    public function getMigrations(Updater $updater): array
    {
        $migrations = [
            $this->migration->db->addColumn('site', 'description', "VARCHAR(255) NOT NULL DEFAULT ''", 'name'),
        ];

        if (DbHelper::tableExists(Common::prefixTable('custom_dimensions'))) {
            $migrations[] = $this->migration->db->addColumn('custom_dimensions', 'description', "VARCHAR(1000) NOT NULL DEFAULT ''", 'name');
        }

        return $migrations;
    }

    public function doUpdate(Updater $updater): void
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }
}
