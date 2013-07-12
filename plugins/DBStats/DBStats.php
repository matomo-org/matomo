<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_DBStats
 */

/**
 *
 * @package Piwik_DBStats
 */
class Piwik_DBStats extends Piwik_Plugin
{
    const TIME_OF_LAST_TASK_RUN_OPTION = 'dbstats_time_of_last_cache_task_run';

    /**
     * @see Piwik_Plugin::getInformation
     */
    public function getInformation()
    {
        return array(
            'description'     => Piwik_Translate('DBStats_PluginDescription'),
            'author'          => 'Piwik',
            'author_homepage' => 'http://piwik.org/',
            'version'         => Piwik_Version::VERSION,
        );
    }

    /**
     * @see Piwik_Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
    {
        return array(
            'AssetManager.getCssFiles'        => 'getCssFiles',
            'AdminMenu.add'                   => 'addMenu',
            'TaskScheduler.getScheduledTasks' => 'getScheduledTasks',
        );
    }

    function addMenu()
    {
        Piwik_AddAdminSubMenu('CoreAdminHome_MenuDiagnostic', 'DBStats_DatabaseUsage',
            array('module' => 'DBStats', 'action' => 'index'),
            Piwik::isUserIsSuperUser(),
            $order = 9);
    }

    /**
     * Gets all scheduled tasks executed by this plugin.
     */
    public function getScheduledTasks(&$tasks)
    {
        $cacheDataByArchiveNameReportsTask = new Piwik_ScheduledTask(
            $this,
            'cacheDataByArchiveNameReports',
            null,
            new Piwik_ScheduledTime_Weekly(),
            Piwik_ScheduledTask::LOWEST_PRIORITY
        );
        $tasks[] = $cacheDataByArchiveNameReportsTask;
    }

    /**
     * Caches the intermediate DataTables used in the getIndividualReportsSummary and
     * getIndividualMetricsSummary reports in the option table.
     */
    public function cacheDataByArchiveNameReports()
    {
        $api = Piwik_DBStats_API::getInstance();
        $api->getIndividualReportsSummary(true);
        $api->getIndividualMetricsSummary(true);

        $now = Piwik_Date::now()->getLocalized("%longYear%, %shortMonth% %day%");
        Piwik_SetOption(self::TIME_OF_LAST_TASK_RUN_OPTION, $now);
    }

    public function getCssFiles(&$cssFiles)
    {
        $cssFiles[] = "plugins/DBStats/stylesheets/dbstatsTable.css";
    }

    /** Returns the date when the cacheDataByArchiveNameReports was last run. */
    public static function getDateOfLastCachingRun()
    {
        return Piwik_GetOption(self::TIME_OF_LAST_TASK_RUN_OPTION);
    }
}
