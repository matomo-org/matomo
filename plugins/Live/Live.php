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


use Piwik\Common;
use Piwik\Menu\MenuMain;
use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\CoreVisualizations\Visualizations\HtmlTable;
use Piwik\WidgetsList;

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
            'ViewDataTable.configure'                => 'configureViewDataTable',
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
        WidgetsList::add('Live!', 'Live_VisitorLog', 'Live', 'getVisitorLog');
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

    public function configureViewDataTable(ViewDataTable $view)
    {
        switch ($view->requestConfig->apiMethodToRequestDataTable) {
            case 'Live.getLastVisitsDetails':
                $this->configureViewForGetLastVisitsDetails($view);
                break;
        }
    }

    public function getDefaultTypeViewDataTable(&$defaultViewTypes)
    {
        $defaultViewTypes['Live.getLastVisitsDetails'] = VisitorLog::ID;
    }

    private function configureViewForGetLastVisitsDetails(ViewDataTable $view)
    {
        $view->config->disable_generic_filters = true;
        $view->config->enable_sort = false;
        $view->config->show_search = false;
        $view->config->show_exclude_low_population = false;
        $view->config->show_offset_information     = false;
        $view->config->show_all_views_icons      = false;
        $view->config->show_table_all_columns      = false;
        $view->config->show_export_as_rss_feed     = false;

        $view->requestConfig->filter_sort_column = 'idVisit';
        $view->requestConfig->filter_sort_order  = 'asc';
        $view->requestConfig->filter_limit       = 20;

        $view->config->documentation = Piwik::translate('Live_VisitorLogDocumentation', array('<br />', '<br />'));
        $view->config->custom_parameters = array(
            // set a very high row count so that the next link in the footer of the data table is always shown
            'totalRows'         => 10000000,

            'filterEcommerce'   => Common::getRequestVar('filterEcommerce', 0, 'int'),
            'pageUrlNotDefined' => Piwik::translate('General_NotDefined', Piwik::translate('Actions_ColumnPageURL'))
        );

        $view->config->footer_icons = array(
            array(
                'class'   => 'tableAllColumnsSwitch',
                'buttons' => array(
                    array(
                        'id'    => 'Piwik\\Plugins\\Live\\VisitorLog',
                        'title' => Piwik::translate('Live_LinkVisitorLog'),
                        'icon'  => 'plugins/Zeitgeist/images/table.png'
                    )
                )
            )
        );

        if ($view->isViewDataTableId(HtmlTable::ID)) {
            $view->config->disable_row_actions = true;
        }
    }
}