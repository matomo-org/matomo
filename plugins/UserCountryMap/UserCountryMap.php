<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UserCountryMap;

class UserCountryMap extends \Piwik\Plugin
{
    public function registerEvents()
    {
        $hooks = array(
            'AssetManager.getJavaScriptFiles' => 'getJsFiles',
            'AssetManager.getStylesheetFiles' => 'getStylesheetFiles',
            'Translate.getClientSideTranslationKeys' => 'getClientSideTranslationKeys',
            'API.getPagesComparisonsDisabledFor'     => 'getPagesComparisonsDisabledFor',
        );
        return $hooks;
    }

    public function getPagesComparisonsDisabledFor(&$pages)
    {
        $pages[] = 'General_Visitors.UserCountryMap_RealTimeMap';
    }

    public function getJsFiles(&$jsFiles)
    {
        $jsFiles[] = "node_modules/visibilityjs/lib/visibility.core.js";
        $jsFiles[] = "plugins/UserCountryMap/javascripts/vendor/raphael.min.js";
        $jsFiles[] = "plugins/UserCountryMap/javascripts/vendor/jquery.qtip.min.js";
        $jsFiles[] = "plugins/UserCountryMap/javascripts/vendor/kartograph.min.js";
        $jsFiles[] = "node_modules/chroma-js/chroma.min.js";
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
        $translationKeys[] = 'UserCountryMap_Unlocated';
        $translationKeys[] = 'UserCountryMap_WithUnknownRegion';
        $translationKeys[] = 'UserCountryMap_WithUnknownCity';
        $translationKeys[] = 'General_UserId';
        $translationKeys[] = 'UserCountryMap_VisitorMap';
        $translationKeys[] = 'CoreHome_ThereIsNoDataForThisReport';
        $translationKeys[] = 'General_LoadingData';
        $translationKeys[] = 'UserCountryMap_Regions';
        $translationKeys[] = 'UserCountryMap_Countries';
        $translationKeys[] = 'UserCountryMap_Cities';
        $translationKeys[] = 'UserCountryMap_WorldWide';
        $translationKeys[] = 'UserCountryMap_ShowingVisits';
        $translationKeys[] = 'UserCountryMap_NoVisitsInfo';
        $translationKeys[] = 'UserCountryMap_NoVisitsInfo2';
    }
}
