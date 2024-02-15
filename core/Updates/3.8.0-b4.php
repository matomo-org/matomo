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

class Updates_3_8_0_b4 extends PiwikUpdates
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
        $trackingFailureTable = $this->migration->db->createTable(
            'tracking_failure',
            array('idsite' => 'BIGINT(20) UNSIGNED NOT NULL',
                  'idfailure' => 'SMALLINT UNSIGNED NOT NULL',
                  'date_first_occurred' => 'DATETIME NOT NULL',
                  'request_url' => 'MEDIUMTEXT NOT NULL'),
            array('idsite', 'idfailure')
        );

        $columns = array(
            'id_brute_force_log' => 'bigint(11) NOT NULL AUTO_INCREMENT',
            'ip_address' => 'VARCHAR(60) DEFAULT NULL',
            'attempted_at' => 'datetime NOT NULL',
        );
        return array(
            $trackingFailureTable,
            $this->migration->db->createTable('brute_force_log', $columns, 'id_brute_force_log'),
            $this->migration->db->addIndex('brute_force_log', 'ip_address', 'index_ip_address'),
        );
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }
}
