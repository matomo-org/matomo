<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Updates;

use Piwik\Common;
use Piwik\SettingsServer;
use Piwik\Updater;
use Piwik\Updates;
use Piwik\Updater\Migration\Factory as MigrationFactory;

/**
 */
class Updates_0_9_1 extends Updates
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
        if (!SettingsServer::isTimezoneSupportEnabled()) {
            return array();
        }
        // @see http://bugs.php.net/46111
        $timezones = timezone_identifiers_list();
        $brokenTZ = array();

        foreach ($timezones as $timezone) {
            $testDate = "2008-08-19 13:00:00 " . $timezone;

            if (!strtotime($testDate)) {
                $brokenTZ[] = $timezone;
            }
        }
        $timezoneList = '"' . implode('","', $brokenTZ) . '"';

        return array(
            $this->migration->db->sql(
                'UPDATE ' . Common::prefixTable('site') . '
                 SET timezone = "UTC" WHERE timezone IN (' . $timezoneList . ')'
            ),

            $this->migration->db->sql(
                'UPDATE `' . Common::prefixTable('option') . '`
                 SET option_value = "UTC" WHERE option_name = "SitesManager_DefaultTimezone" AND option_value IN (' . $timezoneList . ')'
            ),
        );
    }

    public function doUpdate(Updater $updater)
    {
        if (SettingsServer::isTimezoneSupportEnabled()) {
            $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
        }
    }
}
