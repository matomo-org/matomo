<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Updates;

use Piwik\Common;
use Piwik\DbHelper;
use Piwik\Updater;
use Piwik\Updates;
use Piwik\Updater\Migration\Factory as MigrationFactory;

/**
 */
class Updates_0_2_33 extends Updates
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
            // 0.2.33 [1020]
            $this->migration->db->sql('ALTER TABLE `' . Common::prefixTable('user_dashboard') . '`
                CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci ', Updater\Migration\Db::ERROR_CODE_TABLE_NOT_EXISTS),
            $this->migration->db->sql('ALTER TABLE `' . Common::prefixTable('user_language') . '`
                CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci ', Updater\Migration\Db::ERROR_CODE_TABLE_NOT_EXISTS),
        );

        // alter table to set the utf8 collation
        $tablesToAlter = DbHelper::getTablesInstalled(true);
        foreach ($tablesToAlter as $table) {
            $migrations[] = $this->migration->db->sql('ALTER TABLE `' . $table . '` CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci');
        }

        return $migrations;
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }
}
