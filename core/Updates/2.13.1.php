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
use Piwik\Updater;
use Piwik\Updates;

/**
 * Update for version 2.13.1.
 */
class Updates_2_13_1 extends Updates
{
    /**
     * Here you can define one or multiple SQL statements that should be executed during the update.
     * @return array
     */
    public function getMigrationQueries(Updater $updater)
    {
        $optionTable = Common::prefixTable('option');
        $removeEmptyDefaultReportsSql = "delete from `$optionTable` where option_name like '%defaultReport%' and option_value=''";

        return array(
            $removeEmptyDefaultReportsSql => false
        );
    }

    /**
     * Here you can define any action that should be performed during the update. For instance executing SQL statements,
     * renaming config entries, updating files, etc.
     */
    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrationQueries(__FILE__, $this->getMigrationQueries($updater));
    }
}
