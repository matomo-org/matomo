<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\Goals;

use Piwik\Updater;
use Piwik\Updates;
use Piwik\Updater\Migration\Factory as MigrationFactory;


class Updates_3_0_0_b1 extends Updates
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
     * @return Updater\Migration[]
     */
    public function getMigrations(Updater $updater)
    {
        return array(
            $this->migration->db->addColumn('goal', 'description', 'VARCHAR(255) NOT NULL DEFAULT \'\'', 'name'),
        );
    }

    /**
     * Here you can define any action that should be performed during the update. For instance executing SQL statements,
     * renaming config entries, updating files, etc.
     */
    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }
}
