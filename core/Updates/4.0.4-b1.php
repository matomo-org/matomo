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
        $migrations[] = $this->migration->plugin->deactivate('ExampleReport');
        $migrations[] = $this->migration->plugin->deactivate('ExampleAPI');
        $migrations[] = $this->migration->plugin->deactivate('ExampleCommand');
        $migrations[] = $this->migration->plugin->deactivate('ExampleSettingsPlugin');
        $migrations[] = $this->migration->plugin->deactivate('ExampleTracker');
        $migrations[] = $this->migration->plugin->deactivate('ExampleVisualization');

        $migrations[] = $this->migration->plugin->uninstall('ExamplePlugin');
        $migrations[] = $this->migration->plugin->uninstall('ExampleLogTables');
        $migrations[] = $this->migration->plugin->uninstall('ExampleUI');
        $migrations[] = $this->migration->plugin->uninstall('ExampleReport');
        $migrations[] = $this->migration->plugin->uninstall('ExampleAPI');
        $migrations[] = $this->migration->plugin->uninstall('ExampleCommand');
        $migrations[] = $this->migration->plugin->uninstall('ExampleSettingsPlugin');
        $migrations[] = $this->migration->plugin->uninstall('ExampleTracker');
        $migrations[] = $this->migration->plugin->uninstall('ExampleVisualization');
        return $migrations;
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }

}
