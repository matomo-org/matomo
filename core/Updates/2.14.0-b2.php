<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Updates;

use Piwik\Updater;
use Piwik\Updates;
use Piwik\Db;
use Piwik\Updater\Migration\Factory as MigrationFactory;

class Updates_2_14_0_b2 extends Updates
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
        $table = 'site_setting';

        return array(
            $this->migration->db->dropTable($table),
            $this->migration->db->createTable($table, array(
                'idsite' => 'INTEGER(10) UNSIGNED NOT NULL AUTO_INCREMENT',
                'setting_name' => 'VARCHAR(255) NOT NULL',
                'setting_value' => 'LONGTEXT NOT NULL',
            ), $primaryKey = array('idsite', 'setting_name')),
        );
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }
}
