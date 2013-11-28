<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Live
 */
namespace Piwik\Plugins\Live;

use Piwik\Menu\MenuMain;
use Piwik\Plugins\CoreVisualizations\Visualizations\HtmlTable;
use Piwik\WidgetsList;

require_once PIWIK_INCLUDE_PATH . '/plugins/Live/VisitorLog.php';

/**
 *
 * @package Live
 */
class Live extends \Piwik\Plugin
{

    /**
     * @see Piwik_Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
    {
        return array(
            'AssetManager.getJavaScriptFiles'        => 'getJsFiles',
            'AssetManager.getStylesheetFiles'        => 'getStylesheetFiles',
            'WidgetsList.addWidgets'                 => 'addWidget',
            'Menu.Reporting.addItems'                => 'addMenu',
            'Translate.getClientSideTranslationKeys' => 'getClientSideTranslationKeys',
            'ViewDataTable.getDefaultType'           => 'getDefaultTypeViewDataTable'
        );
    }

    public function getStylesheetFiles(&$stylesheets)
    {
        $stylesheets[] = "plugins/Live/stylesheets/live.less";
        $stylesheets[] = "plugins/Live/stylesheets/visitor_profile.less";
    }

    public function getJsFiles(&$jsFiles)
    {
        $jsFiles[] = "plugins/Live/javascripts/live.js";
        $jsFiles[] = "plugins/Live/javascripts/visitorProfile.js";
        $jsFiles[] = "plugins/Live/javascripts/visitorLog.js";
    }

    public function addMenu()
    {
        MenuMain::getInstance()->add('General_Visitors', 'Live_VisitorLog', array('module' => 'Live', 'action' => 'indexVisitorLog'), true, $order = 5);
    }

    public function addWidget()
    {
        WidgetsList::add('Live!', 'Live_VisitorsInRealTime', 'Live', 'widget');
        WidgetsList::add('Live!', 'Live_VisitorLog', 'Live', 'getVisitorLog', array('small' => 1));
        WidgetsList::add('Live!', 'Live_RealTimeVisitorCount', 'Live', 'getSimpleLastVisitCount');
        WidgetsList::add('Live!', 'Live_VisitorProfile', 'Live', 'getVisitorProfilePopup');
    }

    public function getClientSideTranslationKeys(&$translationKeys)
    {
        $translationKeys[] = "Live_VisitorProfile";
        $translationKeys[] = "Live_NoMoreVisits";
        $translationKeys[] = "Live_ShowMap";
        $translationKeys[] = "Live_HideMap";
        $translationKeys[] = "Live_PageRefreshed";
    }

    public function getDefaultTypeViewDataTable(&$defaultViewTypes)
    {
        $defaultViewTypes['Live.getLastVisitsDetails'] = VisitorLog::ID;
    }
}