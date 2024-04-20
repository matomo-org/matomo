<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Updates;

use Piwik\Common;
use Piwik\Updater;
use Piwik\Updates;
use Piwik\Updater\Migration\Factory as MigrationFactory;

/**
 * Update for version 2.13.1.
 */
class Updates_2_13_1 extends Updates
{
    /**
     * @var MigrationFactory
     */
    private $migration;

    public function __construct(MigrationFactory $factory)
    {
        $this->migration = $factory;
    }

    /**
     * Here you can define one or multiple SQL statements that should be executed during the update.
     * @return Updater\Migration[]
     */
    public function getMigrations(Updater $updater)
    {
        $optionTable = Common::prefixTable('option');
        $removeEmptyDefaultReportsSql = "delete from `$optionTable` where option_name like '%defaultReport%' and option_value=''";

        return array(
            $this->migration->db->sql($removeEmptyDefaultReportsSql)
        );
    }

    /**
     * Here you can define any action that should be performed during the update. For instance executing SQL statements,
     * renaming config entries, updating files, etc.
     */
    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }
}
