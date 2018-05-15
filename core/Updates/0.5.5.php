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
use Piwik\DbHelper;
use Piwik\Updater;
use Piwik\Updates;
use Piwik\Updater\Migration\Factory as MigrationFactory;

/**
 */
class Updates_0_5_5 extends Updates
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
        $migrations = array(
            $this->migration->db->dropIndex('log_visit', 'index_idsite_date'),
            $this->migration->db->addIndex('log_visit', array('idsite', 'visit_server_date', 'config_md5config(8)'), 'index_idsite_date_config'),
        );

        $tables = DbHelper::getTablesInstalled();
        foreach ($tables as $tableName) {
            $unprefixedTable = Common::unprefixTable($tableName);
            if (preg_match('/archive_/', $tableName) == 1) {
                $migrations[] = $this->migration->db->dropIndex($unprefixedTable, 'index_all');
            }
            if (preg_match('/archive_numeric_/', $tableName) == 1) {
                $columns = array('idsite', 'date1', 'date2', 'period');
                $migrations[] = $this->migration->db->addIndex($unprefixedTable, $columns, 'index_idsite_dates_period');
            }
        }

        return $migrations;
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }
}
