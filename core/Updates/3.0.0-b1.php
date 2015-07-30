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
 * Update for version 3.0.0-b1.
 */
class Updates_3_0_0_b1 extends Updates
{
    /**
     * Here you can define one or multiple SQL statements that should be executed during the update.
     * @return array
     */
    public function getMigrationQueries(Updater $updater)
    {
        $logVisitTable = Common::prefixTable('log_visit');

        return array(
            'DROP INDEX index_idsite_config_datetime ON `' . $logVisitTable . '`' => 1091,
            'DROP INDEX index_idsite_datetime ON `' . $logVisitTable . '`' => 1091,
            'CREATE INDEX index_idsite_datetime_configid ON `' . $logVisitTable . '`(idsite, visit_last_action_time, config_id)' => 1061,
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
