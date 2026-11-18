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
 * Adds the column that remembers a pageview's time was reported by its own heartbeat ping, so a later pageview
 * in the visit no longer raises it.
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
                'log_page_view_time',
                'has_heartbeat',
                'TINYINT(1) UNSIGNED NOT NULL DEFAULT 0'
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

    public static function isMajorUpdate()
    {
        return true;
    }
}
