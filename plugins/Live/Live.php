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

use Piwik\Piwik;
use Piwik\Common;
use Piwik\WidgetsList;

/**
 *
 * @package Live
 */
class Live extends \Piwik\Plugin
{
    /**
     * This event is called in the Live.getVisitorProfile API method. Plugins can use this event
     * to discover and add extra data to visitor profiles.
     * 
     * For example, if an email address is found in a custom variable, a plugin could load the
     * gravatar for the email and add it to the visitor profile so it will display in the 
     * visitor profile popup.
     * 
     * The following visitor profile elements can be set to augment the visitor profile popup:
     * - visitorAvatar: A URL to an image to display in the top left corner of the popup.
     * - visitorDescription: Text to be used as the tooltip of the avatar image.
     * 
     * Callback Signature: function (array &$result);
     */
    const GET_EXTRA_VISITOR_DETAILS_EVENT = 'Live.getExtraVisitorDetails';

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
        Piwik_AddMenu('General_Visitors', 'Live_VisitorLog', array('module' => 'Live', 'action' => 'indexVisitorLog'), true, $order = 5);
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
            'documentation'               => Piwik_Translate('Live_VisitorLogDocumentation', array('<br />', '<br />')),
            'custom_parameters'           => array(
                // set a very high row count so that the next link in the footer of the data table is always shown
                'totalRows'         => 10000000,

                'filterEcommerce'   => Common::getRequestVar('filterEcommerce', 0, 'int'),
                'pageUrlNotDefined' => Piwik_Translate('General_NotDefined', Piwik_Translate('Actions_ColumnPageURL'))
            ),
            'footer_icons'                => array(
                array(
                    'class' => 'tableAllColumnsSwitch',
                    'buttons' => array(
                        array(
                            'id' => 'Piwik\\Plugins\\Live\\VisitorLog',
                            'title' => Piwik_Translate('Live_LinkVisitorLog'),
                            'icon' => 'plugins/Zeitgeist/images/table.png'
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