<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_UserCountryMap
 */

/**
 *
 * @package Piwik_UserCountryMap
 */
class Piwik_UserCountryMap extends Piwik_Plugin
{
    public function getInformation()
    {
        return array(
            'name'            => 'User Country Map',
            'description'     => 'This plugin provides the widgets Visitor Map and Real-time Map. Note: Requires the UserCountry plugin enabled.',
            'author'          => 'Piwik',
            'author_homepage' => 'http://piwik.org/',
            'version'         => Piwik_Version::VERSION
        );
    }

    public function postLoad()
    {
        Piwik_AddWidget('General_Visitors', Piwik_Translate('UserCountryMap_VisitorMap'), 'UserCountryMap', 'visitorMap');
        Piwik_AddWidget('Live!', Piwik_Translate('UserCountryMap_RealTimeMap'), 'UserCountryMap', 'realtimeMap');

        Piwik_AddAction('template_leftColumnUserCountry', array('Piwik_UserCountryMap', 'insertMapInLocationReport'));
    }

    static public function insertMapInLocationReport($notification)
    {
        $out =& $notification->getNotificationObject();
        $out = '<h2>' . Piwik_Translate('UserCountryMap_VisitorMap') . '</h2>';
        $out .= Piwik_FrontController::getInstance()->fetchDispatch('UserCountryMap', 'visitorMap');
    }

    public function getListHooksRegistered()
    {
        $hooks = array(
            'AssetManager.getJsFiles'  => 'getJsFiles',
            'AssetManager.getCssFiles' => 'getCssFiles',
            'Menu.add'                 => 'addMenu',
        );
        return $hooks;
    }

    function addMenu()
    {
        Piwik_AddMenu('General_Visitors', 'UserCountryMap_RealTimeMap', array('module' => 'UserCountryMap', 'action' => 'realtimeWorldMap'), true, $order = 70);
    }

    /**
     * @param Piwik_Event_Notification $notification  notification object
     */
    public function getJsFiles($notification)
    {
        $jsFiles = & $notification->getNotificationObject();
        $jsFiles[] = "plugins/UserCountryMap/js/vendor/raphael.min.js";
        $jsFiles[] = "plugins/UserCountryMap/js/vendor/jquery.qtip.min.js";
        $jsFiles[] = "plugins/UserCountryMap/js/vendor/kartograph.min.js";
        $jsFiles[] = "plugins/UserCountryMap/js/vendor/chroma.min.js";
        $jsFiles[] = "plugins/UserCountryMap/js/visitor-map.js";
        $jsFiles[] = "plugins/UserCountryMap/js/realtime-map.js";
    }

    public function getCssFiles($notification)
    {
        $cssFiles = &$notification->getNotificationObject();
        $cssFiles[] = "plugins/UserCountryMap/css/visitor-map.css";
        $cssFiles[] = "plugins/UserCountryMap/css/realtime-map.css";
    }

}
