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
    /**
     * @see Piwik_Plugin::getInformation
     */
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

    static public function insertMapInLocationReport(&$out)
    {
        $out = '<h2>' . Piwik_Translate('UserCountryMap_VisitorMap') . '</h2>';
        $out .= Piwik_FrontController::getInstance()->fetchDispatch('UserCountryMap', 'visitorMap');
    }

    /**
     * @see Piwik_Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
    {
        $hooks = array(
            'AssetManager.getJsFiles'  => 'getJsFiles',
            'AssetManager.getCssFiles' => 'getCssFiles',
            'Menu.add'                 => 'addMenu',
        );
        return $hooks;
    }

    public function addMenu()
    {
        Piwik_AddMenu('General_Visitors', 'UserCountryMap_RealTimeMap', array('module' => 'UserCountryMap', 'action' => 'realtimeWorldMap'), true, $order = 70);
    }

    public function getJsFiles(&$jsFiles)
    {
        $jsFiles[] = "plugins/UserCountryMap/javascripts/vendor/raphael.min.js";
        $jsFiles[] = "plugins/UserCountryMap/javascripts/vendor/jquery.qtip.min.js";
        $jsFiles[] = "plugins/UserCountryMap/javascripts/vendor/kartograph.min.js";
        $jsFiles[] = "plugins/UserCountryMap/javascripts/vendor/chroma.min.js";
        $jsFiles[] = "plugins/UserCountryMap/javascripts/visitor-map.js";
        $jsFiles[] = "plugins/UserCountryMap/javascripts/realtime-map.js";
    }

    public function getCssFiles(&$cssFiles)
    {
        $cssFiles[] = "plugins/UserCountryMap/stylesheets/visitor-map.css";
        $cssFiles[] = "plugins/UserCountryMap/stylesheets/realtime-map.css";
    }
}
