<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Updates;

use Piwik\Config;
use Piwik\Updater;
use Piwik\Updates as PiwikUpdates;
use Piwik\Updater\Migration;
use Piwik\Updater\Migration\Factory as MigrationFactory;

/**
 * Update for version 6.0.0-b1
 */
class Updates_6_0_0_b1 extends PiwikUpdates
{
    private MigrationFactory $migration;

    public function __construct(MigrationFactory $factory)
    {
        $this->migration = $factory;
    }

    /**
     * @return Migration[]
     */
    public function getMigrations(Updater $updater)
    {
        $migrations = [];

        // remove discontinued SEO plugin
        $migrations[] = $this->migration->plugin->deactivate('SEO');
        $migrations[] = $this->migration->plugin->uninstall('SEO');

        // TrackingSpamPrevention is bundled with core and activated by default since Matomo 6.
        // When it was installed but deactivated, the updater has already collected the components to
        // update, so its own update files only run during the next update.
        $migrations[] = $this->migration->plugin->activate('TrackingSpamPrevention');

        // flat-first Actions archiving is enabled by default since Matomo 6, but only for new
        // installations, so keep the legacy hierarchical archiving where the setting is untouched
        $config = Config::getInstance();
        $localGeneral = $config->getFromLocalConfig('General');
        $commonGeneral = $config->getFromCommonConfig('General');

        if (
            !isset($localGeneral['datatable_archiving_maximum_rows_actions_flat'])
            && !isset($commonGeneral['datatable_archiving_maximum_rows_actions_flat'])
        ) {
            $migrations[] = $this->migration->config->set(
                'General',
                'datatable_archiving_maximum_rows_actions_flat',
                0
            );
        }

        return $migrations;
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }
}
