<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package VisitorInterest
 */
namespace Piwik\Plugins\VisitorInterest;

use Piwik\ArchiveProcessor;

use Piwik\FrontController;
use Piwik\Menu\MenuMain;
use Piwik\Metrics;
use Piwik\Piwik;
use Piwik\WidgetsList;

/**
 *
 * @package VisitorInterest
 */
class VisitorInterest extends \Piwik\Plugin
{
    /**
     * @see Piwik_Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
    {
        $hooks = array(
            'ArchiveProcessor.Day.compute'             => 'archiveDay',
            'ArchiveProcessor.Period.compute'          => 'archivePeriod',
            'WidgetsList.addWidgets'                   => 'addWidgets',
            'Menu.Reporting.addItems'                  => 'addMenu',
            'API.getReportMetadata'                    => 'getReportMetadata',
            'Visualization.getReportDisplayProperties' => 'getReportDisplayProperties',
        );
        return $hooks;
    }

    public function getReportMetadata(&$reports)
    {
        $reports[] = array(
            'category'          => Piwik::translate('General_Visitors'),
            'name'              => Piwik::translate('VisitorInterest_WidgetLengths'),
            'module'            => 'VisitorInterest',
            'action'            => 'getNumberOfVisitsPerVisitDuration',
            'dimension'         => Piwik::translate('VisitorInterest_ColumnVisitDuration'),
            'metrics'           => array('nb_visits'),
            'processedMetrics'  => false,
            'constantRowsCount' => true,
            'documentation'     => Piwik::translate('VisitorInterest_WidgetLengthsDocumentation')
                . '<br />' . Piwik::translate('General_ChangeTagCloudView'),
            'order'             => 15
        );

        $reports[] = array(
            'category'          => Piwik::translate('General_Visitors'),
            'name'              => Piwik::translate('VisitorInterest_WidgetPages'),
            'module'            => 'VisitorInterest',
            'action'            => 'getNumberOfVisitsPerPage',
            'dimension'         => Piwik::translate('VisitorInterest_ColumnPagesPerVisit'),
            'metrics'           => array('nb_visits'),
            'processedMetrics'  => false,
            'constantRowsCount' => true,
            'documentation'     => Piwik::translate('VisitorInterest_WidgetPagesDocumentation')
                . '<br />' . Piwik::translate('General_ChangeTagCloudView'),
            'order'             => 20
        );

        $reports[] = array(
            'category'          => Piwik::translate('General_Visitors'),
            'name'              => Piwik::translate('VisitorInterest_visitsByVisitCount'),
            'module'            => 'VisitorInterest',
            'action'            => 'getNumberOfVisitsByVisitCount',
            'dimension'         => Piwik::translate('VisitorInterest_visitsByVisitCount'),
            'metrics'           => array(
                'nb_visits',
                'nb_visits_percentage' => Piwik::translate('General_ColumnPercentageVisits'),
            ),
            'processedMetrics'  => false,
            'constantRowsCount' => true,
            'documentation'     => Piwik::translate('VisitorInterest_WidgetVisitsByNumDocumentation')
                . '<br />' . Piwik::translate('General_ChangeTagCloudView'),
            'order'             => 25
        );

        $reports[] = array(
            'category'          => Piwik::translate('General_Visitors'),
            'name'              => Piwik::translate('VisitorInterest_VisitsByDaysSinceLast'),
            'module'            => 'VisitorInterest',
            'action'            => 'getNumberOfVisitsByDaysSinceLast',
            'dimension'         => Piwik::translate('VisitorInterest_VisitsByDaysSinceLast'),
            'metrics'           => array('nb_visits'),
            'processedMetrics'  => false,
            'constantRowsCount' => true,
            'documentation'     => Piwik::translate('VisitorInterest_WidgetVisitsByDaysSinceLastDocumentation'),
            'order'             => 30
        );
    }

    public function addWidgets()
    {
        WidgetsList::add('General_Visitors', 'VisitorInterest_WidgetLengths', 'VisitorInterest', 'getNumberOfVisitsPerVisitDuration');
        WidgetsList::add('General_Visitors', 'VisitorInterest_WidgetPages', 'VisitorInterest', 'getNumberOfVisitsPerPage');
        WidgetsList::add('General_Visitors', 'VisitorInterest_visitsByVisitCount', 'VisitorInterest', 'getNumberOfVisitsByVisitCount');
        WidgetsList::add('General_Visitors', 'VisitorInterest_WidgetVisitsByDaysSinceLast', 'VisitorInterest', 'getNumberOfVisitsByDaysSinceLast');
    }

    public function addMenu()
    {
        MenuMain::getInstance()->rename('General_Visitors', 'VisitFrequency_SubmenuFrequency',
            'General_Visitors', 'VisitorInterest_Engagement');
    }

    function postLoad()
    {
        Piwik::addAction('Template.headerVisitsFrequency', array('Piwik\Plugins\VisitorInterest\VisitorInterest', 'headerVisitsFrequency'));
        Piwik::addAction('Template.footerVisitsFrequency', array('Piwik\Plugins\VisitorInterest\VisitorInterest', 'footerVisitsFrequency'));
    }

    public function archivePeriod(ArchiveProcessor\Period $archiveProcessor)
    {
        $archiving = new Archiver($archiveProcessor);
        if ($archiving->shouldArchive()) {
            $archiving->archivePeriod();
        }
    }

    public function archiveDay(ArchiveProcessor\Day $archiveProcessor)
    {
        $archiving = new Archiver($archiveProcessor);
        if ($archiving->shouldArchive()) {
            $archiving->archiveDay();
        }
    }

    static public function headerVisitsFrequency(&$out)
    {
        $out = '<div id="leftcolumn">';
    }

    static public function footerVisitsFrequency(&$out)
    {
        $out = '</div>
			<div id="rightcolumn">
			';
        $out .= FrontController::getInstance()->fetchDispatch('VisitorInterest', 'index');
        $out .= '</div>';
    }

    public function getReportDisplayProperties(&$properties)
    {
        $properties['VisitorInterest.getNumberOfVisitsPerVisitDuration'] =
            $this->getDisplayPropertiesForGetNumberOfVisitsPerVisitDuration();
        $properties['VisitorInterest.getNumberOfVisitsPerPage'] =
            $this->getDisplayPropertiesForGetNumberOfVisitsPerPage();
        $properties['VisitorInterest.getNumberOfVisitsByVisitCount'] =
            $this->getDisplayPropertiesForGetNumberOfVisitsByVisitCount();
        $properties['VisitorInterest.getNumberOfVisitsByDaysSinceLast'] =
            $this->getDisplayPropertiesForGetNumberOfVisitsByDaysSinceLast();
    }

    private function getDisplayPropertiesForGetNumberOfVisitsPerVisitDuration()
    {
        return array(
            'default_view_type'           => 'cloud',
            'filter_sort_column'          => 'label',
            'filter_sort_order'           => 'asc',
            'translations'                => array('label' => Piwik::translate('VisitorInterest_ColumnVisitDuration')),
            'enable_sort'                 => false,
            'show_exclude_low_population' => false,
            'show_offset_information'     => false,
            'show_pagination_control'     => false,
            'show_limit_control'          => false,
            'show_search'                 => false,
            'show_table_all_columns'      => false,
            'visualization_properties'    => array(
                'graph' => array(
                    'max_graph_elements' => 10
                )
            )
        );
    }

    private function getDisplayPropertiesForGetNumberOfVisitsPerPage()
    {
        return array(
            'default_view_type'           => 'cloud',
            'filter_sort_column'          => 'label',
            'filter_sort_order'           => 'asc',
            'translations'                => array('label' => Piwik::translate('VisitorInterest_ColumnPagesPerVisit')),
            'enable_sort'                 => false,
            'show_exclude_low_population' => false,
            'show_offset_information'     => false,
            'show_pagination_control'     => false,
            'show_limit_control'          => false,
            'show_search'                 => false,
            'show_table_all_columns'      => false,
            'visualization_properties'    => array(
                'graph' => array(
                    'max_graph_elements' => 10
                )
            )
        );
    }

    private function getDisplayPropertiesForGetNumberOfVisitsByVisitCount()
    {
        return array(
            'columns_to_display'          => array('label', 'nb_visits', 'nb_visits_percentage'),
            'filter_sort_column'          => 'label',
            'filter_sort_order'           => 'asc',
            'translations'                => array('label'                => Piwik::translate('VisitorInterest_VisitNum'),
                                                   'nb_visits_percentage' => Metrics::getPercentVisitColumn()),
            'show_exclude_low_population' => false,
            'show_offset_information'     => false,
            'show_pagination_control'     => false,
            'show_limit_control'          => false,
            'filter_limit'                => 15,
            'show_search'                 => false,
            'enable_sort'                 => false,
            'show_table_all_columns'      => false,
            'show_all_views_icons'        => false,
        );
    }

    private function getDisplayPropertiesForGetNumberOfVisitsByDaysSinceLast()
    {
        return array(
            'filter_sort_column'          => 'label',
            'filter_sort_order'           => 'asc',
            'translations'                => array('label' => Piwik::translate('General_DaysSinceLastVisit')),
            'show_exclude_low_population' => false,
            'show_offset_information'     => false,
            'show_pagination_control'     => false,
            'show_limit_control'          => false,
            'show_all_views_icons'        => false,
            'filter_limit'                => 15,
            'show_search'                 => false,
            'enable_sort'                 => false,
            'show_table_all_columns'      => false
        );
    }
}