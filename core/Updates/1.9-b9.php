<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Updates;

use Piwik\Updater;
use Piwik\Updates;
use Piwik\Updater\Migration\Factory as MigrationFactory;

/**
 */
class Updates_1_9_b9 extends Updates
{
    /**
     * @var MigrationFactory
     */
    private $migration;

    public function __construct(MigrationFactory $factory)
    {
        $this->migration = $factory;
    }

    public static function isMajorUpdate()
    {
        return true;
    }

    public function getMigrations(Updater $updater)
    {
        $logVisit = 'log_visit';
        $logConversion = 'log_conversion';

        $addColumns = array('location_region' => 'CHAR(2) NULL',
                             'location_city' => 'VARCHAR(255) NULL',
                             'location_latitude' => 'FLOAT(10, 6) NULL',
                             'location_longitude' => 'FLOAT(10, 6) NULL');
        $dropColumn = 'location_continent';

        return array(
            $this->migration->db->dropColumn($logVisit, $dropColumn),
            $this->migration->db->dropColumn($logConversion, $dropColumn),

            // add geoip columns to log_visit
            $this->migration->db->addColumns($logVisit, $addColumns, 'location_country'),
            // add geoip columns to log_conversion
            $this->migration->db->addColumns($logConversion, $addColumns, 'location_country'),
        );
    }

    public function doUpdate(Updater $updater)
    {
        try {
            self::enableMaintenanceMode();
            $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
            self::disableMaintenanceMode();
        } catch (\Exception $e) {
            self::disableMaintenanceMode();
            throw $e;
        }
    }
}
