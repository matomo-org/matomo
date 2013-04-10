<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_CoreAdminHome
 */

/**
 *
 * @package Piwik_CoreAdminHome
 */
class Piwik_CoreAdminHome extends Piwik_Plugin
{
    public function getInformation()
    {
        return array(
            'description'     => Piwik_Translate('CoreAdminHome_PluginDescription'),
            'author'          => 'Piwik',
            'author_homepage' => 'http://piwik.org/',
            'version'         => Piwik_Version::VERSION,
        );
    }

    public function getListHooksRegistered()
    {
        return array(
            'AssetManager.getCssFiles'        => 'getCssFiles',
            'AssetManager.getJsFiles'         => 'getJsFiles',
            'AdminMenu.add'                   => 'addMenu',
            'TaskScheduler.getScheduledTasks' => 'getScheduledTasks',
        );
    }

    /**
     * @param Piwik_Event_Notification $notification  notification object
     */
    function getScheduledTasks($notification)
    {
        $tasks = & $notification->getNotificationObject();

        // general data purge on older archive tables, executed daily
        $purgeArchiveTablesTask = new Piwik_ScheduledTask ($this,
            'purgeOutdatedArchives',
            null,
            new Piwik_ScheduledTime_Daily(),
            Piwik_ScheduledTask::HIGH_PRIORITY);
        $tasks[] = $purgeArchiveTablesTask;

        // lowest priority since tables should be optimized after they are modified
        $optimizeArchiveTableTask = new Piwik_ScheduledTask ($this,
            'optimizeArchiveTable',
            null,
            new Piwik_ScheduledTime_Daily(),
            Piwik_ScheduledTask::LOWEST_PRIORITY);
        $tasks[] = $optimizeArchiveTableTask;
    }

    /**
     * @param Piwik_Event_Notification $notification  notification object
     */
    function getCssFiles($notification)
    {
        $cssFiles = & $notification->getNotificationObject();

        $cssFiles[] = "libs/jquery/themes/base/jquery-ui.css";
        $cssFiles[] = "plugins/CoreAdminHome/templates/menu.css";
        $cssFiles[] = "themes/default/common.css";
        $cssFiles[] = "plugins/CoreAdminHome/templates/styles.css";
        $cssFiles[] = "plugins/CoreHome/templates/donate.css";
    }

    /**
     * @param Piwik_Event_Notification $notification  notification object
     */
    function getJsFiles($notification)
    {
        $jsFiles = & $notification->getNotificationObject();

        $jsFiles[] = "libs/jquery/jquery.js";
        $jsFiles[] = "libs/jquery/jquery-ui.js";
        $jsFiles[] = "libs/jquery/jquery.browser.js";
        $jsFiles[] = "libs/javascript/sprintf.js";
        $jsFiles[] = "themes/default/common.js";
        $jsFiles[] = "themes/default/ajaxHelper.js";
        $jsFiles[] = "libs/jquery/jquery.history.js";
        $jsFiles[] = "plugins/CoreHome/templates/broadcast.js";
        $jsFiles[] = "plugins/CoreAdminHome/templates/generalSettings.js";
        $jsFiles[] = "plugins/CoreHome/templates/donate.js";
    }

    function addMenu()
    {
        Piwik_AddAdminSubMenu('CoreAdminHome_MenuManage', NULL, "", Piwik::isUserHasSomeAdminAccess(), $order = 1);
        Piwik_AddAdminSubMenu('CoreAdminHome_MenuCommunity', NULL, "", Piwik::isUserHasSomeAdminAccess(), $order = 3);
        Piwik_AddAdminSubMenu('CoreAdminHome_MenuDiagnostic', NULL, "", Piwik::isUserHasSomeAdminAccess(), $order = 20);
        Piwik_AddAdminSubMenu('General_Settings', NULL, "", Piwik::isUserHasSomeAdminAccess(), $order = 5);
        Piwik_AddAdminSubMenu('General_Settings', 'CoreAdminHome_MenuGeneralSettings',
            array('module' => 'CoreAdminHome', 'action' => 'generalSettings'),
            Piwik::isUserHasSomeAdminAccess(),
            $order = 6);
        Piwik_AddAdminSubMenu('CoreAdminHome_MenuManage', 'CoreAdminHome_TrackingCode',
            array('module' => 'CoreAdminHome', 'action' => 'trackingCodeGenerator'),
            Piwik::isUserHasSomeAdminAccess(),
            $order = 4);

    }

    function purgeOutdatedArchives()
    {
        $archiveTables = Piwik::getTablesArchivesInstalled();
        foreach ($archiveTables as $table) {
            if (strpos($table, 'numeric') !== false) {
                Piwik_ArchiveProcessing_Period::doPurgeOutdatedArchives($table);
            }
        }
    }

    function optimizeArchiveTable()
    {
        $archiveTables = Piwik::getTablesArchivesInstalled();
        Piwik_OptimizeTables($archiveTables);
    }
}
