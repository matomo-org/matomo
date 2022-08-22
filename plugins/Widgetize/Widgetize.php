<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\Widgetize;

class Widgetize extends \Piwik\Plugin
{
    /**
     * @see \Piwik\Plugin::registerEvents
     */
    public function registerEvents()
    {
        return array(
            'AssetManager.getJavaScriptFiles'        => 'getJsFiles',
            'AssetManager.getStylesheetFiles'        => 'getStylesheetFiles',
            'Translate.getClientSideTranslationKeys' => 'getClientSideTranslationKeys'
        );
    }

    public function getJsFiles(&$jsFiles)
    {
        $jsFiles[] = "node_modules/jquery.scrollto/jquery.scrollTo.min.js";
        $jsFiles[] = "plugins/Morpheus/javascripts/piwikHelper.js";
        $jsFiles[] = "plugins/CoreHome/javascripts/dataTable.js";
        $jsFiles[] = "plugins/Dashboard/javascripts/widgetMenu.js";
    }

    public function getStylesheetFiles(&$stylesheets)
    {
        $stylesheets[] = "plugins/Widgetize/stylesheets/widgetize.less";
        $stylesheets[] = "plugins/CoreHome/stylesheets/coreHome.less";
        $stylesheets[] = "plugins/CoreHome/stylesheets/dataTable.less";
        $stylesheets[] = "plugins/CoreHome/stylesheets/cloud.less";
        $stylesheets[] = "plugins/Dashboard/stylesheets/dashboard.less";
    }

    public function getClientSideTranslationKeys(&$translations)
    {
        $translations[] = 'Widgetize_OpenInNewWindow';
        $translations[] = 'Dashboard_LoadingWidget';
        $translations[] = 'Widgetize_TooHighAccessLevel';
        $translations[] = 'Widgetize_SelectAReport';
        $translations[] = 'Widgetize_Reports';
        $translations[] = 'Widgetize_Intro';
        $translations[] = 'Widgetize_DisplayDashboardInIframe';
        $translations[] = 'Widgetize_DisplayDashboardInIframeAllSites';
        $translations[] = 'Widgetize_ViewableAnonymously';
        $translations[] = 'Widgetize_EmbedIframe';
        $translations[] = 'Widgetize_DirectLink';
    }
}
