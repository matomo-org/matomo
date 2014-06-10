<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\VisitorInterest;

use Piwik\ArchiveProcessor;
use Piwik\FrontController;
use Piwik\Metrics;
use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\CoreVisualizations\Visualizations\Cloud;
use Piwik\Plugins\CoreVisualizations\Visualizations\Graph;

/**
 *
 */
class VisitorInterest extends \Piwik\Plugin
{
    /**
     * @see Piwik\Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
    {
        $hooks = array(
            'API.getReportMetadata'        => 'getReportMetadata',
            'ViewDataTable.configure'      => 'configureViewDataTable',
            'ViewDataTable.getDefaultType' => 'getDefaultTypeViewDataTable'
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

    function postLoad()
    {
        Piwik::addAction('Template.headerVisitsFrequency', array('Piwik\Plugins\VisitorInterest\VisitorInterest', 'headerVisitsFrequency'));
        Piwik::addAction('Template.footerVisitsFrequency', array('Piwik\Plugins\VisitorInterest\VisitorInterest', 'footerVisitsFrequency'));
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

    public function getDefaultTypeViewDataTable(&$defaultViewTypes)
    {
        $defaultViewTypes['VisitorInterest.getNumberOfVisitsPerVisitDuration'] = Cloud::ID;
        $defaultViewTypes['VisitorInterest.getNumberOfVisitsPerPage']          = Cloud::ID;
    }

    public function configureViewDataTable(ViewDataTable $view)
    {
        switch ($view->requestConfig->apiMethodToRequestDataTable) {
            case 'VisitorInterest.getNumberOfVisitsPerVisitDuration':
                $this->configureViewForGetNumberOfVisitsPerVisitDuration($view);
                break;
            case 'VisitorInterest.getNumberOfVisitsPerPage':
                $this->configureViewForGetNumberOfVisitsPerPage($view);
                break;
            case 'VisitorInterest.getNumberOfVisitsByVisitCount':
                $this->configureViewForGetNumberOfVisitsByVisitCount($view);
                break;
            case 'VisitorInterest.getNumberOfVisitsByDaysSinceLast':
                $this->configureViewForGetNumberOfVisitsByDaysSinceLast($view);
                break;
        }
    }

    private function configureViewForGetNumberOfVisitsPerVisitDuration(ViewDataTable $view)
    {
        $view->requestConfig->filter_sort_column = 'label';
        $view->requestConfig->filter_sort_order  = 'asc';

        $view->config->addTranslation('label', Piwik::translate('VisitorInterest_ColumnVisitDuration'));
        $view->config->enable_sort = false;
        $view->config->show_exclude_low_population = false;
        $view->config->show_offset_information = false;
        $view->config->show_pagination_control = false;
        $view->config->show_limit_control      = false;
        $view->config->show_search             = false;
        $view->config->show_table_all_columns  = false;
        $view->config->columns_to_display      = array('label', 'nb_visits');

        if ($view->isViewDataTableId(Graph::ID)) {
            $view->config->show_series_picker = false;
            $view->config->selectable_columns = array();
            $view->config->max_graph_elements = 10;
        }
    }

    private function configureViewForGetNumberOfVisitsPerPage(ViewDataTable $view)
    {
        $view->requestConfig->filter_sort_column = 'label';
        $view->requestConfig->filter_sort_order  = 'asc';

        $view->config->addTranslation('label', Piwik::translate('VisitorInterest_ColumnVisitDuration'));
        $view->config->enable_sort = false;
        $view->config->show_exclude_low_population = false;
        $view->config->show_offset_information = false;
        $view->config->show_pagination_control = false;
        $view->config->show_limit_control      = false;
        $view->config->show_search             = false;
        $view->config->show_table_all_columns  = false;
        $view->config->columns_to_display      = array('label', 'nb_visits');

        if ($view->isViewDataTableId(Graph::ID)) {
            $view->config->show_series_picker = false;
            $view->config->selectable_columns = array();
            $view->config->max_graph_elements = 10;
        }
    }

    private function configureViewForGetNumberOfVisitsByVisitCount(ViewDataTable $view)
    {
        $view->requestConfig->filter_sort_column = 'label';
        $view->requestConfig->filter_sort_order  = 'asc';
        $view->requestConfig->filter_limit = 15;

        $view->config->addTranslations(array(
            'label'                => Piwik::translate('VisitorInterest_VisitNum'),
            'nb_visits_percentage' => Metrics::getPercentVisitColumn())
        );

        $view->config->columns_to_display = array('label', 'nb_visits', 'nb_visits_percentage');
        $view->config->show_exclude_low_population = false;

        $view->config->enable_sort = false;
        $view->config->show_offset_information = false;
        $view->config->show_pagination_control = false;
        $view->config->show_limit_control      = false;
        $view->config->show_search             = false;
        $view->config->show_table_all_columns  = false;
        $view->config->show_all_views_icons    = false;
    }

    private function configureViewForGetNumberOfVisitsByDaysSinceLast(ViewDataTable $view)
    {
        $view->requestConfig->filter_sort_column = 'label';
        $view->requestConfig->filter_sort_order  = 'asc';
        $view->requestConfig->filter_limit = 15;

        $view->config->show_search = false;
        $view->config->enable_sort = false;
        $view->config->show_offset_information = false;
        $view->config->show_pagination_control = false;
        $view->config->show_limit_control      = false;
        $view->config->show_all_views_icons    = false;
        $view->config->show_table_all_columns  = false;
        $view->config->show_exclude_low_population = false;
        $view->config->addTranslation('label', Piwik::translate('General_DaysSinceLastVisit'));
    }
}
