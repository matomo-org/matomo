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
            'AssetManager.getJavaScriptFiles'          => 'getJsFiles',
            'AssetManager.getStylesheetFiles'          => 'getStylesheetFiles',
            'WidgetsList.addWidgets'                   => 'addWidget',
            'Menu.Reporting.addItems'                  => 'addMenu',
            'Visualization.getReportDisplayProperties' => 'getReportDisplayProperties',
            'Translate.getClientSideTranslationKeys'   => 'getClientSideTranslationKeys',
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

    public function getReportDisplayProperties(&$properties)
    {
        $properties['Live.getLastVisitsDetails'] = $this->getDisplayPropertiesForGetLastVisitsDetails();
    }

    private function getDisplayPropertiesForGetLastVisitsDetails()
    {
        return array(
            'default_view_type'           => 'Piwik\\Plugins\\Live\\VisitorLog',
            'disable_generic_filters'     => true,
            'enable_sort'                 => false,
            'filter_sort_column'          => 'idVisit',
            'filter_sort_order'           => 'asc',
            'show_search'                 => false,
            'filter_limit'                => 20,
            'show_offset_information'     => false,
            'show_exclude_low_population' => false,
            'show_all_views_icons'        => false,
            'show_table_all_columns'      => false,
            'show_export_as_rss_feed'     => false,
            'documentation'               => Piwik::translate('Live_VisitorLogDocumentation', array('<br />', '<br />')),
            'custom_parameters'           => array(
                // set a very high row count so that the next link in the footer of the data table is always shown
                'totalRows'         => 10000000,

                'filterEcommerce'   => Common::getRequestVar('filterEcommerce', 0, 'int'),
                'pageUrlNotDefined' => Piwik::translate('General_NotDefined', Piwik::translate('Actions_ColumnPageURL'))
            ),
            'footer_icons'                => array(
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
            ),
            'visualization_properties'    => array(
                'table' => array(
                    'disable_row_actions' => true,
                )
            )
        );
    }
}