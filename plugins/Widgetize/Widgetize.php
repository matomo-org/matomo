<?php

/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_Widgetize
 */

/**
 *
 * @package Piwik_Widgetize
 */
class Piwik_Widgetize extends Piwik_Plugin
{
    /**
     * @see Piwik_Plugin::getInformation
     */
    public function getInformation()
    {
        return array(
            'description'     => Piwik_Translate('Widgetize_PluginDescription'),
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
            'AssetManager.getJsFiles'  => 'getJsFiles',
            'AssetManager.getCssFiles' => 'getCssFiles',
            'TopMenu.add'              => 'addTopMenu',
        );
    }

    public function addTopMenu()
    {
        $tooltip = Piwik_Translate('Widgetize_TopLinkTooltip');
        $urlParams = array('module' => 'Widgetize', 'action' => 'index', 'segment' => false);

        Piwik_AddTopMenu('General_Widgets', $urlParams, true, 5, $isHTML = false, $tooltip);
    }

    public function getJsFiles(&$jsFiles)
    {
        $jsFiles[] = "libs/jquery/jquery.truncate.js";
        $jsFiles[] = "libs/jquery/jquery.scrollTo.js";
        $jsFiles[] = "plugins/Zeitgeist/javascripts/piwikHelper.js";
        $jsFiles[] = "plugins/CoreHome/javascripts/datatable.js";
        $jsFiles[] = "plugins/Dashboard/javascripts/widgetMenu.js";
        $jsFiles[] = "plugins/Widgetize/javascripts/widgetize.js";
    }

    public function getCssFiles(&$cssFiles)
    {
        $cssFiles[] = "plugins/Widgetize/stylesheets/widgetize.css";
        $cssFiles[] = "plugins/CoreHome/stylesheets/corehome.css";
        $cssFiles[] = "plugins/CoreHome/stylesheets/datatable.css";
        $cssFiles[] = "plugins/CoreHome/stylesheets/cloud.css";
        $cssFiles[] = "plugins/Dashboard/stylesheets/dashboard.css";
    }
}
