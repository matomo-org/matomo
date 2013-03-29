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
    public function getInformation()
    {
        return array(
            'description'     => Piwik_Translate('CoreHome_PluginDescription'),
            'author'          => 'Piwik',
            'author_homepage' => 'http://piwik.org/',
            'version'         => Piwik_Version::VERSION,
        );
    }

    function getListHooksRegistered()
    {
        return array(
            'AssetManager.getCssFiles' => 'getCssFiles',
            'AssetManager.getJsFiles'  => 'getJsFiles',
            'WidgetsList.add'          => 'addWidgets',
        );
    }

    /**
     * Adds the donate form widget.
     *
     * @param Piwik_Event_Notification $notification  notification object
     */
    public function addWidgets()
    {
        Piwik_AddWidget('Example Widgets', 'CoreHome_SupportPiwik', 'CoreHome', 'getDonateForm');
        Piwik_AddWidget('Example Widgets', 'Installation_Welcome', 'CoreHome', 'getPromoVideo');
    }

    /**
     * @param Piwik_Event_Notification $notification  notification object
     */
    function getCssFiles($notification)
    {
        $cssFiles = & $notification->getNotificationObject();

        $cssFiles[] = "libs/jquery/themes/base/jquery-ui.css";
        $cssFiles[] = "themes/default/common.css";
        $cssFiles[] = "plugins/CoreHome/templates/styles.css";
        $cssFiles[] = "plugins/CoreHome/templates/menu.css";
        $cssFiles[] = "plugins/CoreHome/templates/datatable.css";
        $cssFiles[] = "plugins/CoreHome/templates/cloud.css";
        $cssFiles[] = "plugins/CoreHome/templates/jquery.ui.autocomplete.css";
        $cssFiles[] = "plugins/CoreHome/templates/jqplot.css";
        $cssFiles[] = "plugins/CoreHome/templates/donate.css";
    }

    /**
     * @param Piwik_Event_Notification $notification  notification object
     */
    function getJsFiles($notification)
    {
        $jsFiles = & $notification->getNotificationObject();

        $jsFiles[] = "libs/jquery/jquery.js";
        $jsFiles[] = "libs/jquery/jquery-ui.js";
        $jsFiles[] = "libs/jquery/jquery.browser.js";
        $jsFiles[] = "libs/jquery/jquery.truncate.js";
        $jsFiles[] = "libs/jquery/jquery.scrollTo.js";
        $jsFiles[] = "libs/jquery/jquery.history.js";
        $jsFiles[] = "libs/javascript/sprintf.js";
        $jsFiles[] = "themes/default/common.js";
        $jsFiles[] = "themes/default/ajaxHelper.js";
        $jsFiles[] = "plugins/CoreHome/templates/datatable.js";
        $jsFiles[] = "plugins/CoreHome/templates/datatable_rowactions.js";
        $jsFiles[] = "plugins/CoreHome/templates/popover.js";
        $jsFiles[] = "plugins/CoreHome/templates/broadcast.js";
        $jsFiles[] = "plugins/CoreHome/templates/menu.js";
        $jsFiles[] = "plugins/CoreHome/templates/menu_init.js";
        $jsFiles[] = "plugins/CoreHome/templates/calendar.js";
        $jsFiles[] = "plugins/CoreHome/templates/date.js";
        $jsFiles[] = "plugins/CoreHome/templates/autocomplete.js";
        $jsFiles[] = "plugins/CoreHome/templates/sparkline.js";
        $jsFiles[] = "plugins/CoreHome/templates/misc.js";
        $jsFiles[] = "plugins/CoreHome/templates/datatable_manager.js";
        $jsFiles[] = "plugins/CoreHome/templates/donate.js";

        $jsFiles[] = "plugins/CoreHome/templates/jqplot.js";
        $jsFiles[] = "libs/jqplot/jqplot-custom.min.js";
    }

}
