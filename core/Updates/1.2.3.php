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
use Piwik\Config;
use Piwik\Updater;
use Piwik\Updates;
use Piwik\Updater\Migration\Factory as MigrationFactory;

/**
 */
class Updates_1_2_3 extends Updates
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
        $dbName = Config::getInstance()->database['dbname'];

        return array(

            // LOAD DATA INFILE uses the database's charset
            $this->migration->db->sql('ALTER DATABASE `' . $dbName . '` DEFAULT CHARACTER SET utf8'),

            // Various performance improvements schema updates
            $this->migration->db->sql(
               'ALTER TABLE `' . Common::prefixTable('log_visit') . '`
                DROP INDEX index_idsite_datetime_config,
                DROP INDEX index_idsite_idvisit,
                ADD INDEX index_idsite_config_datetime (idsite, config_id, visit_last_action_time),
                ADD INDEX index_idsite_datetime (idsite, visit_last_action_time)',
                array(Updater\Migration\Db::ERROR_CODE_DUPLICATE_KEY, Updater\Migration\Db::ERROR_CODE_COLUMN_NOT_EXISTS)
            ),
        );
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }
}
