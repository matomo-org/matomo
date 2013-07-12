<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_CoreHome
 */

/**
 *
 * @package Piwik_CoreHome
 */
class Piwik_CoreHome extends Piwik_Plugin
{
    /**
     * @see Piwik_Plugin::getListHooksRegistered
     */
    public function getInformation()
    {
        return array(
            'description'     => Piwik_Translate('CoreHome_PluginDescription'),
            'author'          => 'Piwik',
            'author_homepage' => 'http://piwik.org/',
            'version'         => Piwik_Version::VERSION,
        );
    }

    /**
     * @see Piwik_Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
    {
        return array(
            'AssetManager.getCssFiles' => 'getCssFiles',
            'AssetManager.getJsFiles'  => 'getJsFiles',
            'WidgetsList.add'          => 'addWidgets',
        );
    }

    /**
     * Adds the donate form widget.
     */
    public function addWidgets()
    {
        Piwik_AddWidget('Example Widgets', 'CoreHome_SupportPiwik', 'CoreHome', 'getDonateForm');
        Piwik_AddWidget('Example Widgets', 'Installation_Welcome', 'CoreHome', 'getPromoVideo');
    }

    public function getCssFiles(&$cssFiles)
    {
        $cssFiles[] = "libs/jquery/themes/base/jquery-ui.css";
        $cssFiles[] = "plugins/Zeitgeist/stylesheets/common.css";
        $cssFiles[] = "plugins/CoreHome/stylesheets/corehome.css";
        $cssFiles[] = "plugins/CoreHome/stylesheets/menu.css";
        $cssFiles[] = "plugins/CoreHome/stylesheets/datatable.css";
        $cssFiles[] = "plugins/CoreHome/stylesheets/cloud.css";
        $cssFiles[] = "plugins/CoreHome/stylesheets/jquery.ui.autocomplete.css";
        $cssFiles[] = "plugins/CoreHome/stylesheets/jqplot.css";
        $cssFiles[] = "plugins/CoreHome/stylesheets/donate.css";
        $cssFiles[] = "plugins/CoreHome/stylesheets/promo.css";
    }

    public function getJsFiles(&$jsFiles)
    {
        $jsFiles[] = "libs/jquery/jquery.js";
        $jsFiles[] = "libs/jquery/jquery-ui.js";
        $jsFiles[] = "libs/jquery/jquery.browser.js";
        $jsFiles[] = "libs/jquery/jquery.truncate.js";
        $jsFiles[] = "libs/jquery/jquery.scrollTo.js";
        $jsFiles[] = "libs/jquery/jquery.history.js";
        $jsFiles[] = "libs/javascript/sprintf.js";
        $jsFiles[] = "plugins/Zeitgeist/javascripts/piwikHelper.js";
        $jsFiles[] = "plugins/Zeitgeist/javascripts/ajaxHelper.js";
        $jsFiles[] = "plugins/CoreHome/javascripts/datatable.js";
        $jsFiles[] = "plugins/CoreHome/javascripts/datatable_rowactions.js";
        $jsFiles[] = "plugins/CoreHome/javascripts/popover.js";
        $jsFiles[] = "plugins/CoreHome/javascripts/broadcast.js";
        $jsFiles[] = "plugins/CoreHome/javascripts/menu.js";
        $jsFiles[] = "plugins/CoreHome/javascripts/menu_init.js";
        $jsFiles[] = "plugins/CoreHome/javascripts/calendar.js";
        $jsFiles[] = "plugins/CoreHome/javascripts/autocomplete.js";
        $jsFiles[] = "plugins/CoreHome/javascripts/sparkline.js";
        $jsFiles[] = "plugins/CoreHome/javascripts/corehome.js";
        $jsFiles[] = "plugins/CoreHome/javascripts/datatable_manager.js";
        $jsFiles[] = "plugins/CoreHome/javascripts/donate.js";
        $jsFiles[] = "plugins/CoreHome/javascripts/jqplot.js";
        $jsFiles[] = "libs/jqplot/jqplot-custom.min.js";
        $jsFiles[] = "plugins/CoreHome/javascripts/promo.js";
    }

}
