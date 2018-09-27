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
use Piwik\Updates as PiwikUpdates;
use Piwik\Updater\Migration\Factory as MigrationFactory;

class Updates_3_7_0_b1 extends PiwikUpdates
{
    private $migration;

    public function __construct(MigrationFactory $factory)
    {
        $this->migration = $factory;
    }

    public function getMigrations(Updater $updater)
    {
        $trackingFailureTable = $this->migration->db->createTable('tracking_failure',
            array('idsite' => 'BIGINT(20) UNSIGNED NOT NULL',
                  'idfailure' => 'SMALLINT UNSIGNED NOT NULL',
                  'date_first_occurred' => 'DATETIME NOT NULL',
                  'request_url' => 'MEDIUMTEXT NOT NULL'),
            array('idsite', 'idfailure'));

        return array(
            $trackingFailureTable
        );
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }
}
