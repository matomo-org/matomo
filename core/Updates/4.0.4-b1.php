<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Updates;

use Piwik\SettingsPiwik;
use Piwik\Updater;
use Piwik\Updates as PiwikUpdates;
use Piwik\Updater\Migration\Factory as MigrationFactory;

class Updates_4_0_4_b1 extends PiwikUpdates
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
        $migrations = [];

        if (SettingsPiwik::isGitDeployment()) {
            return $migrations;
        }

        $migrations[] = $this->migration->plugin->deactivate('ExamplePlugin');
        $migrations[] = $this->migration->plugin->deactivate('ExampleLogTables');
        $migrations[] = $this->migration->plugin->deactivate('ExampleUI');

        $migrations[] = $this->migration->plugin->uninstall('ExamplePlugin');
        $migrations[] = $this->migration->plugin->uninstall('ExampleLogTables');
        $migrations[] = $this->migration->plugin->uninstall('ExampleUI');
        return $migrations;
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }

}
