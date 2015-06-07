<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserCountryMap;

use Piwik\Container\StaticContainer;
use Piwik\FrontController;
use Piwik\Piwik;
use Piwik\WidgetsList;
use Piwik\Plugin\Manager as PluginManager;

/**
 */
class UserCountryMap extends \Piwik\Plugin
{
    public function insertMapInLocationReport(&$out)
    {
        /** @var FrontController $frontController */
        $frontController = StaticContainer::get('Piwik\FrontController');

        $out = '<h2>' . Piwik::translate('UserCountryMap_VisitorMap') . '</h2>';
        $out .= $frontController->fetchDispatch('UserCountryMap', 'visitorMap');
    }

    public function getListHooksRegistered()
    {
        $hooks = array(
            'AssetManager.getJavaScriptFiles' => 'getJsFiles',
            'AssetManager.getStylesheetFiles' => 'getStylesheetFiles',
            'Translate.getClientSideTranslationKeys' => 'getClientSideTranslationKeys',
            'Platform.initialized' => array(
                'after'    => true,
                'function' => 'registerWidgets'
            ),
            'Template.leftColumnUserCountry' => 'insertMapInLocationReport',
        );
        return $hooks;
    }

    public function registerWidgets()
    {
        if (PluginManager::getInstance()->isPluginActivated('UserCountry')) {
            WidgetsList::add('General_Visitors', Piwik::translate('UserCountryMap_VisitorMap'), 'UserCountryMap', 'visitorMap');
            WidgetsList::add('Live!', Piwik::translate('UserCountryMap_RealTimeMap'), 'UserCountryMap', 'realtimeMap');
        }
    }

    public function getJsFiles(&$jsFiles)
    {
        $jsFiles[] = "libs/bower_components/visibilityjs/lib/visibility.core.js";
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
