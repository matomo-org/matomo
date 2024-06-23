<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Updates;

use Piwik\Updater;
use Piwik\Updates;
use Piwik\Updater\Migration\Factory as MigrationFactory;

/**
 */
class Updates_1_7_2_rc5 extends Updates
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
        return array(
            $this->migration->db->changeColumn('pdf', 'aggregate_reports_format', 'display_format', 'TINYINT(1) NOT NULL')
        );
    }

    public function doUpdate(Updater $updater)
    {
        try {
            $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
        } catch (\Exception $e) {
        }
    }
}
