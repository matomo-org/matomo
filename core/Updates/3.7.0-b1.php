<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Updates;

use Piwik\Updater\Migration\Factory as MigrationFactory;
use Piwik\Updates;
use Piwik\Updater;

/**
 * Update for version 3.7.0-b1.
 */
class Updates_3_7_0_b1 extends Updates
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
        $columns = array(
            'id_brute_force_log' => 'bigint(11) NOT NULL AUTO_INCREMENT',
            'ip_address' => 'VARCHAR(60) DEFAULT NULL', 
            'attempted_at' => 'datetime NOT NULL',
        );
        return array(
            $this->migration->db->createTable('brute_force_log', $columns, 'id_brute_force_log'),
            $this->migration->db->addIndex('brute_force_log', 'ip_address', 'index_ip_address'),
        );
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }
}
