<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Updates;

use Piwik\Cache;
use Piwik\Config;
use Piwik\Updater;
use Piwik\Updates as PiwikUpdates;
use Piwik\Updater\Migration\Factory as MigrationFactory;

/**
 * Update for version 4.0.0-b1.
 */
class Updates_4_0_0_b1 extends PiwikUpdates
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
        $migration1 = $this->migration->db->changeColumnType('log_action', 'name', 'VARCHAR(4096)');
        $migration2 = $this->migration->db->changeColumnType('log_conversion', 'url', 'VARCHAR(4096)');

        $migration3 = $this->migration->plugin->activate('BulkTracking');

        return array(
            $migration1,
            $migration2,
            $migration3
        );
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));

        $this->renameTrackerJsPluginInConfig();

        Cache::flushAll();
    }

    protected function renameTrackerJsPluginInConfig()
    {
        $updater = new Updater();
        $updater->markComponentSuccessfullyUninstalled('CustomPiwikJs');

        $config = Config::getInstance();

        foreach ($config->Plugins['Plugins'] as $index => $plugin) {
            if ($plugin === 'CustomPiwikJs') {
                $config->Plugins['Plugins'][$index] = 'CustomJsTracker';
            }
        }

        foreach ($config->PluginsInstalled['PluginsInstalled'] as $index => $plugin) {
            if ($plugin === 'CustomPiwikJs') {
                $config->PluginsInstalled['PluginsInstalled'][$index] = 'CustomJsTracker';
            }
        }

        $config->forceSave();
    }
}
