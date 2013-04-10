<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_Live
 */

/**
 *
 * @package Piwik_Live
 */
class Piwik_Live extends Piwik_Plugin
{
    public function getInformation()
    {
        return array(
            'description'     => Piwik_Translate('Live_PluginDescription'),
            'author'          => 'Piwik',
            'author_homepage' => 'http://piwik.org/',
            'version'         => Piwik_Version::VERSION,
        );
    }

    function getListHooksRegistered()
    {
        return array(
            'AssetManager.getJsFiles'  => 'getJsFiles',
            'AssetManager.getCssFiles' => 'getCssFiles',
            'WidgetsList.add'          => 'addWidget',
            'Menu.add'                 => 'addMenu',
        );
    }

    /**
     * @param Piwik_Event_Notification $notification  notification object
     */
    function getCssFiles($notification)
    {
        $cssFiles = & $notification->getNotificationObject();

        $cssFiles[] = "plugins/Live/templates/live.css";
    }

    /**
     * @param Piwik_Event_Notification $notification  notification object
     */
    function getJsFiles($notification)
    {
        $jsFiles = & $notification->getNotificationObject();

        $jsFiles[] = "plugins/Live/templates/scripts/live.js";
    }

    function addMenu()
    {
        Piwik_AddMenu('General_Visitors', 'Live_VisitorLog', array('module' => 'Live', 'action' => 'getVisitorLog'), true, $order = 5);
    }

    public function addWidget()
    {
        Piwik_AddWidget('Live!', 'Live_VisitorsInRealTime', 'Live', 'widget');
        Piwik_AddWidget('Live!', 'Live_VisitorLog', 'Live', 'getVisitorLog');
        Piwik_AddWidget('Live!', 'Live_RealTimeVisitorCount', 'Live', 'getSimpleLastVisitCount');
    }

}
