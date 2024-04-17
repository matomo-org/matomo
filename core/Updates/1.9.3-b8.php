<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Updates;

use Piwik\Updater;
use Piwik\Updates;
use Piwik\Updater\Migration\Factory as MigrationFactory;

/**
 */
class Updates_1_9_3_b8 extends Updates
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
            $this->migration->db->addColumn('site', 'excluded_user_agents', 'TEXT NOT NULL', 'excluded_parameters')
        );
    }

    public function doUpdate(Updater $updater)
    {
        // add excluded_user_agents column to site table
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }
}
