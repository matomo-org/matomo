<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Updates
 */

namespace Piwik\Updates;

use Piwik\Common;
use Piwik\Updater;
use Piwik\Updates;

/**
 * @package Updates
 */
class Updates_1_9_b9 extends Updates
{
    static function isMajorUpdate()
    {
        return true;
    }

    static function getSql($schema = 'Myisam')
    {
        $logVisit = Common::prefixTable('log_visit');
        $logConversion = Common::prefixTable('log_conversion');

        $addColumns = "DROP `location_continent`,
					   ADD `location_region` CHAR(2) NULL AFTER `location_country`,
					   ADD `location_city` VARCHAR(255) NULL AFTER `location_region`,
					   ADD `location_latitude` FLOAT(10, 6) NULL AFTER `location_city`,
			           ADD `location_longitude` FLOAT(10, 6) NULL AFTER `location_latitude`";

        return array(
            // add geoip columns to log_visit
            "ALTER TABLE `$logVisit` $addColumns"      => 1091,

            // add geoip columns to log_conversion
            "ALTER TABLE `$logConversion` $addColumns" => 1091,
        );
    }

    static function update()
    {
        try {
            self::enableMaintenanceMode();
            Updater::updateDatabase(__FILE__, self::getSql());
            self::disableMaintenanceMode();
        } catch (\Exception $e) {
            self::disableMaintenanceMode();
            throw $e;
        }
    }
}

