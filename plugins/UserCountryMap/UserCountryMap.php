<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package UserCountryMap
 */
namespace Piwik\Plugins\UserCountryMap;

use Piwik\FrontController;
use Piwik\Menu\MenuMain;
use Piwik\Piwik;
use Piwik\Version;
use Piwik\WidgetsList;

/**
 * @package UserCountryMap
 */
class UserCountryMap extends \Piwik\Plugin
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
            'version'         => Version::VERSION,
            'license'          => 'GPL v3+',
            'license_homepage' => 'http://www.gnu.org/licenses/gpl.html'
        );
    }

    public function postLoad()
    {
        WidgetsList::add('General_Visitors', Piwik::translate('UserCountryMap_VisitorMap'), 'UserCountryMap', 'visitorMap');
        WidgetsList::add('Live!', Piwik::translate('UserCountryMap_RealTimeMap'), 'UserCountryMap', 'realtimeMap');

        Piwik::addAction('Template.leftColumnUserCountry', array('Piwik\Plugins\UserCountryMap\UserCountryMap', 'insertMapInLocationReport'));
    }

    static public function insertMapInLocationReport(&$out)
    {
        $out = '<h2>' . Piwik::translate('UserCountryMap_VisitorMap') . '</h2>';
        $out .= FrontController::getInstance()->fetchDispatch('UserCountryMap', 'visitorMap');
    }

    public function getListHooksRegistered()
    {
        $hooks = array(
            'AssetManager.getJavaScriptFiles' => 'getJsFiles',
            'AssetManager.getStylesheetFiles' => 'getStylesheetFiles',
            'Menu.Reporting.addItems'         => 'addMenu',
        );
        return $hooks;
    }

    public function addMenu()
    {
        MenuMain::getInstance()->add('General_Visitors', 'UserCountryMap_RealTimeMap', array('module' => 'UserCountryMap', 'action' => 'realtimeWorldMap'), true, $order = 70);
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

    public function getStylesheetFiles(&$stylesheets)
    {
        $stylesheets[] = "plugins/UserCountryMap/stylesheets/visitor-map.less";
        $stylesheets[] = "plugins/UserCountryMap/stylesheets/realtime-map.less";
    }
}
