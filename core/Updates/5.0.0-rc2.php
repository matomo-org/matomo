<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Updates;

use Piwik\Common;
use Piwik\Option;
use Piwik\Updater;
use Piwik\Updates as PiwikUpdates;
use Piwik\Updater\Migration\Factory as MigrationFactory;

/**
 * Update for version 5.0.0-rc2
 */
class Updates_5_0_0_rc2 extends PiwikUpdates
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

        $migrations[] = $this->migration->plugin->activate('JsTrackerInstallCheck');
        $migrations[] = $this->migration->db->changeColumn('user_token_auth', 'post_only', 'secure_only', "TINYINT(2) UNSIGNED NOT NULL DEFAULT '0'");

        // We need to remove all stored view data table settings for referrers evolution chart, as the identifier for
        // visible rows was changed from label to the type. Keeping the settings would cause no data to be displayed
        $viewDataTableSettings = Option::getLike('viewDataTableParameters_%_Referrers.getEvolutionGraph');

        foreach ($viewDataTableSettings as $name => $value) {
            $migrations[] = $this->migration->db->sql(
                sprintf(
                    'DELETE FROM %s WHERE option_name = "%s"',
                    Common::prefixTable('option'),
                    $name
                )
            );
        }

        return $migrations;
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }
}
