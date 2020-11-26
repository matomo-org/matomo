<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Updates;

use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\DataAccess\ArchiveTableCreator;
use Piwik\Date;
use Piwik\DbHelper;
use Piwik\Plugin\ReleaseChannels;
use Piwik\SettingsPiwik;
use Piwik\Updater;
use Piwik\Updates as PiwikUpdates;
use Piwik\Updater\Migration\Factory as MigrationFactory;

class Updates_4_0_1_b1 extends PiwikUpdates
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

        $migrations[] = $this->migration->plugin->uninstall('ExampleTheme');
        return $migrations;
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }

}
