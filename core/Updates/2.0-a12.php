<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Updates;

use Piwik\Common;
use Piwik\Db;
use Piwik\Updater;
use Piwik\Updates;
use Piwik\Updater\Migration\Factory as MigrationFactory;

/**
 */
class Updates_2_0_a12 extends Updates
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
        $result = array(
            $this->migration->db->sql('ALTER TABLE ' . Common::prefixTable('logger_message') . ' MODIFY level VARCHAR(16) NULL')
        );

        $unneededLogTables = array('logger_exception', 'logger_error', 'logger_api_call');
        foreach ($unneededLogTables as $table) {
            $tableName = Common::prefixTable($table);

            try {
                $rows = Db::fetchOne("SELECT COUNT(*) FROM $tableName");
                if ($rows == 0) {
                    $result[] = $this->migration->db->dropTable($table);
                }
            } catch (\Exception $ex) {
                // ignore
            }
        }

        return $result;
    }

    public function doUpdate(Updater $updater)
    {
        // change level column in logger_message table to string & remove other logging tables if empty
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }
}
