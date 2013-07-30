<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_Live
 */
use Piwik\Plugin;
use Piwik\WidgetsList;

/**
 *
 * @package Piwik_Live
 */
class Piwik_Live extends Plugin
{
    /**
     * @see Piwik_Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
    {
        return array(
            'AssetManager.getJsFiles'  => 'getJsFiles',
            'AssetManager.getCssFiles' => 'getCssFiles',
            'WidgetsList.add'          => 'addWidget',
            'Menu.add'                 => 'addMenu',
        );
    }

    public function getCssFiles(&$cssFiles)
    {
        $cssFiles[] = "plugins/Live/stylesheets/live.less";
    }

    public function getJsFiles(&$jsFiles)
    {
        $jsFiles[] = "plugins/Live/javascripts/live.js";
    }

    function addMenu()
    {
        Piwik_AddMenu('General_Visitors', 'Live_VisitorLog', array('module' => 'Live', 'action' => 'indexVisitorLog'), true, $order = 5);
    }

    public function addWidget()
    {
        WidgetsList::add('Live!', 'Live_VisitorsInRealTime', 'Live', 'widget');
        WidgetsList::add('Live!', 'Live_VisitorLog', 'Live', 'getVisitorLog');
        WidgetsList::add('Live!', 'Live_RealTimeVisitorCount', 'Live', 'getSimpleLastVisitCount');
    }

}
