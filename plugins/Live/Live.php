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
            'AssetManager.getJsFiles'                  => 'getJsFiles',
            'AssetManager.getStylesheetFiles'                 => 'getStylesheetFiles',
            'WidgetsList.add'                          => 'addWidget',
            'Menu.add'                                 => 'addMenu',
            'ViewDataTable.getReportDisplayProperties' => 'getReportDisplayProperties',
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
        WidgetsList::add('Live!', 'Live_VisitorProfile', 'Live', 'getVisitorProfilePopup');
    }

    public function getReportDisplayProperties(&$properties)
    {
        $properties['Live.getLastVisitsDetails'] = $this->getDisplayPropertiesForGetLastVisitsDetails();
    }

    private function getDisplayPropertiesForGetLastVisitsDetails()
    {
        return array(
            'datatable_template'          => "@Live/getVisitorLog.twig",
            'disable_generic_filters'     => true,
            'enable_sort'                 => false,
            'filter_sort_column'          => 'idVisit',
            'filter_sort_order'           => 'asc',
            'show_search'                 => false,
            'filter_limit'                => 20,
            'show_offset_information'     => false,
            'show_exclude_low_population' => false,
            'show_all_views_icons' => false,
            'show_table_all_columns' => false,
            'show_export_as_rss_feed' => false,
            'documentation' => Piwik_Translate('Live_VisitorLogDocumentation', array('<br />', '<br />')),
            'custom_parameters' => array(
                // set a very high row count so that the next link in the footer of the data table is always shown
                'totalRows'         => 10000000,

                'filterEcommerce'   => Common::getRequestVar('filterEcommerce', 0, 'int'),
                'pageUrlNotDefined' => Piwik_Translate('General_NotDefined', Piwik_Translate('Actions_ColumnPageURL'))
            ),
            'visualization_properties' => array(
                'table' => array(
                    'disable_row_actions' => true,
                )
            )
        );
    }
}