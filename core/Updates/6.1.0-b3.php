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
use Piwik\Updater\Migration\Factory as MigrationFactory;
use Piwik\Updates;

/**
 * Adds the column that stores whether a goal is starred for its website.
 */
class Updates_6_1_0_b3 extends Updates
{
    private MigrationFactory $migration;

    public function __construct(MigrationFactory $factory)
    {
        $this->migration = $factory;
    }

    public function getMigrations(Updater $updater)
    {
        return [
            $this->migration->db->addColumn(
                'goal',
                'starred',
                'TINYINT(1) UNSIGNED NOT NULL DEFAULT 0',
                'event_value_as_revenue'
            ),
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
