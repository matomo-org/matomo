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
class Updates_0_4_2 extends Updates
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
            $this->migration->db->addColumn('log_visit', 'config_java', 'TINYINT(1) NOT NULL', 'config_flash'),
            $this->migration->db->addColumn('log_visit', 'config_quicktime', 'TINYINT(1) NOT NULL', 'config_flash'),
            $this->migration->db->addColumn('log_visit', 'config_gears', 'TINYINT(1) NOT NULL', 'config_windowsmedia'),
            $this->migration->db->addColumn('log_visit', 'config_silverlight', 'TINYINT(1) NOT NULL', 'config_gears'),
        );
    }

    // when restoring (possibly) previousy dropped columns, ignore mysql code error 1060: duplicate column
    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }
}
