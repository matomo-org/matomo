<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserCountryMap;

use Piwik\FrontController;
use Piwik\Piwik;
use Piwik\Version;
use Piwik\WidgetsList;
use Piwik\Plugin\Manager as PluginManager;

/**
 */
class UserCountryMap extends \Piwik\Plugin
{
    /**
     * @see Piwik\Plugin::getInformation
     */
    public function getInformation()
    {
        return array(
            'name'             => 'User Country Map',
            'description'      => 'This plugin provides the widgets Visitor Map and Real-time Map. Note: Requires the UserCountry plugin enabled.',
            'authors'          => array(array('name' => 'Piwik', 'homepage' => 'http://piwik.org/')),
            'version'          => Version::VERSION,
            'license'          => 'GPL v3+',
            'license_homepage' => 'http://www.gnu.org/licenses/gpl.html'
        );
    }

    public function postLoad()
    {
        if (PluginManager::getInstance()->isPluginActivated('UserCountry')) {
            WidgetsList::add('General_Visitors', Piwik::translate('UserCountryMap_VisitorMap'), 'UserCountryMap', 'visitorMap');
            WidgetsList::add('Live!', Piwik::translate('UserCountryMap_RealTimeMap'), 'UserCountryMap', 'realtimeMap');
        }

        Piwik::addAction('Template.leftColumnUserCountry', array('Piwik\Plugins\UserCountryMap\UserCountryMap', 'insertMapInLocationReport'));
    }

    public static function insertMapInLocationReport(&$out)
    {
        $out = '<h2>' . Piwik::translate('UserCountryMap_VisitorMap') . '</h2>';
        $out .= FrontController::getInstance()->fetchDispatch('UserCountryMap', 'visitorMap');
    }

    public function getListHooksRegistered()
    {
        $hooks = array(
            'AssetManager.getJavaScriptFiles' => 'getJsFiles',
            'AssetManager.getStylesheetFiles' => 'getStylesheetFiles',
            'Translate.getClientSideTranslationKeys' => 'getClientSideTranslationKeys'
        );
        return $hooks;
    }

    public function getJsFiles(&$jsFiles)
    {
        $jsFiles[] = "plugins/UserCountryMap/javascripts/vendor/raphael.min.js";
        $jsFiles[] = "plugins/UserCountryMap/javascripts/vendor/jquery.qtip.min.js";
        $jsFiles[] = "plugins/UserCountryMap/javascripts/vendor/kartograph.min.js";
        $jsFiles[] = "libs/bower_components/chroma-js/chroma.min.js";
        $jsFiles[] = "plugins/UserCountryMap/javascripts/visitor-map.js";
        $jsFiles[] = "plugins/UserCountryMap/javascripts/realtime-map.js";
    }

    public function getStylesheetFiles(&$stylesheets)
    {
        $stylesheets[] = "plugins/UserCountryMap/stylesheets/visitor-map.less";
        $stylesheets[] = "plugins/UserCountryMap/stylesheets/realtime-map.less";
    }

    public function getClientSideTranslationKeys(&$translationKeys)
    {
        $translationKeys[] = 'UserCountryMap_WithUnknownRegion';
        $translationKeys[] = 'UserCountryMap_WithUnknownCity';
        $translationKeys[] = 'General_UserId';
    }
}
