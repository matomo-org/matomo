<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Updates;

use Piwik\Common;
use Piwik\SettingsServer;
use Piwik\Updater;
use Piwik\Updates;

/**
 */
class Updates_0_9_1 extends Updates
{
    public function getMigrationQueries(Updater $updater)
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
            'UPDATE ' . Common::prefixTable('site') . '
				SET timezone = "UTC"
				WHERE timezone IN (' . $timezoneList . ')'                                                            => false,

            'UPDATE `' . Common::prefixTable('option') . '`
				SET option_value = "UTC"
			WHERE option_name = "SitesManager_DefaultTimezone"
				AND option_value IN (' . $timezoneList . ')' => false,
        );
    }

    public function doUpdate(Updater $updater)
    {
        if (SettingsServer::isTimezoneSupportEnabled()) {
            $updater->executeMigrationQueries(__FILE__, $this->getMigrationQueries($updater));
        }
    }
}
