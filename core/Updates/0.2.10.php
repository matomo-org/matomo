<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Updates;

use Piwik\Filesystem;
use Piwik\Updater;
use Piwik\Updates;
use Piwik\Updater\Migration\Factory as MigrationFactory;

/**
 */
class Updates_0_2_10 extends Updates
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
        $tableNotExistsError = Updater\Migration\Db::ERROR_CODE_TABLE_NOT_EXISTS;
        return array(
            $this->migration->db->createTable('option', array(
                'idoption' => 'BIGINT NOT NULL AUTO_INCREMENT' ,
                'option_name' => 'VARCHAR( 64 ) NOT NULL' ,
                'option_value' => 'LONGTEXT NOT NULL' ,
            ), array('idoption', 'option_name')),

            // 0.1.7 [463]
            $this->migration->db->changeColumnType('log_visit', 'location_provider', 'VARCHAR( 100 ) DEFAULT NULL'),

            // 0.1.7 [470]
            $this->migration->db->changeColumnTypes('logger_api_call', array(
                'parameter_names_default_values' => 'TEXT',
                'parameter_values' => 'TEXT',
                'returned_value' => 'TEXT',
            ))->addErrorCodeToIgnore($tableNotExistsError),
            $this->migration->db->changeColumnType('logger_error', 'message', 'TEXT')->addErrorCodeToIgnore($tableNotExistsError),
            $this->migration->db->changeColumnType('logger_exception', 'message', 'TEXT')->addErrorCodeToIgnore($tableNotExistsError),
            $this->migration->db->changeColumnType('logger_message', 'message', 'TEXT')->addErrorCodeToIgnore($tableNotExistsError),

            // 0.2.2 [489]
            $this->migration->db->changeColumnType('site', 'feedburnerName', 'VARCHAR( 100 ) DEFAULT NULL'),
        );
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));

        $obsoleteDirectories = array(
            '/plugins/AdminHome',
            '/plugins/Home',
            '/plugins/PluginsAdmin',
        );
        foreach ($obsoleteDirectories as $dir) {
            if (file_exists(PIWIK_INCLUDE_PATH . $dir)) {
                Filesystem::unlinkRecursive(PIWIK_INCLUDE_PATH . $dir, true);
            }
        }
    }
}
