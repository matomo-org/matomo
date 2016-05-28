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
use Piwik\Updater;
use Piwik\Updates;

/**
 */
class Updates_1_9_b9 extends Updates
{
    public static function isMajorUpdate()
    {
        return true;
    }

    public function getMigrationQueries(Updater $updater)
    {
        $logVisit = Common::prefixTable('log_visit');
        $logConversion = Common::prefixTable('log_conversion');

        $addColumns = "ADD `location_region` CHAR(2) NULL AFTER `location_country`,
					   ADD `location_city` VARCHAR(255) NULL AFTER `location_region`,
					   ADD `location_latitude` FLOAT(10, 6) NULL AFTER `location_city`,
			           ADD `location_longitude` FLOAT(10, 6) NULL AFTER `location_latitude`";
        $dropColumns = "DROP `location_continent`";

        return array(

            "ALTER TABLE `$logVisit` $dropColumns"      => 1091,
            "ALTER TABLE `$logConversion` $dropColumns" => 1091,

            // add geoip columns to log_visit
            "ALTER TABLE `$logVisit` $addColumns"      => 1060,
            // add geoip columns to log_conversion
            "ALTER TABLE `$logConversion` $addColumns" => 1060,
        );
    }

    public function doUpdate(Updater $updater)
    {
        try {
            self::enableMaintenanceMode();
            $updater->executeMigrationQueries(__FILE__, $this->getMigrationQueries($updater));
            self::disableMaintenanceMode();
        } catch (\Exception $e) {
            self::disableMaintenanceMode();
            throw $e;
        }
    }
}
