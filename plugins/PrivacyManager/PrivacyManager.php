<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 *
 * @category Piwik_Plugins
 * @package Piwik_PrivacyManager
 */

/**
 *
 * @package Piwik_PrivacyManager
 */
class Piwik_PrivacyManager extends Piwik_Plugin
{
    const OPTION_LAST_DELETE_PIWIK_LOGS = "lastDelete_piwik_logs";
    const OPTION_LAST_DELETE_PIWIK_LOGS_INITIAL = "lastDelete_piwik_logs_initial";
    const DELETE_MAX_ROWS_MULTIPLICATOR = 1000;

    public function getInformation()
    {
        return array(
            'description' => Piwik_Translate('PrivacyManager_PluginDescription'),
            'author' => 'Piwik',
            'author_homepage' => 'http://piwik.org/',
            'version' => Piwik_Version::VERSION,
        );
    }

    public function getListHooksRegistered()
    {
        return array(
            'AssetManager.getJsFiles' => 'getJsFiles',
            'AdminMenu.add' => 'addMenu',
            'TaskScheduler.getScheduledTasks' => 'getScheduledTasks',
        );
    }

    function getScheduledTasks($notification)
    {
        $tasks = &$notification->getNotificationObject();
        $deleteLogTablesTask = new Piwik_ScheduledTask ($this,
            'deleteLogTables',
            new Piwik_ScheduledTime_Daily());
        $tasks[] = $deleteLogTablesTask;
    }

    function getJsFiles($notification)
    {
        $jsFiles = &$notification->getNotificationObject();

        $jsFiles[] = "plugins/PrivacyManager/templates/privacySettings.js";
    }

    function addMenu()
    {
        Piwik_AddAdminMenu('PrivacyManager_MenuPrivacySettings',
                           array('module' => 'PrivacyManager', 'action' => 'privacySettings'),
                           Piwik::isUserHasSomeAdminAccess(),
                           $order = 8);
    }

    /*
     * @ToDo: return number of Rows deleted in last run; Display age of "oldest" row to help the user setting the day offset;
     */
    function deleteLogTables()
    {
        $deleteSettings = Piwik_Config::getInstance()->Deletelogs;

        //Make sure, log deletion is enabled
        if ($deleteSettings['delete_logs_enable'] == 0) {
            return;
        }

        //Log deletion may not run until it is once rescheduled (initial run). This is the only way to guarantee the calculated next scheduled deletion time.
        $initialDelete = Piwik_GetOption(self::OPTION_LAST_DELETE_PIWIK_LOGS_INITIAL);
        if (empty($initialDelete)) {
            Piwik_SetOption(self::OPTION_LAST_DELETE_PIWIK_LOGS_INITIAL, 1);
            return;
        }

        //Make sure, log purging is allowed to run now
        $lastDelete = Piwik_GetOption(self::OPTION_LAST_DELETE_PIWIK_LOGS);
        $deleteIntervalSeconds = $this->getDeleteIntervalInSeconds($deleteSettings['delete_logs_schedule_lowest_interval']);

        if ($lastDelete === false ||
            ($lastDelete !== false && ((int)$lastDelete + $deleteIntervalSeconds) <= time())
        ) {

            $maxIdVisit = $this->getDeleteIdVisitOffset($deleteSettings['delete_logs_older_than']);

            $logTables = $this->getDeleteTableLogTables();

            //set lastDelete time to today
            $date = Piwik_Date::factory("today");
            $lastDeleteDate = $date->getTimestamp();

            /*
             * Tell the DB that log deletion has run BEFORE deletion is executed;
             * If deletion / table optimization exceeds execution time, other tasks maybe prevented of being executed every time,
             * when the schedule is triggered.
             */
            Piwik_SetOption(self::OPTION_LAST_DELETE_PIWIK_LOGS, $lastDeleteDate);

            //Break if no ID was found (nothing to delete for given period)
            if (empty($maxIdVisit)) {
                return;
            }

            foreach ($logTables as $logTable) {
                $this->deleteRowsFromTable($logTable, $maxIdVisit, $deleteSettings['delete_max_rows_per_run'] * self::DELETE_MAX_ROWS_MULTIPLICATOR);
            }

            //optimize table overhead after deletion
            $query = "OPTIMIZE TABLE " . implode(",", $logTables);
            Piwik_Query($query);
        }
    }

    function getDeleteIntervalInSeconds($deleteInterval)
    {
        return (int)$deleteInterval * 24 * 60 * 60;
    }

    /*
     * get highest idVisit to delete rows from
     */
    function getDeleteIdVisitOffset($deleteLogsOlderThan)
    {
        $date = Piwik_Date::factory("today");
        $dateSubX = $date->subDay($deleteLogsOlderThan);

        $sql = "SELECT `idvisit` FROM " . Piwik_Common::prefixTable("log_visit")
               . " WHERE '" . $dateSubX->toString('Y-m-d H:i:s') . "' "
               . "> `visit_last_action_time` AND `idvisit` > 0 ORDER BY `idvisit` DESC LIMIT 1";

        $maxIdVisit = Piwik_FetchOne($sql);

        return $maxIdVisit;
    }

    function deleteRowsFromTable($table, $maxIdVisit, $maxRowsPerRun)
    {
        /*
         * @ToDo: check if DELETE ... tbl_name[.*] [, tbl_name[.*]] ... Statement performance is better (but LIMIT can't be used!). So for now, this is safer.
         * LOW_PRIORITY / QUICK / IGNORE read http://dev.mysql.com/doc/refman/5.0/en/delete.html
         */
        
        $sql = 'DELETE LOW_PRIORITY QUICK IGNORE FROM ' . $table . ' WHERE `idvisit` <= ? ';

        if(isset($maxRowsPerRun) && $maxRowsPerRun > 0) {
            $sql .=  ' LIMIT ' . (int)$maxRowsPerRun;
        }

        Piwik_Query($sql, array($maxIdVisit));
    }

    //let's hardcode, since these are no dynamically created tables
    //exclude piwik_log_action since it is a lookup table
    function getDeleteTableLogTables()
    {
        return array(Piwik_Common::prefixTable("log_conversion"),
                     Piwik_Common::prefixTable("log_link_visit_action"),
                     Piwik_Common::prefixTable("log_visit"),
                     Piwik_Common::prefixTable("log_conversion_item"));
    }
}
