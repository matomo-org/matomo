<?php

/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\Widgetize;
use Piwik\Menu\MenuTop;
use Piwik\Piwik;

/**
 *
 */
class Widgetize extends \Piwik\Plugin
{
    /**
     * @see Piwik\Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
    {
        return array(
            'AssetManager.getJavaScriptFiles'        => 'getJsFiles',
            'AssetManager.getStylesheetFiles'        => 'getStylesheetFiles',
            'Menu.Top.addItems'                      => 'addTopMenu',
            'Translate.getClientSideTranslationKeys' => 'getClientSideTranslationKeys'
        );
    }

    public function addTopMenu()
    {
        $tooltip = Piwik::translate('Widgetize_TopLinkTooltip');
        $urlParams = array('module' => 'Widgetize', 'action' => 'index', 'segment' => false);

        MenuTop::addEntry('General_Widgets', $urlParams, true, 5, $isHTML = false, $tooltip);
    }

    public function getJsFiles(&$jsFiles)
    {
        $jsFiles[] = "libs/jquery/jquery.truncate.js";
        $jsFiles[] = "libs/jquery/jquery.scrollTo.js";
        $jsFiles[] = "plugins/Zeitgeist/javascripts/piwikHelper.js";
        $jsFiles[] = "plugins/CoreHome/javascripts/dataTable.js";
        $jsFiles[] = "plugins/Dashboard/javascripts/widgetMenu.js";
        $jsFiles[] = "plugins/Widgetize/javascripts/widgetize.js";
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
    }
}
